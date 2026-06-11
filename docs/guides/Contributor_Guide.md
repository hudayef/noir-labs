# Contributor Guide

Kami menyambut baik kolaborasi tim! Untuk memastikan kode tetap *maintainable* dan sesuai dengan *Enterprise Standards*, harap patuhi aturan di bawah ini sebelum membuat *Pull Request* (PR).

## 1. Standar Penulisan Kode (Clean Code)
1. **Bahasa Pemrograman:** Semua penamaan Variabel, Kelas, Fungsi, dan Database **WAJIB** menggunakan *Bahasa Inggris*. (Contoh: `CourseController`, BUKAN `KursusController`).
2. **Komentar:** Komentar/dokumentasi kode boleh ditulis dalam Bahasa Indonesia agar mudah dipahami developer internal (sesuai arahan CTO).
3. **Kepatuhan SOLID:** Ikuti prinsip SOLID, terutama *Single Responsibility Principle*. Jika class Controller melebihi 100 baris, kemungkinan besar Anda perlu memindahkan logikanya ke dalam *Service*.

## 2. Git Workflow (Git Flow)
Kami menggunakan model *Git Flow* standar:
- Branch `main` (Produksi - Kode yang sudah stabil dan berjalan di server).
- Branch `develop` (Staging - Branch tujuan utama untuk semua fitur baru).
- **Pembuatan Fitur Baru:** Selalu buat branch baru dari `develop`.
  - Format nama branch: `tipe/nama-fitur`.
  - Contoh: `feat/add-quiz-system`, `fix/login-bug`, `refactor/course-service`.

## 3. Pesan Commit (Conventional Commits)
Harap gunakan *Conventional Commits* untuk mempermudah pembacaan riwayat proyek.
- `feat:` Untuk fitur baru.
- `fix:` Untuk perbaikan *bug*.
- `docs:` Untuk perubahan file *Markdown/README*.
- `refactor:` Untuk perubahan kode yang tidak menambah fitur baru (membersihkan kode).
- `test:` Untuk penambahan unit test.
- Contoh: `feat: implement subscription payment gateway`

## 4. Proses Pull Request (PR)
1. Setelah fitur selesai di branch Anda, jalankan *local test*: `php artisan test` dan `npm run build`. Pastikan semuanya LULUS.
2. Push branch Anda ke repository jarak jauh (Remote/GitHub).
3. Buka halaman GitHub, buat **Pull Request** dari branch Anda ke `develop`.
4. Berikan penjelasan pada kotak deskripsi PR:
   - Apa yang diubah?
   - Alasan teknis perubahan ini.
   - Screenshot (jika itu perubahan UI Frontend).
5. Tunggu *Code Review* dari Senior Developer (Setidaknya butuh 1 *Approve* sebelum dapat di-merge). Pipeline CI/CD juga harus berstatus hijau (Lulus).