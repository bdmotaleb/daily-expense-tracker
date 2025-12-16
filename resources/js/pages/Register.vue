<template>
  <div class="mx-auto flex max-w-md flex-col gap-6 rounded-2xl bg-slate-900/90 p-6 text-slate-100 shadow-xl ring-1 ring-slate-800">
    <div>
      <h2 class="mb-1 text-xl font-semibold tracking-tight sm:text-2xl">
        Create your account
      </h2>
      <p class="text-sm text-slate-400">
        A few details to get started with tracking your daily expenses.
      </p>
    </div>

    <p v-if="auth.error" class="rounded-lg border border-red-400/60 bg-red-500/10 px-3 py-2 text-sm text-red-100">
      {{ auth.error }}
    </p>

    <form class="space-y-4" @submit.prevent="submit">
      <div class="space-y-1.5">
        <label class="block text-xs font-medium text-slate-300" for="name">
          Name
        </label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-50 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
        />
      </div>

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

      <div class="space-y-1.5">
        <label
          class="block text-xs font-medium text-slate-300"
          for="password_confirmation"
        >
          Confirm Password
        </label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          required
          class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-50 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
        />
      </div>

      <button
        type="submit"
        :disabled="auth.loading"
        class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-indigo-500/60 transition hover:bg-indigo-500 hover:shadow-md disabled:opacity-60"
      >
        <span v-if="!auth.loading">Register</span>
        <span v-else>Registering...</span>
      </button>
    </form>

    <p class="text-center text-xs text-slate-400">
      Already have an account?
      <RouterLink to="/login" class="font-medium text-indigo-300 hover:text-indigo-100">
        Login
      </RouterLink>
    </p>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = async () => {
  const ok = await auth.register(form);
  if (ok) {
    router.push('/');
  }
};
</script>

