<template>
  <div class="max-w-md mx-auto bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4">Login</h2>

    <p v-if="auth.error" class="mb-3 text-sm text-red-600">
      {{ auth.error }}
    </p>

    <form @submit.prevent="submit">
      <div class="mb-4">
        <label class="block text-sm font-medium mb-1" for="email">Email</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
        />
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium mb-1" for="password">
          Password
        </label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
        />
      </div>

      <div class="mb-4 flex items-center">
        <input
          id="remember"
          v-model="form.remember"
          type="checkbox"
          class="mr-2"
        />
        <label for="remember" class="text-sm">Remember me</label>
      </div>

      <button
        type="submit"
        :disabled="auth.loading"
        class="inline-flex items-center justify-center rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
      >
        <span v-if="!auth.loading">Login</span>
        <span v-else>Logging in...</span>
      </button>
    </form>

    <p class="mt-4 text-sm">
      Don’t have an account?
      <RouterLink to="/register" class="text-indigo-600 hover:underline">
        Register
      </RouterLink>
    </p>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const form = reactive({
  email: '',
  password: '',
  remember: false,
});

const submit = async () => {
  const ok = await auth.login(form);
  if (ok) {
    const redirect = route.query.redirect || '/';
    router.push(redirect);
  }
};
</script>

