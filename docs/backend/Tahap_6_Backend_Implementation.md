# Tahap 6: Backend Implementation (Laravel)

Sesuai dengan prinsip **Clean Architecture**, proyek ini menggunakan struktur folder kustom di dalam `app/`:
- **Domain:** Berisi entitas bisnis murni dan interface (tidak bergantung pada framework).
- **Application:** Berisi *Use Cases* (Services) dan *Data Transfer Objects* (DTO).
- **Infrastructure:** Berisi implementasi repositori (berhubungan langsung dengan database/ORM Eloquent) dan Models.
- **Presentation:** Berisi API Controllers, Request Validation, API Resources, dan Middleware.

Dalam tahap ini, saya telah mengimplementasikan modul **Core (User/Auth)** sebagai representasi dan cetak biru (blueprint) untuk modul-modul lainnya (Learning, Business, dll).

---

## Penjelasan Struktur File (Blueprint Modul User/Auth)

### 1. Migration
Berada di `database/migrations/0001_01_01_000000_create_users_table.php` (telah dibuat sebelumnya).
**Penjelasan:** Berisi definisi struktur tabel database untuk entitas User menggunakan PostgreSQL (UUID).

### 2. Infrastructure/User/Models/User.php
**Penjelasan:** Ini adalah Eloquent Model Laravel. Digunakan secara eksklusif oleh layer *Infrastructure* untuk berinteraksi dengan database. Layer *Application* tidak boleh menggunakan ini secara langsung, melainkan harus lewat Repositori.

### 3. Domain/User/Repositories/UserRepositoryInterface.php
**Penjelasan:** Interface (Kontrak) yang mendefinisikan apa saja yang bisa dilakukan terhadap data User (seperti `findByEmail`, `create`). Layer *Application* hanya mengetahui interface ini.

### 4. Infrastructure/User/Repositories/UserRepository.php
**Penjelasan:** Implementasi nyata dari `UserRepositoryInterface`. File ini yang memanggil `User::create()` (Eloquent Model). Jika suatu hari kita berpindah dari Eloquent ke Raw SQL, kita hanya mengubah file ini.

### 5. Application/User/DTO/RegisterUserDTO.php
**Penjelasan:** *Data Transfer Object* (DTO). Digunakan untuk membawa data yang bersih dan sudah divalidasi dari *Presentation Layer* (Controller) ke *Application Layer* (Service).

### 6. Application/User/Services/AuthService.php
**Penjelasan:** *Service Layer*. Berisi *Business Logic* (Use Case). Menerima DTO, memanggil *UserRepository* untuk membuat user, memanggil *Hasher* untuk enkripsi, dan menghasilkan respon/Data.

### 7. Presentation/User/Requests/RegisterUserRequest.php
**Penjelasan:** Menggunakan fitur *Form Request Validation* Laravel. Bertugas memvalidasi data mentah dari HTTP sebelum masuk ke Controller. Jika gagal, otomatis mengembalikan respons `422 Unprocessable Entity`.

### 8. Presentation/User/Controllers/AuthController.php
**Penjelasan:** Hanya bertugas menerima HTTP Request, memanggil Form Request untuk validasi, membungkus data ke dalam DTO, mengirimkannya ke *AuthService*, dan mengembalikan respons HTTP. **Controller tidak boleh mengandung logika bisnis (Clean Controller).**

### 9. Presentation/User/Resources/UserResource.php
**Penjelasan:** Format balasan JSON (Data Transformer). Menyembunyikan field rahasia seperti `password_hash` dari respons API dan melakukan standarisasi bentuk respons.

### 10. Presentation/User/Policies/UserPolicy.php
**Penjelasan:** Menyimpan logika *Authorization*. Misalnya, seorang pengguna biasa tidak bisa menghapus (delete) pengguna lain. Hanya Admin yang boleh.

### 11. Presentation/Middleware/RoleMiddleware.php
**Penjelasan:** Menjaga akses *route* API berdasarkan role. Misalnya, endpoint `/api/admin/*` hanya bisa diakses oleh user dengan role `admin`.

### 12. Unit Test (tests/Unit/Application/User/AuthServiceTest.php)
**Penjelasan:** Menguji logika bisnis di `AuthService` murni menggunakan Data Tiruan (Mocking `UserRepository`), untuk memastikan registrasi pengguna mengembalikan entitas yang valid, tanpa benar-benar menyentuh database.

---
*(Semua file ini telah direpresentasikan dan dipetakan di dalam repository)*.