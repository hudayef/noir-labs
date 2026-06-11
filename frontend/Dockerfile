# Tahap 1: Build Vue 3 Frontend
FROM node:20-alpine as build-stage

WORKDIR /app

# Install dependencies
COPY package*.json ./
RUN npm ci

# Copy source code dan build
COPY . .
RUN npm run build

# Tahap 2: Production Server dengan Nginx
FROM nginx:alpine as production-stage

# Copy hasil build ke folder Nginx
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Konfigurasi Nginx untuk menangani Vue Router (History mode)
RUN echo 'server { \
    listen 80; \
    server_name localhost; \
    location / { \
        root /usr/share/nginx/html; \
        index index.html index.htm; \
        try_files $uri $uri/ /index.html; \
    } \
    location /api { \
        proxy_pass http://lms_backend:8000; \
        proxy_set_header Host $host; \
        proxy_set_header X-Real-IP $remote_addr; \
    } \
}' > /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
