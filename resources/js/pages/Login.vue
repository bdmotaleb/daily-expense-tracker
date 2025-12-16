<template>
  <div class="mx-auto flex max-w-md flex-col gap-6 rounded-2xl bg-slate-900/90 p-6 text-slate-100 shadow-xl ring-1 ring-slate-800">
    <div>
      <h2 class="mb-1 text-xl font-semibold tracking-tight sm:text-2xl">
        Welcome back
      </h2>
      <p class="text-sm text-slate-400">
        Sign in to review your dashboard and keep your expenses on track.
      </p>
    </div>

    <p v-if="auth.error" class="rounded-lg border border-red-400/60 bg-red-500/10 px-3 py-2 text-sm text-red-100">
      {{ auth.error }}
    </p>

    <form class="space-y-4" @submit.prevent="submit">
      <div class="space-y-1.5">
        <label class="block text-xs font-medium text-slate-300" for="email">
          Email
        </label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-50 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
        />
      </div>

      <div class="space-y-1.5">
        <label class="block text-xs font-medium text-slate-300" for="password">
          Password
        </label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-50 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
        />
      </div>

      <div class="flex items-center justify-between text-xs text-slate-400">
        <label class="inline-flex items-center gap-2">
          <input
            id="remember"
            v-model="form.remember"
            type="checkbox"
            class="h-3.5 w-3.5 rounded border-slate-600 bg-slate-900 text-indigo-500 focus:ring-indigo-500"
          />
          <span>Remember me</span>
        </label>
      </div>

      <button
        type="submit"
        :disabled="auth.loading"
        class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-indigo-500/60 transition hover:bg-indigo-500 hover:shadow-md disabled:opacity-60"
      >
        <span v-if="!auth.loading">Login</span>
        <span v-else>Logging in...</span>
      </button>
    </form>

    <p class="text-center text-xs text-slate-400">
      Don’t have an account?
      <RouterLink to="/register" class="font-medium text-indigo-300 hover:text-indigo-100">
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

