# Tahap 11: DevOps, CI/CD, & Observability

Sistem Enterprise LMS telah dipersenjatai dengan arsitektur DevOps dan pemantauan (Observability) standar industri untuk memastikan ketersediaan tinggi (*High Availability*) dan kemudahan pemeliharaan (*Maintainability*).

---

## 1. Containerization (Docker)
Platform menggunakan *Docker* untuk mengemas aplikasi.
- **Backend Dockerfile:** Dibangun dari `php:8.4-fpm-alpine`, menginstal ekstensi PostgreSQL dan Redis, lalu dioptimalkan khusus untuk *production* (tanpa *dev dependencies* composer).
- **Frontend Dockerfile:** Menggunakan *Multi-stage build*. Tahap pertama me-*compile* Vue 3 TypeScript menggunakan Node.js. Tahap kedua menyajikan file statis tersebut menggunakan server web sangat ringan, yaitu **Nginx** (Alpine), lengkap dengan konfigurasi *Proxy Pass* untuk merutekan `/api` ke backend.

---

## 2. Docker Compose (Infrastruktur Lengkap)
File `docker-compose.yml` telah dirancang untuk menjalankan seluruh tumpukan aplikasi secara bersamaan di server produksi atau lokal:
- `lms_backend` (Laravel API)
- `lms_frontend` (Vue 3 Nginx Server)
- `postgres` (Primary Database)
- `redis` (Cache & Queue)
- `minio` (Object Storage Video)
- `meilisearch` (Search Engine)
- `clickhouse` (OLAP Database Analytics)
- `nats` (Event Message Broker)
- **Observability Stack:** `prometheus`, `loki`, dan `grafana`.

---

## 3. Observability (Monitoring, Logging, Tracing)
Kita menggunakan pendekatan terpusat (*Centralized Observability*).

### A. Grafana (Visualisasi Pusat)
Dashboard utama (*single pane of glass*) untuk melihat seluruh metrik, log, dan *trace* aplikasi. Berjalan di Port 3000.

### B. Prometheus (Metrics)
Telah disiapkan konfigurasi `prometheus.yml` untuk melakukan proses *scraping* (menarik data) dari Laravel `/metrics` dan `postgres_exporter` setiap 15 detik. Ini digunakan untuk memantau:
- Beban CPU & Memori container.
- *Request per second* (RPS) dan *HTTP 500 Error rate*.
- Jumlah *Connections* pada PostgreSQL.

### C. Loki (Log Aggregation)
Telah disiapkan `loki.yml`. Loki berfungsi menangkap semua log (*stdout/stderr*) dari container Docker dan file `laravel.log`. Keuntungannya, *developer* tidak perlu masuk (SSH) ke dalam server untuk melihat pesan error; cukup buka Grafana dan cari dengan bahasa *LogQL* (contoh: `{container="lms_backend"} |= "Exception"`).

### D. OpenTelemetry (Tracing) - Konseptual
Meskipun tidak diaktifkan langsung di dalam *compose* dasar karena kompleksitasnya, aplikasi didesain agar kompatibel dengan *OpenTelemetry*. Jika *request* terasa lambat, *Trace ID* akan ditangkap untuk melihat waktu eksekusi mulai dari Controller -> Redis -> DB -> API Eksternal.

---

## 4. CI/CD Pipeline (GitHub Actions)
File `.github/workflows/ci.yml` memastikan kualitas kode secara otomatis (*Continuous Integration*).
- **Triggers:** Dijalankan setiap kali ada *Push* atau *Pull Request* ke branch `main` atau `develop`.
- **Backend Jobs:**
  1. Menyiapkan container PostgreSQL dan Redis sementara (*Service Containers*).
  2. Menjalankan *Composer Install*.
  3. Mengeksekusi seluruh migrasi database (`php artisan migrate`).
  4. Menjalankan PHPUnit Tests (`php artisan test`) untuk memastikan *Clean Architecture* dan *Service layer* bekerja normal.
- **Frontend Jobs:**
  1. Menjalankan *npm install*.
  2. Mengeksekusi *TypeScript build* (`npm run build`) untuk memastikan tidak ada kesalahan kompilasi (*type errors*).

---

## 5. Backup & Recovery Strategy
Untuk *Enterprise Environment*, kita mengandalkan otomatisasi skrip dan fitur *cloud-native*.

- **Database (PostgreSQL & ClickHouse):**
  - *Automated Daily Snapshots* (Dump) menggunakan *cron job* internal server.
  - Snapshot diunggah (*sync*) otomatis ke AWS S3 Glacier menggunakan *AWS CLI* atau ke *MinIO* cluster lainnya.
  - Implementasi *Write-Ahead Logging* (WAL-G) untuk mendukung fitur *Point-in-Time Recovery (PITR)*. Jika data terhapus jam 10:05, kita bisa *rollback* ke jam 10:04.
- **Storage (MinIO Videos):**
  - Mengaktifkan fitur **MinIO Bucket Replication**. Setiap kali instruktur mengunggah video ke *Server A*, video tersebut otomatis direplikasi secara *real-time* ke *Server B* (Region/Data Center berbeda).
- **Disaster Recovery (RTO & RPO):**
  - *Recovery Time Objective (RTO)* ditargetkan < 1 jam berkat *Infrastructure as Code* (Docker Compose/K8s). Cukup jalankan perintah `docker compose up` di server baru dan *restore* database.
  - *Recovery Point Objective (RPO)* ditargetkan < 5 menit berkat replikasi WAL aktif.