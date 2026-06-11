# Architecture Guide

Platform LMS ini menggunakan desain berskala *Enterprise*, agar tidak terjadi kebingungan struktural (*spaghetti code*) seiring bertambahnya fitur. Silakan rujuk ke diagram Mermaid yang ada di dokumentasi sebelumnya untuk peta visualnya.

## 1. Backend: Modular Monolith (Clean Architecture)
Kita tidak menggunakan struktur standar MVC Laravel (`app/Models`, `app/Controllers`).
Semua logika bisnis dibagi ke dalam empat (4) layer utama di dalam folder `app/`:

1. **Domain (`app/Domain/`)**: Hati aplikasi. Berisi Entitas murni (misal: `UserEntity`) dan Interface Repositori. Tidak boleh memanggil fungsi Laravel atau ORM Eloquent di sini.
2. **Application (`app/Application/`)**: Berisi DTO (Data Transfer Object) dan *Services* (Use Case).
   - *Aturan:* Service hanya menerima DTO (bukan Request class), mengeksekusi logika, memanggil Repositori, dan mengembalikan Entity.
3. **Infrastructure (`app/Infrastructure/`)**: Tempat kode kotor berada. Berisi Model Eloquent (`app/Infrastructure/User/Models/User.php`), implementasi Repositori SQL, panggilan Redis, dan integrasi API pihak ketiga (AWS, Midtrans).
4. **Presentation (`app/Presentation/`)**: Wajah aplikasi (API). Berisi Controller, Form Request (Validasi), Middleware, dan API Resource JSON.
   - *Aturan Clean Controller:* Controller tidak boleh memiliki `if-else` untuk bisnis logik. Panggil form request, buat DTO, panggil Service, kembalikan Resource. Itu saja.

## 2. Frontend: Vue 3 (Composition API)
Arsitektur Frontend mematuhi *Separation of Concerns*:
- **Views/Pages (`src/pages/`)**: Komponen makro yang mewakili satu halaman URL. Menggunakan `<script setup>`.
- **Components (`src/components/`)**: Dibagi dua: `ui` (Shadcn standar) dan `shared` (Komponen spesifik aplikasi seperti *VideoPlayer*).
- **State (`src/store/`)**: Pinia digunakan secara konservatif. Hindari menaruh *semua hal* di Pinia. Hanya data global seperti *Auth Sesi*, *Cart*, atau *Theme* yang berada di sini.
- **Services (`src/services/`)**: Jangan memanggil `axios.get()` di dalam komponen `.vue`. Buat *service file* terpisah untuk memudahkan unit testing dan penanganan token (Interceptors).

## 3. Infrastruktur & Analitik
Untuk menahan *load* puluhan ribu *events* klik per detik (Analytics), kami menggunakan:
- **NATS:** Sebagai penampung peluru (Message Broker).
- **ClickHouse:** Menggunakan skema *MergeTree* dan *Materialized Views* untuk memakan event dan merender grafik mili-detik (Real-time reporting).
- Kami tidak menyimpan jejak klik/video (logs) di PostgreSQL. PostgreSQL murni untuk Transaksi (Auth, Beli Kursus).