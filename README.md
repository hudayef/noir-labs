# Enterprise LMS Platform

Platform e-learning (LMS) berskala enterprise yang dirancang menggunakan pendekatan **Clean Architecture** & **Domain-Driven Design (DDD)**.
Proyek ini dibangun untuk memenuhi kebutuhan standar industri yang *Production Ready, Maintainable, dan Scalable*.

## Technology Stack

### Frontend (User Interface)
- **Vue 3** & **TypeScript**
- **Pinia** (State Management)
- **Vue Router**
- **Tailwind CSS** (Styling)
- **Shadcn Vue** (UI Components)

### Backend (API & Core Logic)
- **Laravel 12** & **PHP 8.4+**
- **Sanctum** (Authentication)
- PostgreSQL (Primary Database)

### Infrastructure & Services (Docker Compose)
- **Redis** (Caching & Queue)
- **MinIO** (S3-Compatible Object Storage for Videos/Files)
- **Meilisearch** (Instant Search Engine)
- **ClickHouse** (High-Performance Analytics DB)
- **NATS** (Message Broker / Event-Driven Architecture)
- Grafana, Prometheus, Loki (Monitoring)

---

## Panduan Memulai untuk Developer Pemula

Proyek ini menggunakan **Docker Compose** agar semua *dependencies* (database, cache, search engine) dapat berjalan dengan mudah di mesin lokal Anda.

### 1. Kebutuhan Sistem (Prerequisites)
Pastikan Anda telah menginstal:
- Docker & Docker Compose
- PHP 8.4 (Jika ingin menjalankan *artisan* lokal)
- Composer
- Node.js (versi 20+) & npm

### 2. Setup Infrastruktur (Layanan Database, dll)
Masuk ke root direktori proyek, lalu jalankan:
```bash
docker compose up -d
```
Perintah ini akan menjalankan PostgreSQL, Redis, MinIO, Meilisearch, ClickHouse, dan NATS di latar belakang.

### 3. Setup Backend (Laravel)
```bash
cd backend
cp .env.example .env
# Sesuaikan .env untuk DB_PORT=5432 (PostgreSQL) dll.
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

### 4. Setup Frontend (Vue 3)
```bash
cd frontend
npm install
npm run dev
```

---

## Arsitektur & Aturan Pengembangan (Development Rules)

Proyek ini tidak menggunakan struktur MVC standar Laravel, melainkan **Modular Monolith (DDD)**.

1. **Domain Logic:** Folder `app/Domain/` berisi semua modul seperti `Auth`, `Course`, `Enrollment`.
2. **Controllers:** Jangan simpan di `app/Http/Controllers`, letakkan di `app/Domain/[NamaDomain]/Controllers`.
3. **Komunikasi Antar Modul:** Gunakan interface (API Internal) atau sistem antrean (Laravel Queue / NATS) agar modul tidak saling bergantung erat (loose coupling).

### Dokumentasi Lainnya
Silakan periksa direktori `docs/` untuk membaca:
- Product Requirement (BRD/SRS)
- Architecture Diagram (C4 Model & ERD)

---
*Dibangun oleh Tim Software House Enterprise (CTO, Architect, Vue Dev, Laravel Dev).*