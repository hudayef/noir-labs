# Tahap 1: Product & Requirements Analysis

LMS Platform (Enterprise Grade)

## 1. Product Vision
Menjadi platform Learning Management System (LMS) terdepan yang menyediakan pengalaman belajar berkualitas tinggi, interaktif, dan terukur. Platform ini dirancang untuk mendemokratisasi akses pendidikan dengan menghubungkan para ahli (instruktur) dengan pembelajar (students) melalui ekosistem yang scalable, aman, dan mudah digunakan (baik oleh end-user maupun developer yang melakukan maintenance).

## 2. Business Model
Model bisnis yang diterapkan adalah model *Hybrid*:
- **B2C (Business to Consumer):**
  - **Pay-per-Course (A la carte):** Pengguna membeli kursus secara satuan dengan akses seumur hidup untuk kursus tersebut.
  - **Subscription (Langganan):** Pengguna membayar biaya bulanan/tahunan untuk mengakses seluruh atau sebagian besar katalog kursus (mirip LinkedIn Learning).
- **B2B (Business to Business):**
  - Menawarkan paket perusahaan untuk pelatihan karyawan dengan fitur pelaporan (analytics) yang mendalam untuk HR/Manager.
- **Revenue Sharing:** Pembagian hasil antara platform dan instruktur (misalnya 70% untuk instruktur, 30% untuk platform) untuk setiap penjualan kursus atau berdasarkan watch-time bagi pengguna langganan.

## 3. Target Market
- **Individu (Students):**
  - Profesional muda yang ingin *upskilling/reskilling*.
  - Mahasiswa yang mencari materi pelengkap di luar kurikulum kampus.
  - Hobbies yang ingin mempelajari keterampilan baru.
- **Kreator/Ahli (Instructors):**
  - Praktisi industri, akademisi, dan kreator konten yang ingin memonetisasi pengetahuan mereka.
- **Organisasi/Perusahaan:**
  - Perusahaan yang membutuhkan platform pelatihan internal yang reliabel tanpa harus membangun sistem sendiri dari nol.

## 4. User Persona
- **Persona 1: Rina, Mahasiswi/Fresh Graduate (Student)**
  - *Goal:* Ingin belajar skill UI/UX Design untuk mencari kerja.
  - *Pain point:* Budget terbatas, butuh materi terstruktur dan sertifikat yang diakui.
- **Persona 2: Budi, Senior Developer (Instructor)**
  - *Goal:* Ingin membuat kelas programming dan mendapatkan penghasilan pasif.
  - *Pain point:* Tidak ingin repot memikirkan infrastruktur hosting video dan pembayaran. Butuh dashboard analytics yang jelas.
- **Persona 3: Sarah, HR Manager (B2B Admin)**
  - *Goal:* Melacak progress belajar karyawan di berbagai departemen.
  - *Pain point:* Kesulitan melihat mana karyawan yang aktif belajar dan membutuhkan laporan terpusat.
- **Persona 4: Andi, Platform Admin (Super Admin)**
  - *Goal:* Menjaga kualitas konten dan kestabilan platform.
  - *Pain point:* Membutuhkan tool untuk review konten, menangani laporan pelanggaran, dan memantau kesehatan sistem.

## 5. Stakeholder Analysis
- **Investor/Founders:** Fokus pada pertumbuhan revenue, user acquisition, dan scalability.
- **Pengguna Akhir (Students/Instructors):** Fokus pada kemudahan penggunaan (UI/UX), ketersediaan materi, dan kelancaran akses video.
- **Tim Developer & DevOps:** Fokus pada arsitektur yang *clean*, mudah dipelihara, dokumentasi lengkap, dan infrastruktur yang stabil.
- **Tim Operasional/CS:** Membutuhkan panel admin yang kuat untuk menangani *dispute*, refund, dan moderasi konten.
- **Payment Gateway Providers:** Ekosistem pihak ketiga untuk memfasilitasi transaksi yang aman.

## 6. Business Requirement Document (BRD)
**Tujuan Bisnis:** Meluncurkan platform LMS dalam waktu 6 bulan yang dapat menangani minimal 10.000 pengguna aktif harian pada peluncuran perdana dengan uptime 99.9%.
**Kebutuhan Utama Bisnis:**
- Sistem registrasi dan manajemen akun multi-peran (Student, Instructor, Admin, B2B Admin).
- Sistem manajemen kursus (pembuatan modul, unggah video, kuis, dan materi teks).
- Integrasi pembayaran otomatis (Payment Gateway).
- Sistem analitik untuk memantau performa penjualan dan tingkat penyelesaian kursus.
- Sistem sertifikasi otomatis setelah kursus selesai.

