<template>
  <div>
    <h2 class="text-center text-3xl font-extrabold text-zinc-900">Sign in to your account</h2>
    <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-zinc-700">Email address</label>
          <input v-model="form.email" type="email" required class="mt-1 block w-full px-3 py-2 border border-zinc-300 rounded-md shadow-sm focus:outline-none focus:ring-zinc-500 focus:border-zinc-500 sm:text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-zinc-700">Password</label>
          <input v-model="form.password" type="password" required class="mt-1 block w-full px-3 py-2 border border-zinc-300 rounded-md shadow-sm focus:outline-none focus:ring-zinc-500 focus:border-zinc-500 sm:text-sm" />
        </div>
      </div>
      <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-900 hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900" :disabled="isLoading">
        {{ isLoading ? 'Signing in...' : 'Sign in' }}
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../store/auth.store';
import { AuthService } from '../../services/auth.service';

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
