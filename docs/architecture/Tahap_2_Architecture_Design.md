# Tahap 2: Architecture & System Design

LMS Platform (Enterprise Grade)

## 1. Arsitektur Sistem (Clean Architecture & Domain-Driven Design)

Platform ini mengadopsi prinsip **Clean Architecture** yang diintegrasikan dengan **Domain-Driven Design (DDD)**. Karena kita menggunakan Laravel (yang berbasis MVC), kita akan mengadaptasinya menjadi struktur *Modular Monolith*. Hal ini memastikan sistem mudah dipelihara, *scalable*, dan batas-batas domain (Bounded Contexts) tetap terjaga.

### Bounded Contexts Utama:
1.  **Identity & Access (IAM):** Autentikasi, manajemen pengguna (User, Role, Permission).
2.  **Catalog (Course Management):** Manajemen kategori, kursus, modul, silabus, dan review.
3.  **Enrollment & Learning:** Manajemen kepesertaan siswa, pelacakan progres (video/kuis), sertifikasi.
4.  **Billing & Payment:** Manajemen keranjang (cart), order, integrasi payment gateway, invoice.

### Struktur Layer per Domain (Clean Architecture):
-   **Domain Layer:** Entitas bisnis murni, Value Objects, Domain Events. *Tidak bergantung pada framework.*
-   **Application Layer:** Use Cases (Service classes), DTOs, dan CQS/CQRS (Command/Query Handlers). Mengkoordinasikan Domain layer.
-   **Infrastructure Layer:** Implementasi konkret dari interface (Repository pattern untuk Eloquent), interaksi dengan cache (Redis), storage (MinIO), search (Meilisearch), NATS, dan API pihak ketiga.
-   **Presentation/UI Layer:** API Controllers (Laravel), Middleware, Form Requests. Mengolah HTTP request dan mengembalikan JSON response ke Frontend (Vue 3).

---

## 2. C4 Model (Context Diagram)

Diagram berikut menggunakan standar C4 Model level 1 (System Context) untuk menggambarkan bagaimana aktor dan sistem eksternal berinteraksi dengan LMS Platform.

```mermaid
C4Context
    title System Context Diagram for LMS Platform

    Person(student, "Student", "Pengguna yang mencari dan mengikuti kursus.")
    Person(instructor, "Instructor", "Pengguna yang membuat dan mengelola materi kursus.")
    Person(admin, "Platform Admin", "Pengelola sistem, verifikasi kursus, dan customer service.")

    System(lms, "Enterprise LMS Platform", "Menyediakan katalog kursus, video player, dan manajemen pembelajaran (Vue 3 + Laravel 12).")

    System_Ext(payment_gateway, "Payment Gateway", "Memproses pembayaran kartu kredit, bank transfer, e-wallet (cth: Midtrans/Stripe).")
    System_Ext(storage_minio, "Object Storage (MinIO)", "Menyimpan file video, dokumen, dan gambar.")
    System_Ext(search_meili, "Search Engine (Meilisearch)", "Menyediakan pencarian instan untuk kursus dan konten.")
    System_Ext(analytics_clickhouse, "Analytics Engine (ClickHouse)", "Menyimpan dan mengolah data analitik skala besar.")
    System_Ext(monitoring_grafana, "Monitoring (Grafana/Prometheus)", "Memantau kesehatan infrastruktur dan log aplikasi.")

    Rel(student, lms, "Mencari kursus, menonton video, dan menyelesaikan kuis", "HTTPS")
    Rel(instructor, lms, "Mengunggah materi, melihat analitik penjualan", "HTTPS")
    Rel(admin, lms, "Mengelola pengguna, moderasi konten", "HTTPS")

    Rel(lms, payment_gateway, "Meneruskan transaksi dan menerima webhook pembayaran", "HTTPS/JSON")
    Rel(lms, storage_minio, "Menyimpan dan membaca file media", "S3 API")
    Rel(lms, search_meili, "Sinkronisasi data indeks kursus untuk pencarian", "HTTP")
    Rel(lms, analytics_clickhouse, "Mengirim event pembelajaran (video progress, log)", "TCP/HTTP")
    Rel(lms, monitoring_grafana, "Mengirim metrik dan log (via OpenTelemetry)", "gRPC/HTTP")
```

---

## 3. Database Entity Relationship Diagram (ERD)

ERD di bawah ini merepresentasikan struktur data MVP dari LMS Platform yang akan diimplementasikan pada PostgreSQL.

