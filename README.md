# Mini Enterprise LMS Platform

Selamat datang di repositori utama **Enterprise LMS Platform**. Sistem ini adalah platform *Learning Management System* skala *enterprise* (mirip Udemy/Coursera) yang dirancang agar *Production Ready*, *Highly Scalable*, dan sangat *Maintainable*.

## Deskripsi Singkat

Sistem ini menggunakan arsitektur **Clean Architecture (Modular Monolith)** pada Backend, dan pendekatan **Component-Driven** di Frontend. Seluruh infrastruktur disatukan menggunakan *Docker* untuk konsistensi lingkungan pengembangan hingga produksi.

### Tech Stack Utama:
- **Frontend:** Vue 3, TypeScript, Pinia, Tailwind CSS, Shadcn Vue.
- **Backend:** Laravel 12 (PHP 8.4+), Sanctum (API Auth).
- **Database:** PostgreSQL (Primary), Redis (Cache/Queue), ClickHouse (Analytics OLAP).
- **Messaging & Search:** NATS (Message Broker), Meilisearch (Search Engine).
- **Storage:** MinIO (S3 Compatible Object Storage).
- **DevOps & Observability:** Docker, GitHub Actions, Prometheus, Loki, Grafana.

---

## Daftar Panduan Dokumentasi (Documentation Hub)

Agar *Onboarding* Anda sebagai *developer* baru berjalan mulus tanpa hambatan, harap baca panduan teknis berikut secara berurutan:

1. 🚀 **[Installation Guide](docs/guides/Installation_Guide.md)** - Panduan lengkap dari *clone* repositori hingga aplikasi menyala di mesin lokal Anda.
2. 🏛️ **[Architecture Guide](docs/guides/Architecture_Guide.md)** - Pahami *Clean Architecture*, *Domain Driven Design (DDD)*, dan arsitektur data (*ClickHouse & NATS*).
3. 🔌 **[API Documentation](docs/guides/API_Documentation.md)** - Standar kontrak API, autentikasi (Bearer Token), dan cara integrasi frontend-backend.
4. 🔒 **[Security Guide](docs/guides/Security_Guide.md)** - Penjelasan tentang *Rate Limiting*, JWT/Sanctum Security, Hashing, dan RBAC (Role-Based Access Control).
5. 🚢 **[Deployment Guide](docs/guides/Deployment_Guide.md)** - Langkah-langkah untuk membawa aplikasi ini ke lingkungan *Production* (AWS/GCP, K8s).
6. 🚑 **[Troubleshooting Guide](docs/guides/Troubleshooting_Guide.md)** - Daftar error umum yang sering terjadi saat *setup* Docker atau Composer dan cara mengatasinya.
7. 🤝 **[Contributor Guide](docs/guides/Contributor_Guide.md)** - Standar penulisan kode (*Clean Code*), aturan *Git Branching*, dan standar pembuatan *Pull Request*.

---

*Dikembangkan untuk memberikan pendidikan berkualitas dengan standar teknologi tertinggi.*
