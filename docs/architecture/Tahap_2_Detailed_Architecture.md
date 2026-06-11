# Tahap 2: Detailed Architecture & System Design

LMS Platform (Enterprise Grade)

## 1. Context Diagram

Diagram ini menunjukkan gambaran sistem dalam ekosistem bisnis secara keseluruhan, aktor yang terlibat, dan interaksinya dengan sistem eksternal.

```mermaid
C4Context
    title Context Diagram - Enterprise LMS Platform

    Person(student, "Student", "Pengguna yang mencari, membeli, dan mengikuti kursus.")
    Person(instructor, "Instructor", "Pengguna yang membuat materi, video, dan kuis.")
    Person(b2b_admin, "B2B Admin / HR", "Mengelola dan memantau progres belajar karyawan perusahaan.")
    Person(sysadmin, "Platform Admin", "Moderasi konten, kelola user, dan pantau sistem.")

    System(lms, "Enterprise LMS Platform", "Platform utama untuk manajemen pembelajaran, e-commerce kursus, dan video streaming.")

    System_Ext(payment_gateway, "Payment Gateway", "Xendit/Midtrans untuk pemrosesan pembayaran.")
    System_Ext(email_service, "Email Service Provider", "SendGrid/AWS SES untuk notifikasi email.")
    System_Ext(video_cdn, "Video CDN", "Jaringan pengiriman konten (CDN) untuk distribusi video yang cepat.")

    Rel(student, lms, "Akses kursus, tonton video, kerjakan kuis", "HTTPS")
    Rel(instructor, lms, "Unggah materi, cek analytics", "HTTPS")
    Rel(b2b_admin, lms, "Lihat laporan performa karyawan", "HTTPS")
    Rel(sysadmin, lms, "Kelola operasional LMS", "HTTPS")

    Rel(lms, payment_gateway, "Kirim transaksi & terima webhook", "API/JSON")
    Rel(lms, email_service, "Kirim email transaksional", "SMTP/API")
    Rel(lms, video_cdn, "Distribusi media statis & video", "HTTPS")
```

**Penjelasan Komponen:**
- **Sistem Inti:** Berperan sebagai jembatan antara instruktur (kreator) dan siswa (konsumen).
- **Sistem Eksternal:** Layanan pihak ketiga yang tidak dikembangkan dari awal karena sudah ada solusi enterprise yang tangguh (Pembayaran, Email, dan CDN).

---

## 2. High Level Architecture

Menjelaskan bagaimana infrastruktur fisik/logis disusun di level makro.

```mermaid
flowchart TB
    Client[Browser / Mobile App] -->|HTTPS| LoadBalancer[Load Balancer / Nginx]

    LoadBalancer --> Frontend[Frontend Cluster\nVue 3 + Nginx]
    LoadBalancer --> API[API Gateway / Backend Cluster\nLaravel 12]

    Frontend -.->|REST API / GraphQL| API

    subgraph "Backend Services"
        API
        Worker[Laravel Queue Workers]
        Cron[Scheduler/Cron]
    end

    subgraph "Data Storage & Caching"
        DB[(PostgreSQL\nPrimary DB)]
        Cache[(Redis\nCache & Sessions)]
        Search[(Meilisearch\nSearch Engine)]
        Storage[(MinIO\nObject Storage)]
    end

    subgraph "Event & Analytics"
        Broker((NATS\nMessage Broker))
        OLAP[(ClickHouse\nAnalytics DB)]
    end

    API --> DB
    API <--> Cache
    API <--> Search
    API --> Storage

    API -.->|Publish Event| Broker
    Broker -.->|Consume Event| Worker
    Worker --> OLAP
```

**Penjelasan Komponen:**
- **Load Balancer:** Menangani *traffic routing* dan SSL Termination.
- **Frontend Cluster:** Menyajikan file statis SPA Vue 3.
- **Backend Cluster:** Berisi instans API Laravel. Dapat di-scale secara horizontal.
- **Queue Workers:** Memproses tugas berat secara *asynchronous* (misal: kirim email, render sertifikat).
- **NATS & ClickHouse:** NATS menangani aliran pesan (event) dalam volume tinggi, yang kemudian diserap oleh ClickHouse untuk kebutuhan analitik tanpa membebani PostgreSQL.

---

## 3. Low Level Architecture (Backend Clean Architecture)

Menggambarkan bagaimana kode di dalam aplikasi Laravel (Modular Monolith) disusun.

