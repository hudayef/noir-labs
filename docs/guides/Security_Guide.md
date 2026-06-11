# Security Guide

Panduan ini mengatur standar keamanan minimum yang harus diterapkan oleh semua *developer* saat menyentuh kode.

## 1. Authentication (Autentikasi)
- **Hashing:** Semua *password* di-hash menggunakan driver `Bcrypt` atau `Argon2i` (standar Laravel). **JANGAN PERNAH** mengenkripsi password dengan MD5/SHA1, dan jangan pernah mencetaknya di log.
- **Tokens (Sanctum):** Token bersifat sensitif. Jangan pernah mengembalikan `password_hash` atau `remember_token` di dalam payload JSON. Selalu gunakan `UserResource` untuk menyaring atribut.
- **Penyimpanan Frontend:** Token disimpan di `localStorage` (hanya untuk SPA internal). Untuk keamanan lebih tinggi (mencegah XSS) pada versi *next-gen*, gunakan `HttpOnly Cookies` melalui *Sanctum stateful auth*.

## 2. Authorization (Otorisasi)
- **RBAC (Role Based Access Control):** Pastikan setiap endpoint yang mengubah data dilindungi oleh Middleware Role. (Contoh: `Route::post('/courses')->middleware('role:instructor')`).
- **Policy (IDOR Prevention):** Pastikan satu *Instructor* tidak bisa mengedit kursus *Instructor* lain. Gunakan **Laravel Policies**.
  ```php
  // Jangan hanya ini:
  $course->update($data);

  // HARUS DICEK:
  if ($request->user()->cannot('update', $course)) {
      abort(403);
  }
  ```

## 3. Input Validation & SQL Injection
- **Validasi Wajib:** Jangan percaya input dari *client* (meskipun frontend sudah memvalidasi). Semua titik masuk (`POST/PUT/PATCH`) **WAJIB** menggunakan `FormRequest`.
- **SQL Injection:** Karena menggunakan Eloquent ORM, PDO *parameter binding* sudah ditangani. *TAPI*, hindari penggunaan `DB::raw()` sembarangan yang disisipi variabel input pengguna.

## 4. Rate Limiting & DDoS Prevention
- Endpoint Auth (Login/Register) rentan terhadap serangan *Brute Force*.
- Pastikan limitasi aktif (Laravel `ThrottleRequests`). Default adalah 60 request per menit. Khusus *Login* di-set ke 5 percobaan per menit.

## 5. Security Audit Log
Setiap kali entitas krusial (User, Course, Order) diubah, entri harus dimasukkan ke tabel `audit_logs` (atau dikirim ke Elasticsearch/Loki) untuk keperluan forensik.