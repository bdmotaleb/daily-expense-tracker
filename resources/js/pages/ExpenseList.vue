<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-semibold tracking-tight text-slate-50 sm:text-2xl">
          Expenses
        </h2>
        <p class="text-xs text-slate-400 sm:text-sm">
          Review and manage your outgoing transactions.
        </p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-indigo-500/60 transition hover:bg-indigo-500 hover:shadow-md"
        @click="openCreateModal"
      >
        <span class="text-base leading-none">＋</span>
        Add Expense
      </button>
    </div>

    <!-- Filters -->
    <div
      class="rounded-2xl bg-slate-900/70 p-4 text-xs text-slate-200 shadow-sm ring-1 ring-slate-800/80 backdrop-blur"
    >
      <div class="mb-3 flex items-center justify-between gap-3">
        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
          Filters
        </p>
        <p class="text-[11px] text-slate-500">
          Narrow down by date range and category.
        </p>
      </div>
      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="space-y-1.5">
          <label class="block text-[11px] font-medium text-slate-400">
            From
          </label>
          <input
            v-model="filters.from_date"
            type="date"
            class="w-full rounded-lg border border-slate-700/80 bg-slate-900/60 px-2.5 py-2 text-xs text-slate-100 placeholder-slate-500 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
          />
        </div>
        <div class="space-y-1.5">
          <label class="block text-[11px] font-medium text-slate-400">
            To
          </label>
          <input
            v-model="filters.to_date"
            type="date"
            class="w-full rounded-lg border border-slate-700/80 bg-slate-900/60 px-2.5 py-2 text-xs text-slate-100 placeholder-slate-500 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
          />
        </div>
        <div class="space-y-1.5">
          <label class="block text-[11px] font-medium text-slate-400">
            Category
          </label>
          <select
            v-model="filters.category_id"
            class="w-full rounded-lg border border-slate-700/80 bg-slate-900/60 px-2.5 py-2 text-xs text-slate-100 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
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
        <div class="flex items-end gap-2">
          <button
            type="button"
            class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-900 shadow-sm transition hover:bg-white"
            @click="applyFilters"
          >
            Apply
          </button>
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg bg-slate-800/80 px-3 py-2 text-[11px] font-medium text-slate-200 ring-1 ring-slate-700/80 transition hover:bg-slate-700"
            @click="resetFilters"
          >
            Reset
          </button>
        </div>
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
                Date
              </th>
              <th class="px-4 py-2 text-right text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Amount
              </th>
              <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Category
              </th>
              <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Payment
              </th>
              <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Note
              </th>
              <th class="px-4 py-2 text-right text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/80">
            <tr v-if="loading">
              <td colspan="6" class="px-4 py-6 text-center text-slate-400">
                Loading expenses...
              </td>
            </tr>
            <tr v-else-if="!expenses.length">
              <td colspan="6" class="px-4 py-6 text-center text-slate-400">
                No expenses recorded yet.
              </td>
            </tr>
            <tr
              v-else
              v-for="expense in expenses"
              :key="expense.id"
              class="bg-slate-900/60 transition hover:bg-slate-800/80"
            >
              <td class="px-4 py-2 align-top text-slate-100">
                {{ expense.date }}
              </td>
              <td class="px-4 py-2 align-top text-right font-semibold text-rose-300">
                {{ formatAmount(expense.amount) }}
              </td>
              <td class="px-4 py-2 align-top text-slate-100">
                {{ expense.category?.name || '-' }}
              </td>
              <td class="px-4 py-2 align-top text-slate-300">
                {{ expense.payment_method || '-' }}
              </td>
              <td class="px-4 py-2 align-top text-slate-400">
                {{ expense.note || '-' }}
              </td>
              <td class="px-4 py-2 align-top text-right">
                <button
                  type="button"
                  class="mr-2 text-[11px] font-medium text-indigo-300 transition hover:text-indigo-100"
                  @click="openEditModal(expense)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="text-[11px] font-medium text-rose-300 transition hover:text-rose-100"
                  @click="confirmDelete(expense)"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ExpenseFormModal
      :open="modalOpen"
      :expense="selectedExpense"
      :categories="categories"
      @save="handleSave"
      @close="closeModal"
    />
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { api } from '@/bootstrap';
import ExpenseFormModal from './ExpenseFormModal.vue';

const expenses = ref([]);
const categories = ref([]);
const loading = ref(false);
const error = ref(null);

const filters = reactive({
  from_date: '',
  to_date: '',
  category_id: '',
});

const modalOpen = ref(false);
const selectedExpense = ref(null);

const loadCategories = async () => {
  try {
    const { data } = await api.get('/categories', {
      params: { type: 'expense' },
    });
    categories.value = data.data || data;
  } catch (e) {
    // silent; dropdown will just be empty
  }
};

const loadExpenses = async () => {
  loading.value = true;
  error.value = null;
  try {
    const { data } = await api.get('/expenses', {
      params: {
        from_date: filters.from_date || undefined,
        to_date: filters.to_date || undefined,
        category_id: filters.category_id || undefined,
      },
    });
    expenses.value = data.data || data;
  } catch (e) {
    error.value = 'Failed to load expenses.';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await Promise.all([loadCategories(), loadExpenses()]);
});

const applyFilters = () => {
  loadExpenses();
};

const resetFilters = () => {
  filters.from_date = '';
  filters.to_date = '';
  filters.category_id = '';
  loadExpenses();
};

const openCreateModal = () => {
  selectedExpense.value = null;
  modalOpen.value = true;
};

const openEditModal = (expense) => {
  selectedExpense.value = { ...expense };
  modalOpen.value = true;
};

const closeModal = () => {
  modalOpen.value = false;
};

const handleSave = async (payload) => {
  try {
    if (payload.id) {
      await api.put(`/expenses/${payload.id}`, payload);
    } else {
      await api.post('/expenses', payload);
    }
    await loadExpenses();
    closeModal();
  } catch (e) {
    error.value =
      e.response?.data?.message ||
      'Failed to save expense. Please check your inputs.';
  }
};

const confirmDelete = async (expense) => {
  if (!window.confirm('Delete this expense record?')) return;
  try {
    await api.delete(`/expenses/${expense.id}`);
    await loadExpenses();
  } catch (e) {
    error.value =
      e.response?.data?.message ||
      'Failed to delete expense. Please try again.';
  }
};

const formatAmount = (value) => {
  return Number(value).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};
</script>


