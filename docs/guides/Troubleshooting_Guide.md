# Troubleshooting Guide

Jika Anda mengalami masalah selama instalasi atau pengembangan, periksa solusi dari error umum di bawah ini.

## 1. Docker Error: `port is already allocated`
**Gejala:** Saat menjalankan `docker compose up`, muncul pesan bahwa port 5432 (Postgres) atau 6379 (Redis) atau 8000 (Backend) sudah digunakan.
**Solusi:**
Anda memiliki service lokal yang sedang berjalan di komputer Anda. Anda harus mematikannya terlebih dahulu:
- Ubuntu/Mac (Postgres): `sudo systemctl stop postgresql`
- Mac (Homebrew): `brew services stop postgresql`
- Atau ubah *port mapping* di file `docker-compose.yml` (misal dari `"5432:5432"` menjadi `"5433:5432"`), lalu sesuaikan DB_PORT di `.env`.

## 2. Laravel Error: `SQLSTATE[08006] [7] connection to server at "127.0.0.1", port 5432 failed`
**Gejala:** Menjalankan `php artisan migrate` gagal konek ke database.
**Solusi:**
1. Pastikan container Docker menyala (`docker ps` dan pastikan `lms_postgres` statusnya `Up`).
2. Tunggu 10-15 detik setelah `docker compose up -d` pertama kali dijalankan, karena Postgres butuh waktu untuk inisialisasi tabel pertama kalinya.
3. Periksa `.env` Anda, pastikan `DB_HOST=127.0.0.1`, `DB_PORT=5432`, `DB_DATABASE=lms_db`, `DB_USERNAME=lms_user`, `DB_PASSWORD=secretpassword`.

## 3. Frontend Error: `Cannot find module '@/...'` atau `Unresolved alias`
**Gejala:** TypeScript atau Vite mengeluh tidak mengenali path alias `@/`.
**Solusi:**
1. Pastikan Anda sudah menjalankan `npm install`.
2. Pastikan file `tsconfig.app.json` (atau `tsconfig.json`) dan `vite.config.ts` sudah selaras konfigurasi *paths/alias*-nya seperti yang ada di setup awal Shadcn.

## 4. Frontend Error: Cors Policy Blocked
**Gejala:** Axios gagal menarik data dari `http://localhost:8000/api` karena CORS.
**Solusi:**
1. Buka file `backend/config/cors.php`.
2. Pastikan `allowed_origins` memiliki entri `['http://localhost:5173', 'http://127.0.0.1:5173']`.
3. Clear cache backend: `php artisan config:clear`.

## 5. Pesan Error tidak jelas (Error 500)
**Gejala:** API mengembalikan HTTP 500 tanpa rincian.
**Solusi:**
- Buka file `backend/.env`, ubah `APP_DEBUG=false` menjadi `APP_DEBUG=true`.
- Jika memakai Docker, Anda juga bisa melihat *log* aplikasi melalui Loki di Grafana (Port 3000), atau cara cepat via terminal: `docker logs lms_backend -f`.