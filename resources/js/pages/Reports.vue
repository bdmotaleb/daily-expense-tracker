<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-semibold">Reports</h2>
        <p class="text-sm text-gray-600">
          Export filtered income and expense data.
        </p>
      </div>
    </div>

    <div class="rounded bg-white p-4 shadow-sm">
      <form class="grid gap-4 md:grid-cols-4" @submit.prevent="loadReport">
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600">
            Type
          </label>
          <select
            v-model="filters.type"
            class="w-full rounded border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
          >
            <option value="expense">Expense</option>
            <option value="income">Income</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600">
            From
          </label>
          <input
            v-model="filters.from"
            type="date"
            class="w-full rounded border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
          />
        </div>

        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600">
            To
          </label>
          <input
            v-model="filters.to"
            type="date"
            class="w-full rounded border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
          />
        </div>

        <div v-if="filters.type === 'expense'">
          <label class="mb-1 block text-xs font-medium text-gray-600">
            Category
          </label>
          <select
            v-model="filters.category_id"
            class="w-full rounded border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
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

        <div class="flex items-end gap-2 md:col-span-4">
          <button
            type="submit"
            class="rounded bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-700"
          >
            Apply Filters
          </button>
          <button
            type="button"
            class="rounded bg-gray-100 px-4 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200"
            @click="resetFilters"
          >
            Reset
          </button>
          <button
            type="button"
            class="rounded bg-emerald-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-emerald-700"
            @click="exportCsv"
          >
            Export CSV
          </button>
          <button
            type="button"
            class="rounded bg-rose-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-rose-700"
            @click="exportPdf"
          >
            Export PDF
          </button>
        </div>
      </form>
    </div>

    <div v-if="error" class="rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
      {{ error }}
    </div>

    <div class="overflow-x-auto rounded border border-gray-200 bg-white">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left font-medium text-gray-700">Date</th>
            <th class="px-4 py-2 text-left font-medium text-gray-700">
              Category / Source
            </th>
            <th class="px-4 py-2 text-left font-medium text-gray-700">
              Type
            </th>
            <th class="px-4 py-2 text-right font-medium text-gray-700">
              Amount
            </th>
            <th class="px-4 py-2 text-left font-medium text-gray-700">Note</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="5" class="px-4 py-4 text-center text-gray-500">
              Loading report...
            </td>
          </tr>
          <tr v-else-if="!rows.length">
            <td colspan="5" class="px-4 py-4 text-center text-gray-500">
              No records match the selected filters.
            </td>
          </tr>
          <tr
            v-else
            v-for="(row, idx) in rows"
            :key="idx"
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-2 align-top text-gray-800">
              {{ row.date }}
            </td>
            <td class="px-4 py-2 align-top text-gray-800">
              {{ row.category_name || row.source || '-' }}
            </td>
            <td class="px-4 py-2 align-top text-gray-700">
              {{ filters.type === 'income' ? 'Income' : 'Expense' }}
            </td>
            <td class="px-4 py-2 align-top text-right font-medium text-gray-900">
              {{ formatMoney(row.amount) }}
            </td>
            <td class="px-4 py-2 align-top text-gray-600">
              {{ row.note || '-' }}
            </td>
          </tr>
        </tbody>
      </table>
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

onMounted(async () => {
  await loadCategories();
  await loadReport();
});
</script>


