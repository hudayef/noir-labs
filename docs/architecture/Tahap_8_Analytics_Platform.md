# Tahap 8: Analytics Platform Enterprise Architecture

Untuk memproses jutaan interaksi (*event*) yang dihasilkan oleh siswa di platform LMS tanpa membebani database utama (PostgreSQL), kita membangun sebuah ekosistem *Data & Analytics Pipeline* yang berdedikasi menggunakan **NATS**, **Redis**, dan **ClickHouse**.

Sistem ini didesain agar sangat *scalable*, *real-time*, dan mampu menghasilkan *insights* pelaporan (reporting) yang mendalam.

---

## 1. High-Level Event Pipeline

Aliran data (*data flow*) berawal dari aktivitas *client* (Frontend Vue 3), melewati API Backend, masuk ke Message Broker, diproses oleh pekerja (*Workers*), hingga disimpan secara permanen di OLAP Database.

```mermaid
flowchart TD
    Client[Client / Vue.js Player] -->|POST /api/track/event| API[Tracking API / Gateway]

    subgraph "Data Ingestion"
        API -->|Publish (Fire & Forget)| NATS((NATS JetStream))
        API -.->|Fall-back Buffer| Redis[(Redis Buffer)]
    end

    subgraph "Data Processing (Workers)"
        NATS --> WorkerA[Tracking Worker\nConsume Event]
        NATS --> WorkerB[Business Logic Worker\ne.g. Update Progress]
    end

    subgraph "Data Storage & Query"
        WorkerA -->|Batch Insert (Batched every 1s)| ClickHouse[(ClickHouse\nOLAP Database)]
        ReportAPI[Reporting Service] --> ClickHouse
    end

    ReportAPI -->|GET /api/reports/*| Dashboard[Analytics Dashboard]
```

---

## 2. Core Components

Platform analitik ini terdiri dari tiga layanan (Service) yang terpisah secara logika.

### A. Tracking Service (Ingestion Layer)
**Tujuan:** Menerima raw data event dari sisi klien secara super-cepat tanpa pemrosesan bisnis yang rumit.
- Klien mengirim paket event secara *batched* (misal setiap 5 detik atau saat *unload*) ke API `POST /api/track/event`.
- Layanan ini melakukan validasi dasar (JWT/Sesi) lalu menembakkannya (Publish) ke **NATS JetStream** dengan topik `events.lms.*`.
- **Redis** digunakan sebagai penampung statis sementara jika ada lonjakan trafik ekstrem yang membuat *rate limiter* NATS kewalahan, sebelum dipompa kembali ke NATS.

### B. Analytics Service (Processing Layer)
**Tujuan:** Bertindak sebagai jembatan yang mengonsumsi pesan dari NATS dan menuliskannya secara efisien ke ClickHouse.
- *Analytics Worker* mem-subscribe antrean NATS.
- Daripada melakukan `INSERT` ke ClickHouse setiap ada 1 event (yang akan membunuh performa disk I/O), layanan ini melakukan **Micro-batching**. Mengumpulkan 1000 event atau setiap 2 detik, lalu menyuntikkannya sekaligus (`INSERT INTO events (...) VALUES (...)`).

### C. Reporting Service (Serving Layer)
**Tujuan:** Menyediakan *aggregated data* (data agregat) kepada UI/Frontend untuk digambar ke dalam grafik (Chart).
- Layanan ini berinteraksi langsung dengan **ClickHouse** menggunakan *Materialized Views* untuk kueri agregasi super cepat.
- Endpoint seperti `GET /api/reports/video-retention/{course_id}` akan mengembalikan data berapa rata-rata menit siswa menonton video.

---

## 3. Supported Events & Payloads

Sistem didesain untuk melacak (tracking) *event-event* krusial berikut:

| Kategori | Event Name | Deskripsi |
| :--- | :--- | :--- |
| **Authentication** | `Login`, `Logout` | Melacak frekuensi akses platform dan perangkat yang digunakan. |
| **Discovery** | `Course_View`, `Lesson_View` | Mengukur *Page Views* untuk mengetahui kursus mana yang paling sering dilirik. |
| **Engagement** | `Video_Play`, `Video_Pause`, `Video_Complete` | Metrik vital. Menyertakan atribut `timestamp_video` (di menit/detik keberapa video dijeda) untuk menghitung *Drop-off rate*. |
| **Assessment** | `Quiz_Start`, `Quiz_Submit`, `Assignment_Submit` | Menghitung *Completion rate* dan tingkat kesulitan soal. |
| **Achievement** | `Certificate_Download` | Metrik kesuksesan siswa. |
| **Monetization** | `Payment_Success` | Melacak konversi dan nilai ekonomi (LTV - *Life Time Value*). |

**Contoh Struktur Payload JSON yang dikirimkan Client ke API:**
```json
{
  "event_id": "c9b2938e-a2...",
  "event_name": "Video_Pause",
  "user_id": "usr_99812...",
  "session_id": "ses_123...",
  "timestamp": "2026-06-10T14:30:00Z",
  "properties": {
    "course_id": "crs_555...",
    "lesson_id": "lsn_777...",
    "video_current_time_seconds": 125,
    "device": "mobile",
    "browser": "Chrome"
  }
}
```

---

## 4. ClickHouse Schema Design

ClickHouse merupakan *columnar database*, sehingga skema tabel harus dirancang lebar (wide-column) dan diindeks berdasarkan waktu (Time-Series) menggunakan mesin `MergeTree`.

```sql
-- Tabel Induk (Raw Events)
CREATE TABLE lms_analytics.events
(
    event_id UUID,
    event_name String,
    user_id UUID,
    session_id String,
    event_date Date,
    event_time DateTime,

    -- Menggunakan Map/JSON untuk menyimpan properties kustom yang bervariasi per event
    course_id Nullable(UUID),
    lesson_id Nullable(UUID),
    video_time_seconds Nullable(UInt32),
    device_type String,
    user_agent String
)
ENGINE = MergeTree()
PARTITION BY toYYYYMM(event_date)
ORDER BY (event_date, event_name, course_id, user_id)
SETTINGS index_granularity = 8192;
```

### Menggunakan Materialized Views (Untuk Reporting)
Kueri analitik raksasa secara *on-the-fly* bisa jadi berat. ClickHouse akan menggunakan **Materialized Views** untuk memprakalkulasi (pre-compute) laporan, misalnya "Berapa banyak jumlah tayangan video per kursus per hari?".

```sql
CREATE MATERIALIZED VIEW lms_analytics.daily_course_views_mv
ENGINE = SummingMergeTree()
PARTITION BY toYYYYMM(event_date)
ORDER BY (event_date, course_id)
AS SELECT
    event_date,
    course_id,
    count() as total_views,
    uniqExact(user_id) as unique_viewers
FROM lms_analytics.events
WHERE event_name IN ('Course_View', 'Video_Play')
GROUP BY event_date, course_id;
```
Dengan View ini, *Reporting Service* tidak perlu menscan jutaan baris data, ia cukup memanggil View tabel ringkasan ini yang merespons dalam waktu beberapa milidetik.