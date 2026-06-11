# Installation Guide

Dokumen ini memandu Anda (Developer Baru) untuk menjalankan proyek Enterprise LMS di komputer lokal Anda dari nol. **Ikuti panduan ini langkah demi langkah tanpa dilewati.**

## Kebutuhan Sistem (Prerequisites)
Pastikan Anda sudah menginstal aplikasi berikut sebelum mulai:
- **Git** (untuk *clone* repo)
- **Docker & Docker Compose** (Minimal versi v2.20+)
- **PHP 8.4+** (Hanya diperlukan jika ingin menjalankan *Artisan/Composer* secara native di luar Docker)
- **Composer** v2+
- **Node.js** v20+ & **npm**

## Langkah-Langkah Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/your-org/enterprise-lms.git
cd enterprise-lms
```

### 2. Jalankan Infrastruktur (Docker Compose)
Kita akan menyalakan semua service yang dibutuhkan backend (Postgres, Redis, Meilisearch, dll).
```bash
# Perintah ini akan menarik (pull) image docker (bisa memakan waktu 5-10 menit)
docker compose up -d postgres redis meilisearch clickhouse minio nats
```
*Tip: Pastikan tidak ada aplikasi di komputer Anda yang menggunakan port `5432` (Postgres) atau `6379` (Redis).*

### 3. Setup Backend (Laravel)
Buka terminal baru, masuk ke direktori `backend`:
```bash
cd backend

# Copy konfigurasi environment
cp .env.example .env

# Install dependensi PHP (Gunakan sudo jika di Linux dan bermasalah)
composer install

# Generate application key
php artisan key:generate

# Jalankan migrasi dan seeding database utama
php artisan migrate:fresh --seed
```
*Catatan: File `.env` sudah diatur secara default agar terkoneksi dengan database PostgreSQL di dalam Docker container (Host: `127.0.0.1`, Port: `5432`, User: `lms_user`).*

### 4. Menjalankan Backend API
Karena kita di lingkungan lokal, Anda bisa menjalankan *built-in server* Laravel:
```bash
php artisan serve
```
Backend API akan berjalan di `http://127.0.0.1:8000`.

### 5. Setup Frontend (Vue 3)
Buka terminal baru, masuk ke direktori `frontend`:
```bash
cd frontend

# Install dependensi Node.js
npm install

# Jalankan Vite Development Server
npm run dev
```
Frontend akan terbuka di `http://localhost:5173` (port dapat bervariasi).

## Verifikasi Instalasi
Buka browser dan arahkan ke `http://localhost:5173`. Jika Anda melihat halaman **Enterprise LMS Landing Page** dan bisa bernavigasi ke halaman *Login*, maka instalasi Anda **SUKSES**.

*(Lanjutkan membaca `Troubleshooting_Guide.md` jika Anda menemui layar putih/kegagalan koneksi DB).*