## 7. Software Requirement Specification (SRS)
Platform berbasis web dengan pendekatan *Single Page Application* (SPA) menggunakan Vue 3 di frontend dan Laravel 12 di backend sebagai RESTful/GraphQL API. Sistem akan menggunakan arsitektur berbasis microservices secara logis (Domain-Driven Design) dalam sebuah monolith (Modular Monolith) di fase awal, siap untuk di-scale ke microservices fisik jika diperlukan.
*Infrastruktur didukung penuh oleh Docker.*

## 8. Functional Requirement
- **Authentication & Authorization:** Register, Login, OAuth (Google/Github), Reset Password, Role-Based Access Control (RBAC).
- **Course Management:**
  - Instruktur dapat membuat draft, upload video (dengan integrasi storage MinIO), membuat kuis, dan publish kursus.
  - Sistem review kursus oleh Admin sebelum tayang.
- **Learning Experience:**
  - Video player yang responsif dengan pelacakan progres (resume video, penandaan selesai).
  - Sistem kuis (pilihan ganda, esai singkat) dengan penilaian otomatis.
  - Forum diskusi per sesi/kursus.
- **Transaction & Monetization:**
  - Cart system dan checkout.
  - Integrasi dengan payment gateway.
  - Dashboard penghasilan (revenue) untuk instruktur.
- **Search & Discovery:**
  - Pencarian materi secara instan dengan typo-tolerance menggunakan Meilisearch.
  - Sistem rating dan ulasan (review) dari siswa.
- **Analytics:**
  - Pelaporan data massal untuk performa belajar dan keuangan menggunakan ClickHouse.

## 9. Non Functional Requirement
- **Performance:** Response time API rata-rata di bawah 200ms. Pencarian via Meilisearch < 50ms.
- **Scalability:** Mampu menangani *spike traffic* hingga 10x lipat beban normal menggunakan Laravel Queue dan caching Redis.
- **Reliability:** Uptime infrastruktur 99.9%. Penanganan error asinkron tanpa mengganggu pengalaman pengguna menggunakan NATS.
- **Security:** Implementasi JWT/Sanctum untuk autentikasi, sanitasi input untuk mencegah XSS/SQL Injection, Rate Limiting, CSRF protection, data sensitif (password) di-hash menggunakan Argon2/Bcrypt.
- **Maintainability:** Clean Code, SOLID principles, Repository/Service Pattern, komentar di setiap class/metode utama, serta dokumentasi API via OpenAPI/Swagger.

## 10. Risk Analysis
- **Risiko 1: Biaya Penyimpanan & Bandwidth Video Membengkak.**
  - *Mitigasi:* Menggunakan MinIO sebagai storage tiering di awal, merencanakan kompresi video, dan mempertimbangkan CDN.
- **Risiko 2: Pembajakan Konten.**
  - *Mitigasi:* Implementasi proteksi standar seperti watermarking video, HLS streaming dengan signed URL.
- **Risiko 3: Kesulitan Pemeliharaan oleh Developer Baru.**
  - *Mitigasi:* Dokumentasi ekstensif (Technical Writer), penerapan Clean Architecture, dan *code review* yang ketat (CI/CD via GitHub Actions).
- **Risiko 4: Payment Gateway Downtime.**
  - *Mitigasi:* Menggunakan Message Broker (NATS) atau Laravel Queue dengan mekanisme retry untuk memastikan konsistensi data transaksi.

## 11. MVP Scope (Minimum Viable Product)
- **Modul User:** Autentikasi (Email/Password), Profil User, RBAC sederhana (Admin, Instructor, Student).
- **Modul Course:** CRUD kursus, upload materi (video mp4 standar via MinIO, PDF), pembuatan silabus sederhana.
- **Modul Belajar:** Video player dasar, *progress tracking* (centang manual/otomatis per modul).
- **Modul Transaksi:** Beli putus kursus menggunakan 1 Payment Gateway lokal.
- **Modul Pencarian:** Pencarian teks biasa (kategori dan judul).

## 12. Future Scope (Pasca MVP)
- Transcoding video adaptif (HLS/DASH).
- Model *Subscription* (Langganan B2C) & Portal B2B Analytics.
- *Real-time collaboration* / Live class / Webinar.
- Rekomendasi kursus berbasis AI (AI Engineer).
- Gamifikasi (Badges, Leaderboard).
- Mobile Apps (Flutter/React Native).
