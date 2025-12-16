<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-semibold">Incomes</h2>
      <button
        type="button"
        class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
        @click="openCreateModal"
      >
        Add Income
      </button>
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
            <th class="px-4 py-2 text-left font-medium text-gray-700">Source</th>
            <th class="px-4 py-2 text-left font-medium text-gray-700">Note</th>
            <th class="px-4 py-2 text-right font-medium text-gray-700">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="5" class="px-4 py-4 text-center text-gray-500">
              Loading incomes...
            </td>
          </tr>
          <tr v-else-if="!incomes.length">
            <td colspan="5" class="px-4 py-4 text-center text-gray-500">
              No incomes recorded yet.
            </td>
          </tr>
          <tr
            v-else
            v-for="income in incomes"
            :key="income.id"
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-2 align-top text-gray-800">
              {{ income.date }}
            </td>
            <td class="px-4 py-2 align-top text-right font-medium text-gray-900">
              {{ formatAmount(income.amount) }}
            </td>
            <td class="px-4 py-2 align-top text-gray-800">
              {{ income.source || '-' }}
            </td>
            <td class="px-4 py-2 align-top text-gray-600">
              {{ income.note || '-' }}
            </td>
            <td class="px-4 py-2 align-top text-right">
              <button
                type="button"
                class="mr-2 text-xs font-medium text-indigo-600 hover:underline"
                @click="openEditModal(income)"
              >
                Edit
              </button>
              <button
                type="button"
                class="text-xs font-medium text-red-600 hover:underline"
                @click="confirmDelete(income)"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <IncomeFormModal
      :open="modalOpen"
      :income="selectedIncome"
      @save="handleSave"
      @close="closeModal"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '@/bootstrap';
import IncomeFormModal from './IncomeFormModal.vue';

const incomes = ref([]);
const loading = ref(false);
const error = ref(null);

const modalOpen = ref(false);
const selectedIncome = ref(null);

const loadIncomes = async () => {
  loading.value = true;
  error.value = null;
  try {
    const { data } = await api.get('/incomes');
    // Resource collection wraps data under "data"
    incomes.value = data.data || data;
  } catch (e) {
    error.value = 'Failed to load incomes.';
  } finally {
    loading.value = false;
  }
};

onMounted(loadIncomes);

const openCreateModal = () => {
  selectedIncome.value = null;
  modalOpen.value = true;
};

const openEditModal = (income) => {
  selectedIncome.value = { ...income };
  modalOpen.value = true;
};

const closeModal = () => {
  modalOpen.value = false;
};

const handleSave = async (payload) => {
  try {
    if (payload.id) {
      await api.put(`/incomes/${payload.id}`, payload);
    } else {
      await api.post('/incomes', payload);
    }
    await loadIncomes();
    closeModal();
  } catch (e) {
    error.value =
      e.response?.data?.message || 'Failed to save income. Please try again.';
  }
};

const confirmDelete = async (income) => {
  if (!window.confirm('Delete this income record?')) return;
  try {
    await api.delete(`/incomes/${income.id}`);
    await loadIncomes();
  } catch (e) {
    error.value =
      e.response?.data?.message || 'Failed to delete income. Please try again.';
  }
};

const formatAmount = (value) => {
  return Number(value).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};
</script>


