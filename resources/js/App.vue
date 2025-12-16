<template>
  <div class="min-h-screen bg-gray-100 text-gray-900">
    <header class="bg-white shadow">
      <div class="mx-auto max-w-5xl px-4 py-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Daily Expense Tracker</h1>
        <nav class="flex gap-4 items-center">
          <RouterLink to="/" class="text-sm font-medium hover:underline">
            Dashboard
          </RouterLink>
          <RouterLink
            v-if="auth.user"
            to="/incomes"
            class="text-sm font-medium hover:underline"
          >
            Incomes
          </RouterLink>
          <RouterLink
            v-if="auth.user"
            to="/expenses"
            class="text-sm font-medium hover:underline"
          >
            Expenses
          </RouterLink>
          <RouterLink
            v-if="auth.user"
            to="/categories"
            class="text-sm font-medium hover:underline"
          >
            Categories
          </RouterLink>
          <RouterLink
            v-if="auth.user"
            to="/budgets"
            class="text-sm font-medium hover:underline"
          >
            Budgets
          </RouterLink>
          <RouterLink
            v-if="auth.user"
            to="/reports"
            class="text-sm font-medium hover:underline"
          >
            Reports
          </RouterLink>
          <RouterLink
            v-if="!auth.user"
            to="/login"
            class="text-sm font-medium hover:underline"
          >
            Login
          </RouterLink>
          <RouterLink
            v-if="!auth.user"
            to="/register"
            class="text-sm font-medium hover:underline"
          >
            Register
          </RouterLink>
          <button
            v-if="auth.user"
            type="button"
            class="text-sm font-medium text-red-600 hover:underline"
            @click="logout"
          >
            Logout
          </button>
        </nav>
      </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
      <RouterView />
    </main>
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

<style scoped>
.min-h-screen {
  min-height: 100vh;
}
</style>

