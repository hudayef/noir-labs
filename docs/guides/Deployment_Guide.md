# Deployment Guide

Panduan tingkat tinggi untuk men-deploy Enterprise LMS ke lingkungan Produksi.

## 1. Persiapan Cloud Infrastructure
Disarankan menggunakan penyedia Cloud modern (AWS, GCP, atau Azure).
- **Compute:** Cluster Kubernetes (EKS/GKE) atau Docker Swarm/ECS. (Bisa juga VPS EC2 ukuran besar untuk tahap awal).
- **Database:** Sangat tidak disarankan menjalankan Database di dalam Container Docker untuk *Production*. Gunakan layanan Managed Database seperti **Amazon RDS for PostgreSQL**.
- **Cache/Queue:** Gunakan **Amazon ElastiCache (Redis)**.
- **Storage:** Gunakan **Amazon S3** (MinIO lokal hanya untuk tahap *development*).

## 2. CI/CD GitHub Actions
GitHub Actions telah dikonfigurasi (`.github/workflows/ci.yml`).
Untuk mengubahnya menjadi skrip *Continuous Deployment*:
1. Tambahkan *Job* baru bernama `deploy` yang bergantung (`needs`) pada `backend-tests` dan `frontend-tests`.
2. Job ini akan:
   - Login ke *Docker Registry* (Amazon ECR / Docker Hub).
   - Menjalankan `docker build -t lms_backend:latest ./backend` dan mem-pushnya.
   - Menggunakan *kubectl* atau *AWS CLI* untuk memperbarui layanan aplikasi di server: `kubectl set image deployment/lms-backend lms-backend=lms_backend:latest`.

## 3. Optimasi Server Production (Laravel)
JANGAN PERNAH menyalakan `APP_DEBUG=true` di server produksi.
Setelah kode berada di server produksi, jalankan rutinitas optimasi:
```bash
# Matikan debugging
php artisan env:decrypt # (Opsional jika pakai vault)

# Optimasi Autoloader
composer install --optimize-autoloader --no-dev

# Cache Konfigurasi & Route (SANGAT PENTING UNTUK PERFORMA)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## 4. Reverse Proxy & SSL (Nginx)
Container Frontend Nginx (`lms_frontend`) telah diatur agar bertindak sebagai web server.
Namun, di depannya Anda WAJIB menaruh Load Balancer (misal: AWS ALB) atau Nginx utama yang mengurus **Sertifikat SSL (HTTPS)**. NATS, ClickHouse, dan Postgres harus ditaruh di *Private Subnet* dan tidak boleh memiliki alamat IP publik secara langsung.