<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-semibold">Expenses</h2>
      <button
        type="button"
        class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
        @click="openCreateModal"
      >
        Add Expense
      </button>
    </div>

    <div class="flex flex-wrap gap-3 rounded bg-white p-3 shadow">
      <div>
        <label class="mb-1 block text-xs font-medium text-gray-600">
          From
        </label>
        <input
          v-model="filters.from_date"
          type="date"
          class="rounded border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
        />
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-gray-600">
          To
        </label>
        <input
          v-model="filters.to_date"
          type="date"
          class="rounded border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
        />
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-gray-600">
          Category
        </label>
        <select
          v-model="filters.category_id"
          class="rounded border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
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
          class="rounded bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200"
          @click="applyFilters"
        >
          Apply
        </button>
        <button
          type="button"
          class="rounded bg-white px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-50"
          @click="resetFilters"
        >
          Reset
        </button>
      </div>
    </div>

    <div v-if="error" class="rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
      {{ error }}
    </div>

    <div class="overflow-x-auto rounded border border-gray-200 bg-white">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left font-medium text-gray-700">Date</th>
            <th class="px-4 py-2 text-right font-medium text-gray-700">Amount</th>
            <th class="px-4 py-2 text-left font-medium text-gray-700">Category</th>
            <th class="px-4 py-2 text-left font-medium text-gray-700">Payment</th>
            <th class="px-4 py-2 text-left font-medium text-gray-700">Note</th>
            <th class="px-4 py-2 text-right font-medium text-gray-700">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="px-4 py-4 text-center text-gray-500">
              Loading expenses...
            </td>
          </tr>
          <tr v-else-if="!expenses.length">
            <td colspan="6" class="px-4 py-4 text-center text-gray-500">
              No expenses recorded yet.
            </td>
          </tr>
          <tr
            v-else
            v-for="expense in expenses"
            :key="expense.id"
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-2 align-top text-gray-800">
              {{ expense.date }}
            </td>
            <td class="px-4 py-2 align-top text-right font-medium text-gray-900">
              {{ formatAmount(expense.amount) }}
            </td>
            <td class="px-4 py-2 align-top text-gray-800">
              {{ expense.category?.name || '-' }}
            </td>
            <td class="px-4 py-2 align-top text-gray-800">
              {{ expense.payment_method || '-' }}
            </td>
            <td class="px-4 py-2 align-top text-gray-600">
              {{ expense.note || '-' }}
            </td>
            <td class="px-4 py-2 align-top text-right">
              <button
                type="button"
                class="mr-2 text-xs font-medium text-indigo-600 hover:underline"
                @click="openEditModal(expense)"
              >
                Edit
              </button>
              <button
                type="button"
                class="text-xs font-medium text-red-600 hover:underline"
                @click="confirmDelete(expense)"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
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
    const { data } = await api.get('/categories');
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


