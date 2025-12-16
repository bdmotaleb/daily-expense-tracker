<template>
  <div class="min-h-screen bg-slate-950/95 text-slate-900">
    <div class="mx-auto flex min-h-screen max-w-6xl flex-col px-3 pb-8 pt-4 sm:px-4 lg:px-6">
      <!-- App header / nav -->
      <header
        class="mb-6 flex items-center justify-between rounded-2xl border border-slate-800/80 bg-slate-900/80 px-4 py-3 shadow-[0_18px_45px_rgba(15,23,42,0.75)] backdrop-blur"
      >
        <div class="flex items-center gap-3">
          <div
            class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/90 text-sm font-semibold text-white shadow-md shadow-indigo-500/40"
          >
            DT
          </div>
          <div>
            <h1 class="text-sm font-semibold tracking-tight text-slate-50 sm:text-base">
              Daily Expense Tracker
            </h1>
            <p class="text-[11px] text-slate-400 sm:text-xs">
              Stay on top of income, expenses, and budgets in one clean view.
            </p>
          </div>
        </div>
        <nav class="flex items-center gap-2 text-xs sm:gap-3 sm:text-sm">
          <RouterLink
            to="/"
            class="rounded-full px-3 py-1.5 font-medium text-slate-200/80 transition hover:bg-slate-800 hover:text-slate-50"
            active-class="bg-slate-100 text-slate-900 hover:bg-slate-100"
          >
            Dashboard
          </RouterLink>
          <RouterLink
            v-if="auth.user"
            to="/incomes"
            class="rounded-full px-3 py-1.5 font-medium text-slate-200/80 transition hover:bg-slate-800 hover:text-slate-50"
            active-class="bg-slate-100 text-slate-900 hover:bg-slate-100"
          >
            Incomes
          </RouterLink>
          <RouterLink
            v-if="auth.user"
            to="/expenses"
            class="rounded-full px-3 py-1.5 font-medium text-slate-200/80 transition hover:bg-slate-800 hover:text-slate-50"
            active-class="bg-slate-100 text-slate-900 hover:bg-slate-100"
          >
            Expenses
          </RouterLink>
          <RouterLink
            v-if="auth.user"
            to="/categories"
            class="rounded-full px-3 py-1.5 font-medium text-slate-200/80 transition hover:bg-slate-800 hover:text-slate-50"
            active-class="bg-slate-100 text-slate-900 hover:bg-slate-100"
          >
            Categories
          </RouterLink>
          <RouterLink
            v-if="auth.user"
            to="/budgets"
            class="rounded-full px-3 py-1.5 font-medium text-slate-200/80 transition hover:bg-slate-800 hover:text-slate-50"
            active-class="bg-slate-100 text-slate-900 hover:bg-slate-100"
          >
            Budgets
          </RouterLink>
          <RouterLink
            v-if="auth.user"
            to="/reports"
            class="rounded-full px-3 py-1.5 font-medium text-slate-200/80 transition hover:bg-slate-800 hover:text-slate-50"
            active-class="bg-slate-100 text-slate-900 hover:bg-slate-100"
          >
            Reports
          </RouterLink>
          <RouterLink
            v-if="!auth.user"
            to="/login"
            class="rounded-full px-3 py-1.5 font-medium text-slate-200/80 transition hover:bg-slate-800 hover:text-slate-50"
            active-class="bg-slate-100 text-slate-900 hover:bg-slate-100"
          >
            Login
          </RouterLink>
          <RouterLink
            v-if="!auth.user"
            to="/register"
            class="rounded-full px-3 py-1.5 font-medium text-slate-200/80 transition hover:bg-slate-800 hover:text-slate-50"
            active-class="bg-slate-100 text-slate-900 hover:bg-slate-100"
          >
            Register
          </RouterLink>
          <button
            v-if="auth.user"
            type="button"
            class="ml-1 inline-flex items-center rounded-full bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-300 ring-1 ring-red-500/40 transition hover:bg-red-500/20 hover:text-red-100 hover:ring-red-400/80"
            @click="logout"
          >
            Logout
          </button>
        </nav>
      </header>

      <!-- Page content -->
      <main class="flex-1">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();

const logout = async () => {
  await auth.logout();
  router.push({ name: 'login' });
};
</script>

<style scoped></style>

