<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-semibold">Budgets</h2>
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-700" for="month">Month</label>
        <input
          id="month"
          v-model="month"
          type="month"
          class="rounded border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
          @change="loadBudgets"
        />
      </div>
    </div>

    <div v-if="error" class="rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
      {{ error }}
    </div>

    <div class="overflow-x-auto rounded border border-gray-200 bg-white">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left font-medium text-gray-700">
              Category
            </th>
            <th class="px-4 py-2 text-right font-medium text-gray-700">
              Budget
            </th>
            <th class="px-4 py-2 text-right font-medium text-gray-700">
              Spent
            </th>
            <th class="px-4 py-2 text-right font-medium text-gray-700">
              Remaining
            </th>
            <th class="px-4 py-2 font-medium text-gray-700">
              Progress
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="5" class="px-4 py-4 text-center text-gray-500">
              Loading budget summary...
            </td>
          </tr>
          <tr v-else-if="!rows.length">
            <td colspan="5" class="px-4 py-4 text-center text-gray-500">
              No categories available.
            </td>
          </tr>
          <tr
            v-else
            v-for="row in rows"
            :key="row.category_id"
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-2 align-top text-gray-900">
              <div class="flex items-center gap-2">
                <span
                  v-if="row.category_color"
                  class="inline-block h-3 w-3 rounded-full border"
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
                class="w-24 rounded border border-gray-300 px-2 py-1 text-right text-sm focus:outline-none focus:ring focus:ring-indigo-200"
              />
            </td>
            <td class="px-4 py-2 align-top text-right text-gray-900">
              {{ formatMoney(row.spent) }}
            </td>
            <td
              class="px-4 py-2 align-top text-right"
              :class="row.over_budget ? 'text-red-600 font-semibold' : 'text-gray-900'"
            >
              {{ formatMoney(row.remaining) }}
            </td>
            <td class="px-4 py-2 align-top">
              <div class="mb-1 flex items-center justify-between text-xs">
                <span
                  :class="row.over_budget ? 'text-red-700 font-semibold' : 'text-gray-700'"
                >
                  {{ row.progress }}%
                </span>
                <span
                  v-if="row.over_budget"
                  class="rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700"
                >
                  Over budget!
                </span>
              </div>
              <div class="h-2 w-full rounded-full bg-gray-100">
                <div
                  class="h-2 rounded-full"
                  :class="row.over_budget ? 'bg-red-500' : 'bg-indigo-500'"
                  :style="{ width: row.progress + '%' }"
                ></div>
              </div>
              <button
                type="button"
                class="mt-2 text-xs font-medium text-indigo-600 hover:underline"
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


