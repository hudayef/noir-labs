# Tahap 4: Module Boundaries & API Contracts

Sistem Enterprise LMS ini dipecah menjadi beberapa *Bounded Contexts* (Modul) berdasarkan domain bisnis. Pemisahan ini memungkinkan tim untuk bekerja secara paralel, mencegah kode yang tumpang tindih (*spaghetti code*), dan mempersiapkan sistem jika kelak perlu diubah menjadi Microservices fisik.

---

## 1. Core Module
Menangani identitas, keamanan, dan hak akses dari setiap pengguna.

- **Responsibility:** Registrasi, Autentikasi (Login/OAuth), manajemen profil dasar, manajemen Role (Student, Instructor, dll), dan Permission (hak akses detail per *action*).
- **API Contracts:**
  - `POST /api/auth/register`
  - `POST /api/auth/login`
  - `POST /api/auth/refresh`
  - `GET /api/users/me`
- **Database:** `users`, `roles`, `permissions`, `role_user`, `permission_role`.
- **Events Published:**
  - `UserRegistered` (Memicu pembuatan profil di modul lain).
  - `UserRoleChanged` (Memicu pengecekan akses ulang).
- **Dependencies:** Berdiri sendiri. Modul ini adalah *upstream* yang digunakan oleh semua modul lain.

---

## 2. Learning Module
Inti dari pengalaman belajar. Menangani kurikulum dan materi.

- **Responsibility:** Pembuatan dan publikasi Course, Lesson (Video/Text), Quiz, Assignment (Tugas praktik), dan Exam (Ujian akhir kursus). Menangani proses *enrollment* (pendaftaran) siswa ke dalam kursus.
- **API Contracts:**
  - `GET /api/courses` (Katalog)
  - `POST /api/instructor/courses` (Buat draf)
  - `GET /api/courses/{id}/lessons`
  - `POST /api/lessons/{id}/progress` (Catat watch time/progress)
  - `POST /api/quizzes/{id}/submit`
- **Database:** `courses`, `sections`, `lessons`, `quizzes`, `assignments`, `enrollments`, `progress`.
- **Events Published:**
  - `CoursePublished` (Bisa memicu *Analytics* dan *Search Engine*).
  - `LessonCompleted` (Memicu kalkulasi *progress*).
  - `CourseCompleted` (Memicu penerbitan sertifikat).
- **Dependencies:** Sangat bergantung pada **Core** (untuk User ID & instruktur) dan dipicu oleh **Business** (untuk *enrollment* otomatis setelah pembayaran).

---

## 3. Business Module
Mengelola semua transaksi finansial dan monetisasi.

- **Responsibility:** Pembayaran keranjang belanja (Payment), model langganan bulanan/tahunan (Subscription), diskon/Kupon (Coupon), dan program rujukan (Affiliate).
- **API Contracts:**
  - `POST /api/checkout/cart`
  - `POST /api/payments/webhook` (Endpoint untuk Xendit/Midtrans)
  - `POST /api/subscriptions/subscribe`
  - `GET /api/affiliate/earnings`
- **Database:** `orders`, `order_items`, `payments`, `subscriptions`, `coupons`, `affiliates`.
- **Events Published:**
  - `PaymentSuccessful` (Paling kritikal: Memicu *Learning Module* untuk membuat `enrollment`).
  - `SubscriptionExpired` (Memicu pencabutan akses).
- **Dependencies:** **Core** (User ID), **Learning** (mengetahui harga dan ID kursus), pihak ketiga (Payment Gateway).

---

## 4. Community Module
Menangani interaksi sosial antar pengguna untuk meningkatkan retensi.

- **Responsibility:** Forum diskusi kursus, tanya jawab (Q&A) per video/materi, komentar, dan pesan langsung (Chat) antar pengguna (misal Student ke Instructor).
- **API Contracts:**
  - `GET /api/courses/{id}/discussions`
  - `POST /api/discussions/{id}/reply`
  - `POST /api/chats/send`
- **Database:** `forums`, `discussions`, `comments`, `messages`.
- **Events Published:**
  - `NewCommentPosted` (Memicu notifikasi ke pembuat *thread*).
  - `NewMessageReceived`.
- **Dependencies:** **Core** (User ID), **Learning** (Forum terikat pada ID Course/Lesson).

---

## 5. Analytics Module
Sistem analitik *read-heavy* untuk kebutuhan pelaporan.

- **Responsibility:** Melacak event (Tracking), menyajikan laporan penjualan untuk instruktur (Reporting), dan wawasan belajar untuk HR/Admin (Insights).
- **API Contracts:**
  - `POST /api/analytics/track` (Menerima raw event dari frontend, misal `video_paused`, `button_clicked`).
  - `GET /api/reports/sales` (Untuk Dashboard Instruktur).
  - `GET /api/reports/engagement` (Untuk B2B / HR).
- **Database:** Diarahkan ke database OLAP seperti **ClickHouse** (tabel seperti `event_logs`, `daily_sales_aggregates`).
- **Events Published:** Biasanya modul ini menjadi *Consumer* pasif (mendengarkan event dari RabbitMQ/NATS), namun bisa memicu alarm (misal `TrafficSpikeDetected`).
- **Dependencies:** Tidak bergantung langsung pada modul lain, ia memanen data secara asinkron (Event-Driven) dari **Learning** dan **Business**.

---

## 6. Administration Module
Modul *back-office* untuk operasional platform.

- **Responsibility:** Mengelola pengguna yang bermasalah (User Management), meninjau pendaftaran instruktur baru (Instructor Management), meninjau draf kursus sebelum tayang (Course Moderation), dan mencatat seluruh aksi admin (Audit Log).
- **API Contracts:**
  - `GET /api/admin/users`
  - `PUT /api/admin/users/{id}/suspend`
  - `POST /api/admin/courses/{id}/approve`
  - `GET /api/admin/audit-logs`
- **Database:** Memiliki tabel sendiri seperti `audit_logs`, `moderation_queues`, dan memiliki akses administratif lintas-domain (melalui *API internal* modul lain).
- **Events Published:**
  - `CourseApproved` (Memicu notifikasi ke instruktur).
  - `UserSuspended`.
- **Dependencies:** Modul ini adalah *Observer*. Menggunakan API dari modul **Core** dan **Learning** untuk memodifikasi state sistem, serta menyimpan aksinya ke tabel `audit_logs`.