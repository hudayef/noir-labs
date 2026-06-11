# Tahap 9: Analytics Dashboards & Reporting Features

Sistem pelaporan dan dasbor analitik ini mengambil data dari **ClickHouse** (Reporting Service) yang telah dioptimasi dengan *Materialized Views*. Metrik disajikan secara terpisah sesuai dengan hak akses (Role) pengguna untuk memastikan privasi dan relevansi data.

---

## 1. Student Dashboard
Difokuskan pada motivasi dan pencapaian individu.

- **Learning Progress:** Persentase (%) penyelesaian dari setiap kursus yang sedang diikuti. Disajikan dalam bentuk *Circular Progress Bar*.
- **Learning Streak:** Jumlah hari berturut-turut siswa login dan menonton minimal 1 video (Gamifikasi). Menumbuhkan kebiasaan belajar.
- **Weekly Study Time:** Total jam dan menit yang dihabiskan untuk menonton video dalam 7 hari terakhir. Disajikan dalam *Bar Chart* harian.
- **Quiz Score:** Nilai rata-rata dari kuis-kuis yang telah diselesaikan.
- **Assignment Score:** Nilai rata-rata tugas praktik yang dinilai oleh instruktur.

## 2. Instructor Dashboard
Difokuskan pada performa kursus dan keuntungan moneter instruktur terkait.

- **Student Count:** Total siswa unik yang terdaftar pada semua kursusnya (aktif maupun tidak).
- **Completion Rate:** Rata-rata persentase siswa yang menyelesaikan kursusnya hingga 100%. Berguna untuk menilai apakah materi terlalu sulit.
- **Revenue:** Total pendapatan kotor dan bersih (setelah potong komisi platform) di bulan berjalan. Disajikan dalam *Line Chart* tren pendapatan harian.
- **Enrollment Trend:** Jumlah siswa baru yang mendaftar setiap hari/minggu.
- **Course Popularity:** Peringkat kursus milik instruktur tersebut berdasarkan jumlah pendaftaran, tayangan, dan rating (Review).

## 3. Admin Dashboard
Difokuskan pada operasional rutin dan kesehatan finansial platform secara makro.

- **User Growth:** Pertumbuhan pendaftar (registrasi baru) per bulan (misal: +15% dari bulan lalu).
- **Revenue Growth:** Total seluruh pendapatan platform (kotor) dan profit platform (bersih).
- **Active Users:** Jumlah pengguna yang sedang online atau login dalam 24 jam terakhir.
- **Course Statistics:** Total kursus yang aktif, draf, menunggu tinjauan (Pending), dan rata-rata rating platform secara keseluruhan.

## 4. Super Admin Dashboard (Executive & Growth Metrics)
Difokuskan pada keputusan strategis tingkat eksekutif, menggunakan perhitungan analisis data *Big Data*.

- **DAU (Daily Active Users):** Jumlah pengguna unik per hari.
- **WAU (Weekly Active Users):** Jumlah pengguna unik per minggu.
- **MAU (Monthly Active Users):** Jumlah pengguna unik per bulan (Indikator utama pertumbuhan startup).
- **Churn Rate:** Persentase pengguna yang membatalkan langganan (Subscription) atau tidak login kembali selama 30 hari.
- **Retention Rate:** Kebalikan dari Churn; persentase pengguna yang kembali login atau mendaftar kursus baru di bulan kedua, ketiga, dst. (*Cohort Analysis*).
- **Conversion Rate:** Persentase pengunjung (Landing Page) yang pada akhirnya membayar (Checkout) menjadi siswa berbayar.

---

## 5. Fitur Ekspor & Pelaporan Tambahan (Reporting Features)

Platform Enterprise membutuhkan fleksibilitas dalam mengolah data di luar sistem.

### A. Export Excel
- **Tujuan:** Memungkinkan B2B Admin atau Instructor untuk mengolah angka lebih dalam menggunakan Pivot Table atau Macro di Microsoft Excel.
- **Mekanisme:** Frontend menembak API `GET /api/reports/export/excel?type=revenue`. Backend menggunakan library seperti **Laravel Excel (Maatwebsite)** yang men-query ClickHouse/Postgres, memformat datanya menjadi `.xlsx`, dan mengirimkannya kembali sebagai *Stream Download*.

### B. Export PDF
- **Tujuan:** Menyediakan laporan formal untuk rapat manajerial (C-Level) yang formatnya tetap (*read-only*) dan rapi.
- **Mekanisme:** Frontend (atau Backend menggunakan library **DomPDF / Snappy / Browsershot**) men-render halaman HTML berisi tabel dan grafik (bisa menggunakan Chart.js server-side) lalu mengubahnya menjadi file PDF dengan kop surat perusahaan.

### C. Scheduled Reports (Cron / Laravel Scheduler)
- **Tujuan:** Memberikan kemudahan bagi instruktur atau admin yang tidak sempat login, dengan mengirimkan laporan ke email mereka secara berkala.
- **Mekanisme:**
  - Pengguna menyetel preferensi (misal: "Kirim ringkasan pendapatan setiap hari Senin jam 08:00").
  - **Laravel Scheduler** (`cron`) memicu **Job (Queue)** setiap jam 08:00.
  - *Worker* mem-parsing data dari ClickHouse, membuat *template email* Blade yang indah, melampirkan file PDF/Excel jika perlu.
  - Email dikirim secara asinkron menggunakan layanan seperti AWS SES atau Sendgrid agar tidak menghambat *Job* lainnya.