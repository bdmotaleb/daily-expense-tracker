<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-semibold tracking-tight text-slate-50 sm:text-2xl">
          Incomes
        </h2>
        <p class="text-xs text-slate-400 sm:text-sm">
          Track and manage all incoming money in one place.
        </p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-emerald-500/60 transition hover:bg-emerald-500 hover:shadow-md"
        @click="openCreateModal"
      >
        <span class="text-base leading-none">＋</span>
        Add Income
      </button>
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
                Source
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
              <td colspan="5" class="px-4 py-6 text-center text-slate-400">
                Loading incomes...
              </td>
            </tr>
            <tr v-else-if="!incomes.length">
              <td colspan="5" class="px-4 py-6 text-center text-slate-400">
                No incomes recorded yet.
              </td>
            </tr>
            <tr
              v-else
              v-for="income in incomes"
              :key="income.id"
              class="bg-slate-900/60 transition hover:bg-slate-800/80"
            >
              <td class="px-4 py-2 align-top text-slate-100">
                {{ income.date }}
              </td>
              <td class="px-4 py-2 align-top text-right font-semibold text-emerald-300">
                {{ formatAmount(income.amount) }}
              </td>
              <td class="px-4 py-2 align-top text-slate-100">
                {{ income.source || '-' }}
              </td>
              <td class="px-4 py-2 align-top text-slate-400">
                {{ income.note || '-' }}
              </td>
              <td class="px-4 py-2 align-top text-right">
                <button
                  type="button"
                  class="mr-2 text-[11px] font-medium text-indigo-300 transition hover:text-indigo-100"
                  @click="openEditModal(income)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="text-[11px] font-medium text-rose-300 transition hover:text-rose-100"
                  @click="confirmDelete(income)"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
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