```mermaid
graph TD
    subgraph "Presentation Layer (HTTP)"
        Controllers[Controllers]
        Middleware[Middleware]
        Requests[Form Requests]
    end

    subgraph "Application Layer (Use Cases)"
        Services[Application Services / Command Handlers]
        DTO[Data Transfer Objects]
    end

    subgraph "Domain Layer (Core Logic)"
        Entities[Domain Entities]
        ValueObjects[Value Objects]
        Events[Domain Events]
        RepoInterfaces[Repository Interfaces]
    end

    subgraph "Infrastructure Layer"
        Eloquent[Eloquent Models]
        Repositories[Repository Implementations]
        External[External API Clients\nPayment, Email]
        CacheImpl[Redis / Cache]
    end

    Controllers -->|Map Request to DTO| DTO
    Controllers --> Services
    Services --> RepoInterfaces
    Services --> Entities
    Services --> Events

    Repositories -.->|Implements| RepoInterfaces
    Repositories --> Eloquent
    Repositories --> CacheImpl
```

**Penjelasan Komponen:**
- **Presentation:** Menerima *request* dari luar dan mengembalikan JSON.
- **Application:** Orkestrasi logika. Tidak memiliki aturan bisnis murni, hanya mengatur aliran (misal: "ambil data dari repo, panggil entitas, simpan ke repo").
- **Domain:** Inti sistem. Tidak tahu-menahu soal Laravel/Database. Berisi logika murni seperti *EnrollmentRules*, *CoursePricing*.
- **Infrastructure:** Kode yang berinteraksi langsung dengan database (Eloquent) atau sistem eksternal.

---

## 4. Component Diagram

Diagram komponen untuk satu domain spesifik, misalnya **Course Catalog Domain**.

```mermaid
componentDiagram
    package "Course Catalog Domain" {
        [CourseController]
        [CourseService]
        [CourseRepository]
        [MeilisearchSyncListener]
    }

    database "PostgreSQL" {
        [courses_table]
    }

    database "Meilisearch" {
        [course_index]
    }

    [Vue Frontend] --> [CourseController] : GET /api/courses
    [CourseController] --> [CourseService] : getCourses(filters)
    [CourseService] --> [CourseRepository] : findActiveCourses()
    [CourseRepository] --> [courses_table] : SQL Query

    [CourseService] ..> [MeilisearchSyncListener] : triggers Event
    [MeilisearchSyncListener] --> [course_index] : Sync Data
```

**Penjelasan Komponen:**
Menunjukkan isolasi domain. Jika data kursus berubah, event akan dipicu untuk memastikan *Search Engine* (Meilisearch) selalu mutakhir tanpa harus membebani *flow* utama.

---

## 5. Deployment Diagram

Menjelaskan bagaimana infrastruktur didistribusikan di lingkungan *Cloud* (misal: AWS/GCP).

```mermaid
graph TB
    Internet((Internet))

    subgraph "Cloud Provider (e.g. AWS)"
        WAF[Web Application Firewall]
        ALB[Application Load Balancer]

        subgraph "Public Subnet"
            Nat[NAT Gateway]
            CDN[CDN / CloudFront]
        end

        subgraph "Private Subnet (App Tier)"
            EKS[Kubernetes / ECS Cluster]
            PodUI[Vue UI Pods]
            PodAPI[Laravel API Pods]
            PodWorker[Laravel Worker Pods]
        end

        subgraph "Private Subnet (Data Tier)"
            RDS[(Managed PostgreSQL)]
            ElastiCache[(Managed Redis)]
            MS[(Meilisearch Instance)]
            CH[(ClickHouse Cluster)]
        end

        S3[(S3 / MinIO Storage)]
    end

    Internet --> WAF
    WAF --> CDN
    CDN --> ALB
    ALB --> EKS
    EKS --> PodUI
    EKS --> PodAPI

    PodAPI --> RDS
    PodAPI --> ElastiCache
    PodAPI --> MS
    PodWorker --> CH
    PodAPI --> S3
```

**Penjelasan Komponen:**
Pemisahan zona jaringan. Database dan *App Server* berada di *Private Subnet* sehingga tidak bisa diakses langsung dari internet, hanya bisa melalui *Load Balancer* di *Public Subnet*.

---

## 6. Security Architecture

Fokus pada pengamanan data dan akses.

