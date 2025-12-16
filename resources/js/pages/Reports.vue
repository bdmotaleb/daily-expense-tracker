<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-semibold tracking-tight text-slate-50 sm:text-2xl">
          Reports
        </h2>
        <p class="text-xs text-slate-400 sm:text-sm">
          Filter, review, and export your income and expense history.
        </p>
      </div>
    </div>

    <!-- Filters -->
    <div
      class="rounded-2xl bg-slate-900/80 p-4 text-xs text-slate-200 shadow-sm ring-1 ring-slate-800 backdrop-blur"
    >
      <form class="space-y-4" @submit.prevent="loadReport">
        <div class="grid gap-3 md:grid-cols-4">
          <div class="space-y-1.5">
            <label class="mb-1 block text-[11px] font-medium text-slate-400">
              Type
            </label>
            <select
              v-model="filters.type"
              class="w-full rounded-lg border border-slate-700/80 bg-slate-950/70 px-2.5 py-2 text-xs text-slate-100 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
            >
              <option value="expense">Expense</option>
              <option value="income">Income</option>
            </select>
          </div>

          <div class="space-y-1.5">
            <label class="mb-1 block text-[11px] font-medium text-slate-400">
              From
            </label>
            <input
              v-model="filters.from"
              type="date"
              class="w-full rounded-lg border border-slate-700/80 bg-slate-950/70 px-2.5 py-2 text-xs text-slate-100 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
            />
          </div>

          <div class="space-y-1.5">
            <label class="mb-1 block text-[11px] font-medium text-slate-400">
              To
            </label>
            <input
              v-model="filters.to"
              type="date"
              class="w-full rounded-lg border border-slate-700/80 bg-slate-950/70 px-2.5 py-2 text-xs text-slate-100 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
            />
          </div>

          <div
            v-if="filters.type === 'expense'"
            class="space-y-1.5"
          >
            <label class="mb-1 block text-[11px] font-medium text-slate-400">
              Category
            </label>
            <select
              v-model="filters.category_id"
              class="w-full rounded-lg border border-slate-700/80 bg-slate-950/70 px-2.5 py-2 text-xs text-slate-100 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
            >
              <option value="">All</option>
              <option
                v-for="c in categories"
                :key="c.id"
                :value="c.id"
              >
                {{ c.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 pt-1">
          <button
            type="submit"
            class="inline-flex items-center rounded-lg bg-slate-100 px-4 py-1.5 text-xs font-semibold text-slate-900 shadow-sm transition hover:bg-white"
          >
            Apply Filters
          </button>
          <button
            type="button"
            class="inline-flex items-center rounded-lg bg-slate-800/80 px-3 py-1.5 text-[11px] font-medium text-slate-200 ring-1 ring-slate-700/80 transition hover:bg-slate-700"
            @click="resetFilters"
          >
            Reset
          </button>

          <div class="ml-auto flex flex-wrap items-center gap-2">
            <button
              type="button"
              class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm ring-1 ring-emerald-500/70 transition hover:bg-emerald-500 hover:shadow-md"
              @click="exportCsv"
            >
              Export CSV
            </button>
            <button
              type="button"
              class="inline-flex items-center rounded-lg bg-rose-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm ring-1 ring-rose-500/70 transition hover:bg-rose-500 hover:shadow-md"
              @click="exportPdf"
            >
              Export PDF
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Error -->
    <div
      v-if="error"
      class="rounded-xl border border-red-400/60 bg-red-500/10 px-4 py-3 text-sm text-red-100"
    >
      {{ error }}
    </div>

    <!-- Table -->
    <div class="overflow-hidden rounded-2xl bg-slate-900/80 shadow-lg ring-1 ring-slate-800">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-800 text-xs sm:text-sm">
          <thead class="bg-slate-900/80">
            <tr>
              <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Date
              </th>
              <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Category / Source
              </th>
              <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Type
              </th>
              <th class="px-4 py-2 text-right text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Amount
              </th>
              <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Note
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/80">
            <tr v-if="loading">
              <td colspan="5" class="px-4 py-6 text-center text-slate-400">
                Loading report...
              </td>
            </tr>
            <tr v-else-if="!rows.length">
              <td colspan="5" class="px-4 py-6 text-center text-slate-400">
                No records match the selected filters.
              </td>
            </tr>
            <tr
              v-else
              v-for="(row, idx) in rows"
              :key="idx"
              class="bg-slate-900/60 transition hover:bg-slate-800/80"
            >
              <td class="px-4 py-2 align-top text-slate-100">
                {{ formatDate(row.date) }}
              </td>
              <td class="px-4 py-2 align-top text-slate-100">
                {{ row.category_name || row.source || '-' }}
              </td>
              <td class="px-4 py-2 align-top text-slate-300">
                {{ filters.type === 'income' ? 'Income' : 'Expense' }}
              </td>
              <td class="px-4 py-2 align-top text-right font-semibold" :class="filters.type === 'income' ? 'text-emerald-300' : 'text-rose-300'">
                {{ formatMoney(row.amount) }}
              </td>
              <td class="px-4 py-2 align-top text-slate-400">
                {{ row.note || '-' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { api } from '@/bootstrap';

const categories = ref([]);
const rows = ref([]);
const loading = ref(false);
const error = ref(null);

const today = new Date().toISOString().slice(0, 10);

const filters = reactive({
  type: 'expense',
  from: today,
  to: today,
  category_id: '',
});

const loadCategories = async () => {
  try {
    const { data } = await api.get('/categories');
    categories.value = data.data || data;
  } catch {
    // ignore
  }
};

const buildQueryString = () => {
  const params = new URLSearchParams();
  if (filters.type) params.set('type', filters.type);
  if (filters.from) params.set('from', filters.from);
  if (filters.to) params.set('to', filters.to);
  if (filters.type === 'expense' && filters.category_id) {
    params.set('category_id', filters.category_id);
  }
  return params.toString();
};

const loadReport = async () => {
  loading.value = true;
  error.value = null;
  try {
    const { data } = await api.get('/reports', {
      params: {
        type: filters.type,
        from: filters.from,
        to: filters.to,
        category_id:
          filters.type === 'expense' && filters.category_id
            ? filters.category_id
            : undefined,
      },
    });
    rows.value = data.data || [];
  } catch (e) {
    error.value = 'Failed to load report data.';
  } finally {
    loading.value = false;
  }
};

const resetFilters = () => {
  filters.type = 'expense';
  filters.from = today;
  filters.to = today;
  filters.category_id = '';
  loadReport();
};

const exportCsv = () => {
  const qs = buildQueryString();
  window.location.href = `/api/reports/export/csv?${qs}`;
};

const exportPdf = () => {
  const qs = buildQueryString();
  window.location.href = `/api/reports/export/pdf?${qs}`;
};

const formatMoney = (value) => {
  return Number(value || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const formatDate = (value) => {
  if (!value) return '';
  // Handle ISO strings like "2025-12-01T00:00:00.000000Z"
  const str = String(value);
  if (str.length >= 10) return str.slice(0, 10);
  return str;
};

onMounted(async () => {
  await loadCategories();
  await loadReport();
});
</script>


