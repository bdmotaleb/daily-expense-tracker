<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-semibold tracking-tight text-slate-50 sm:text-2xl">
          Budgets
        </h2>
        <p class="text-xs text-slate-400 sm:text-sm">
          Set and track monthly limits for each category.
        </p>
      </div>
      <div class="flex items-center gap-3 rounded-2xl bg-slate-900/80 px-3 py-2 text-xs text-slate-200 ring-1 ring-slate-700">
        <span class="hidden text-[11px] font-medium text-slate-400 sm:inline">
          Month
        </span>
        <input
          id="month"
          v-model="month"
          type="month"
          class="w-32 rounded-lg border border-slate-700 bg-slate-950/60 px-2 py-1.5 text-xs text-slate-100 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
          @change="loadBudgets"
        />
      </div>
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
                Category
              </th>
              <th class="px-4 py-2 text-right text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Budget
              </th>
              <th class="px-4 py-2 text-right text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Spent
              </th>
              <th class="px-4 py-2 text-right text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Remaining
              </th>
              <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Progress
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/80">
            <tr v-if="loading">
              <td colspan="5" class="px-4 py-6 text-center text-slate-400">
                Loading budget summary...
              </td>
            </tr>
            <tr v-else-if="!rows.length">
              <td colspan="5" class="px-4 py-6 text-center text-slate-400">
                No categories available.
              </td>
            </tr>
            <tr
              v-else
              v-for="row in rows"
              :key="row.category_id"
              class="bg-slate-900/60 transition hover:bg-slate-800/80"
            >
              <td class="px-4 py-2 align-top text-slate-100">
                <div class="flex items-center gap-2">
                  <span
                    v-if="row.category_color"
                    class="inline-block h-3 w-3 rounded-full border border-slate-700"
                    :style="{ backgroundColor: row.category_color }"
                  ></span>
                  <span>{{ row.category_name }}</span>
                </div>
              </td>
              <td class="px-4 py-2 align-top text-right">
                <input
                  v-model.number="row.editBudget"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-24 rounded-lg border border-slate-700 bg-slate-950/60 px-2 py-1 text-right text-xs text-slate-100 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
                />
              </td>
              <td class="px-4 py-2 align-top text-right text-slate-100">
                {{ formatMoney(row.spent) }}
              </td>
              <td
                class="px-4 py-2 align-top text-right"
                :class="row.over_budget ? 'text-rose-300 font-semibold' : 'text-slate-100'"
              >
                {{ formatMoney(row.remaining) }}
              </td>
              <td class="px-4 py-2 align-top">
                <div class="mb-1 flex items-center justify-between text-[11px]">
                  <span
                    :class="row.over_budget ? 'text-rose-300 font-semibold' : 'text-slate-300'"
                  >
                    {{ row.progress }}%
                  </span>
                  <span
                    v-if="row.over_budget"
                    class="rounded-full bg-rose-500/10 px-2 py-0.5 text-[10px] font-medium text-rose-200 ring-1 ring-rose-400/60"
                  >
                    Over budget
                  </span>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-800">
                  <div
                    class="h-2 rounded-full"
                    :class="row.over_budget ? 'bg-rose-500' : 'bg-indigo-500'"
                    :style="{ width: row.progress + '%' }"
                  ></div>
                </div>
                <button
                  type="button"
                  class="mt-2 text-[11px] font-medium text-indigo-300 transition hover:text-indigo-100"
                  @click="saveRow(row)"
                >
                  Save
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '@/bootstrap';

const rows = ref([]);
const month = ref(new Date().toISOString().slice(0, 7)); // YYYY-MM
const loading = ref(false);
const error = ref(null);

const loadBudgets = async () => {
  loading.value = true;
  error.value = null;
  try {
    const { data } = await api.get('/budgets', {
      params: { month: month.value },
    });
    rows.value = (data.data || []).map((row) => ({
      ...row,
      editBudget: row.budget_amount,
    }));
  } catch (e) {
    error.value = 'Failed to load budget summary.';
  } finally {
    loading.value = false;
  }
};

onMounted(loadBudgets);

const saveRow = async (row) => {
  const payload = {
    category_id: row.category_id,
    month: month.value,
    amount: row.editBudget ?? 0,
  };

  try {
    if (row.budget_id) {
      // Update existing
      await api.put(`/budgets/${row.budget_id}`, {
        amount: payload.amount,
      });
    } else {
      // Create new
      const { data } = await api.post('/budgets', payload);
      row.budget_id = data.id;
    }
    await loadBudgets();
  } catch (e) {
    error.value =
      e.response?.data?.message ||
      'Failed to save budget. Please check your input.';
  }
};

const formatMoney = (value) => {
  return Number(value).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};
</script>


