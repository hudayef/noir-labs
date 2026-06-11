# API Documentation & Contracts

Proyek ini menggunakan arsitektur **RESTful JSON API**.

## Aturan Umum
1. **Base URL Lokal:** `http://127.0.0.1:8000/api`
2. **Format Data:** Semua respons wajib dikembalikan dalam bentuk JSON standar.
3. **Standar Respons:**
   ```json
   {
       "success": true, // atau false
       "message": "Pesan deskriptif",
       "data": { ... } // Objek tunggal atau array
   }
   ```

## Autentikasi
Kami menggunakan **Laravel Sanctum**.
1. Lakukan request ke `POST /api/login` atau `POST /api/v2/register`.
2. Tangkap `data.token` dari respons.
3. Sisipkan pada setiap permintaan selanjutnya di Header HTTP:
   `Authorization: Bearer {token}`

---

## Modul Core (User & Auth)

### 1. Register User
`POST /api/v2/register`
- **Tujuan:** Membuat akun baru (Student/Instructor).
- **Body (JSON):**
  ```json
  {
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "password": "Password123!",
      "password_confirmation": "Password123!"
  }
  ```
- **Response (201 Created):**
  ```json
  {
      "success": true,
      "message": "User registered successfully",
      "data": {
          "id": "uuid-1234...",
          "name": "Budi Santoso",
          "email": "budi@example.com",
          "is_active": true,
          "created_at": "2026-06-10T..."
      }
  }
  ```

### 2. Login
`POST /api/login`
- **Tujuan:** Mendapatkan token otorisasi.
- **Body (JSON):** `{"email": "budi@example.com", "password": "Password123!"}`
- **Response (200 OK):**
  Akan mengembalikan `token` dan objek `user`.

---

## Error Handling
Jika terjadi kesalahan input atau *server error*, format respons tetap konsisten:
**Contoh 422 Unprocessable Entity (Validasi Gagal):**
```json
{
    "message": "Data yang diberikan tidak valid.",
    "errors": {
        "email": ["Email tersebut sudah digunakan."]
    }
}
```