```mermaid
flowchart LR
    User((User)) -->|1. Request with JWT/Sanctum Cookie| WAF[WAF / Rate Limiter]
    WAF -->|2. Passed| API[API Gateway]

    subgraph "Security Controls"
        AuthMiddleware[Authentication Middleware]
        Policy[Authorization Policies / RBAC]
        Sanitizer[Input Sanitizer / Form Request]
    end

    API --> AuthMiddleware
    AuthMiddleware -->|3. Valid Token| Policy
    Policy -->|4. Has Permission| Sanitizer
    Sanitizer -->|5. Clean Data| Controller[Business Logic]

    Controller --> DB[(Database)]

    style AuthMiddleware fill:#f9f,stroke:#333,stroke-width:2px
    style Policy fill:#f9f,stroke:#333,stroke-width:2px
```

**Penjelasan Komponen:**
- **WAF & Rate Limiter:** Melindungi dari serangan DDoS dan Brute Force.
- **Auth & Policy:** Autentikasi memastikan *siapa* penggunanya, sedangkan Policy (RBAC) memastikan apakah ia punya *hak* untuk melakukan tindakan tersebut (misal: hanya instruktur pemilik kursus yang bisa mengubah harga).
- **Data Encryption:** Password di-hash menggunakan Argon2/Bcrypt, transaksi TLS/HTTPS wajib.

---

## 7. Monitoring Architecture

Memastikan sistem bisa diobservasi (*Observability*) oleh tim DevOps.

```mermaid
graph TD
    subgraph "App Servers"
        App[Laravel API]
        Vue[Vue Frontend]
    end

    subgraph "Monitoring Stack"
        Prometheus[Prometheus\nMetrics Scraper]
        Loki[Loki\nLog Aggregation]
        Grafana[Grafana\nDashboard & Alerts]
        Tempo[Tempo/OpenTelemetry\nDistributed Tracing]
    end

    App -->|Expose /metrics| Prometheus
    App -->|Push Logs via Promtail| Loki
    App -->|Push Traces| Tempo

    Vue -->|Error Tracking| Loki

    Prometheus --> Grafana
    Loki --> Grafana
    Tempo --> Grafana

    Grafana -.->|Alert via Slack/Email| DevOps((DevOps Team))
```

**Penjelasan Komponen:**
- **Prometheus:** Mengumpulkan metrik (CPU, Memory, Request Rate).
- **Loki:** Mengumpulkan log aplikasi (error logs).
- **Tempo:** Melacak satu *request* dari ujung ke ujung untuk menganalisis *bottleneck* performa.
- **Grafana:** Visualisasi seluruh data dan mengirimkan alarm jika ada anomali.

---

## 8. Logging Architecture

Standarisasi pencatatan aktivitas.

```mermaid
flowchart LR
    AppLog[Application Logs\nLaravel Log] -->|JSON Format| Filebeat[Log Shipper / Promtail]
    AuditLog[Audit Trail\nDB Log] --> Filebeat
    AccessLog[Nginx Access Logs] --> Filebeat

    Filebeat --> CentralLog[(Centralized Log Storage\nLoki / ELK)]
    CentralLog --> Dashboard[Kibana / Grafana]
```

**Penjelasan Komponen:**
- Aplikasi harus membuang log dalam format **JSON** agar mudah diurai (parsed).
- **Audit Trail:** Mencatat *siapa melakukan apa dan kapan* (misal: "Admin A mengubah harga Kursus B dari 100k ke 50k pada jam 10:00").

---

## 9. Backup Architecture

Strategi untuk *Disaster Recovery*.

```mermaid
graph TD
    PrimaryDB[(Primary PostgreSQL)]
    Storage[(MinIO / S3)]

    subgraph "Backup Strategy"
        CronJob[Backup Cron Job\npg_dump]
        Replication[(Read Replica DB)]
        S3Backup[(Off-site S3 Backup\nGlacier)]
    end

    PrimaryDB -->|Real-time Streaming| Replication
    PrimaryDB -->|Daily Full Backup| CronJob
    CronJob -->|Upload| S3Backup
    Storage -->|Sync/Replication| S3Backup
```

**Penjelasan Komponen:**
- **High Availability:** Menggunakan *Read Replica* secara real-time. Jika Primary mati, Replica akan naik menjadi Primary (Auto Failover).
- **Disaster Recovery:** *Daily Full Backup* disimpan di *cold storage* yang berbeda region/provider (misal AWS S3 Glacier) untuk mencegah kehilangan data akibat bencana fisik atau peretasan.