```mermaid
erDiagram
    USERS ||--o{ ROLES_USERS : "has"
    ROLES ||--o{ ROLES_USERS : "assigned to"
    USERS ||--o{ COURSES : "creates (instructor)"
    USERS ||--o{ ENROLLMENTS : "enrolls in"
    USERS ||--o{ PAYMENTS : "makes"
    COURSES ||--o{ SECTIONS : "contains"
    COURSES ||--o{ REVIEWS : "receives"
    SECTIONS ||--o{ LESSONS : "contains"
    COURSES ||--o{ ENROLLMENTS : "has"
    ENROLLMENTS ||--o{ PROGRESS : "tracks"
    LESSONS ||--o{ PROGRESS : "recorded in"
    ORDERS ||--o{ PAYMENTS : "paid via"
    USERS ||--o{ ORDERS : "places"
    ORDERS ||--o{ ORDER_ITEMS : "contains"
    COURSES ||--o{ ORDER_ITEMS : "included in"

    USERS {
        uuid id PK
        string name
        string email UK
        string password
        string avatar_url
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    ROLES {
        int id PK
        string name "e.g., admin, instructor, student"
    }

    ROLES_USERS {
        uuid user_id FK
        int role_id FK
    }

    COURSES {
        uuid id PK
        uuid instructor_id FK
        string title
        string slug UK
        text description
        decimal price
        string thumbnail_url
        string status "draft, published, archived"
        datetime published_at
        datetime created_at
    }

    SECTIONS {
        uuid id PK
        uuid course_id FK
        string title
        int order_index
    }

    LESSONS {
        uuid id PK
        uuid section_id FK
        string title
        string type "video, article, quiz"
        string content_url "MinIO URL if video"
        text text_content "If article"
        int order_index
        boolean is_free_preview
    }

    ENROLLMENTS {
        uuid id PK
        uuid user_id FK
        uuid course_id FK
        datetime enrolled_at
        decimal progress_percentage
        boolean is_completed
    }

    PROGRESS {
        uuid id PK
        uuid enrollment_id FK
        uuid lesson_id FK
        boolean is_completed
        int watch_time_seconds
        datetime last_accessed_at
    }

    ORDERS {
        uuid id PK
        uuid user_id FK
        decimal total_amount
        string status "pending, success, failed, refunded"
        datetime created_at
    }

    ORDER_ITEMS {
        uuid id PK
        uuid order_id FK
        uuid course_id FK
        decimal price
    }

    PAYMENTS {
        uuid id PK
        uuid order_id FK
        uuid user_id FK
        string transaction_id "from gateway"
        string payment_method
        decimal amount
        string status
        datetime paid_at
    }

    REVIEWS {
        uuid id PK
        uuid course_id FK
        uuid user_id FK
        int rating "1-5"
        text comment
        datetime created_at
    }
```

---

## 4. API Contracts & Batasan Modul

Untuk menjaga agar aplikasi dapat di-*maintain* dengan baik, setiap modul hanya boleh berkomunikasi dengan modul lain melalui antarmuka yang didefinisikan (Interface/API) atau menggunakan Event-Driven architecture (menggunakan Laravel Events atau NATS).

**Contoh Event-Driven Flow (Asynchronous):**
1. Saat pesanan dibayar secara sukses (`OrderPaidEvent` dipicu).
2. Domain *Enrollment* mendengarkan event tersebut dan secara otomatis membuat rekam jejak `ENROLLMENT` untuk user tersebut.
3. Domain *Notification* mendengarkan event tersebut dan mengirimkan email selamat datang ke siswa.
4. Data analitik (pembelian) dikirimkan secara asinkron ke *ClickHouse*.

**Komunikasi Frontend & Backend:**
- **Protokol:** RESTful JSON API (dan kemungkinan GraphQL ke depannya untuk query kompleks).
- **Autentikasi API:** Menggunakan Laravel Sanctum (Stateful cookie untuk web app, Token untuk mobile app di masa depan).
- **Format Response Standar:**
  ```json
  {
      "success": true,
      "message": "Data retrieved successfully",
      "data": { ... },
      "meta": { ... } // Untuk pagination dll.
  }
  ```

---

### Penjelasan Teknis Tambahan:
- **Modularitas di Laravel:** Kita akan memisahkan domain menjadi folder seperti `app/Domain/Course`, `app/Domain/User`, dll., bukan menggunakan struktur MVC standar (`app/Models`, `app/Controllers` tercampur semua domain). Ini memastikan *Clean Architecture*.
- **PostgreSQL UUID:** Kita menggunakan tipe UUID untuk *Primary Key* guna keamanan ID yang tidak tertebak, terutama pada entitas utama seperti User, Course, Order, dll.
