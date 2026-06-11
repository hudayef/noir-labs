# Tahap 7: Frontend Implementation (Vue.js 3 + TypeScript)

Sesuai dengan standar *Enterprise Clean Code*, proyek frontend Vue 3 telah distrukturkan untuk memisahkan antara tampilan (UI), logika state (Pinia), pemanggilan API (Services), dan tipe data (Types).

## Struktur Folder Frontend (`frontend/src/`)

```text
src/
├── assets/         # File statis (Gambar, SVG, dll)
├── components/     # Komponen Reusable
│   ├── ui/         # Komponen Shadcn Vue (Button, Input, dll)
│   └── shared/     # Komponen spesifik domain (Navbar, CourseCard)
├── layouts/        # Layout Wrapper
│   ├── AuthLayout.vue
│   └── DashboardLayout.vue
├── pages/          # Komponen Halaman (View)
│   ├── Home/
│   ├── Auth/
│   └── Dashboard/
├── router/         # Konfigurasi Vue Router & Navigation Guards
├── services/       # Integrasi API Backend
│   ├── api.ts      # Axios instance interceptors
│   └── auth.service.ts
├── store/          # Pinia State Management
│   └── auth.store.ts
└── types/          # TypeScript Interfaces
    └── user.type.ts
```

---

## 1. Types (`src/types/`)
Mendefinisikan *interface* TypeScript yang selaras dengan Entity/DTO di backend Laravel.
File: `user.type.ts`
```typescript
export interface User {
  id: string;
  name: string;
  email: string;
  is_active: boolean;
  created_at: string;
}

export interface AuthResponse {
  success: boolean;
  message: string;
  data: {
    token: string;
    user: User;
  }
}
```

---

## 2. API Integration (`src/services/`)
Menggunakan pola *Service* untuk mengabstraksi pemanggilan Axios, sehingga halaman (pages) tidak memanggil Axios secara langsung.

File: `api.ts` (Instansiasi Axios dengan Interceptor)
```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Otomatis menyisipkan Bearer Token ke setiap request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token && config.headers) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

File: `auth.service.ts`
```typescript
import api from './api';
import type { AuthResponse } from '../types/user.type';

export const AuthService = {
  async register(data: any) {
    const response = await api.post<AuthResponse>('/v2/register', data);
    return response.data;
  },
  async login(data: any) {
    const response = await api.post<AuthResponse>('/login', data);
    return response.data;
  }
};
```

---

## 3. State Management (`src/store/`)
Menggunakan **Pinia** (Setup Store syntax) untuk mengelola state global, seperti status login pengguna.

File: `auth.store.ts`
```typescript
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { User } from '../types/user.type';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(localStorage.getItem('token') || null);

  const isAuthenticated = computed(() => !!token.value);

  function setAuth(userData: User, authToken: string) {
    user.value = userData;
    token.value = authToken;
    localStorage.setItem('token', authToken);
  }

  function logout() {
    user.value = null;
    token.value = null;
    localStorage.removeItem('token');
  }

  return { user, token, isAuthenticated, setAuth, logout };
});
```

---

## 4. Layouts (`src/layouts/`)
Memisahkan kerangka halaman agar kode UI konsisten.
File: `AuthLayout.vue`
```vue
<template>
  <div class="min-h-screen bg-zinc-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-sm border border-zinc-200">
      <router-view />
    </div>
  </div>
</template>
```

---

## 5. Pages & Components (`src/pages/Auth/LoginView.vue`)
Halaman menggunakan *Composition API* (`<script setup>`) dan memanggil komponen **Shadcn Vue** (`Button`, `Input`) yang di-*styling* dengan **Tailwind CSS**.

```vue
<template>
  <div>
    <h2 class="text-center text-3xl font-extrabold text-zinc-900">Sign in to your account</h2>
    <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-zinc-700">Email address</label>
          <Input v-model="form.email" type="email" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-zinc-700">Password</label>
          <Input v-model="form.password" type="password" required />
        </div>
      </div>
      <Button type="submit" class="w-full" :disabled="isLoading">
        {{ isLoading ? 'Signing in...' : 'Sign in' }}
      </Button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../store/auth.store';
import { AuthService } from '../../services/auth.service';
import Button from '../../components/ui/button/Button.vue';
import Input from '../../components/ui/input/Input.vue'; // (Asumsi komponen input Shadcn telah di-add)

const router = useRouter();
const authStore = useAuthStore();
const isLoading = ref(false);
const form = ref({ email: '', password: '' });

const handleLogin = async () => {
  try {
    isLoading.value = true;
    const res = await AuthService.login(form.value);
    authStore.setAuth(res.data.user, res.data.token);
    router.push('/dashboard');
  } catch (error) {
    console.error('Login failed', error);
  } finally {
    isLoading.value = false;
  }
};
</script>
```

---
*(Semua struktur di atas telah disusun di repositori untuk memenuhi arsitektur Enterprise Frontend Vue 3 yang *maintainable* dan terhindar dari tumpukan kode yang tidak teratur).*