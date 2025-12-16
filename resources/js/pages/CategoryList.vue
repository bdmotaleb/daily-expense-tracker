<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-semibold tracking-tight text-slate-50 sm:text-2xl">
          Categories
        </h2>
        <p class="text-xs text-slate-400 sm:text-sm">
          Organize your income and expense categories.
        </p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-indigo-500/60 transition hover:bg-indigo-500 hover:shadow-md"
        @click="openCreate"
      >
        <span class="text-base leading-none">＋</span>
        Add Category
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
                Name
              </th>
              <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Scope
              </th>
              <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Color
              </th>
              <th class="px-4 py-2 text-right text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/80">
            <tr v-if="loading">
              <td colspan="4" class="px-4 py-6 text-center text-slate-400">
                Loading categories...
              </td>
            </tr>
            <tr v-else-if="!categories.length">
              <td colspan="4" class="px-4 py-6 text-center text-slate-400">
                No categories found.
              </td>
            </tr>
            <tr
              v-else
              v-for="cat in categories"
              :key="cat.id"
              class="bg-slate-900/60 transition hover:bg-slate-800/80"
            >
              <td class="px-4 py-2 align-top text-slate-100">
                {{ cat.name }}
              </td>
              <td class="px-4 py-2 align-top text-slate-300">
                <span
                  v-if="!cat.user_id"
                  class="inline-flex items-center rounded-full bg-slate-800/70 px-2 py-0.5 text-[11px] font-medium text-slate-200 ring-1 ring-slate-700"
                >
                  Global
                </span>
                <span
                  v-else
                  class="inline-flex items-center rounded-full bg-emerald-500/15 px-2 py-0.5 text-[11px] font-medium text-emerald-200 ring-1 ring-emerald-400/40"
                >
                  My category
                </span>
              </td>
              <td class="px-4 py-2 align-top text-slate-300">
                <span
                  v-if="cat.color"
                  class="inline-flex items-center gap-2 text-[11px]"
                >
                  <span
                    class="inline-block h-3 w-3 rounded-full border border-slate-700"
                    :style="{ backgroundColor: cat.color }"
                  ></span>
                  {{ cat.color }}
                </span>
                <span v-else class="text-[11px] text-slate-500">None</span>
              </td>
              <td class="px-4 py-2 align-top text-right">
                <template v-if="cat.user_id">
                  <button
                    type="button"
                    class="mr-2 text-[11px] font-medium text-indigo-300 transition hover:text-indigo-100"
                    @click="openEdit(cat)"
                  >
                    Edit
                  </button>
                  <button
                    type="button"
                    class="text-[11px] font-medium text-rose-300 transition hover:text-rose-100"
                    @click="confirmDelete(cat)"
                  >
                    Delete
                  </button>
                </template>
                <span v-else class="text-[11px] text-slate-500">
                  Read-only
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="modalOpen"
      class="fixed inset-0 z-30 flex items-center justify-center bg-black/60 backdrop-blur"
    >
      <div class="w-full max-w-md rounded-2xl bg-slate-950/95 p-6 text-slate-100 shadow-2xl ring-1 ring-slate-700">
        <h3 class="mb-4 text-lg font-semibold tracking-tight">
          {{ editing ? 'Edit Category' : 'Add Category' }}
        </h3>

        <form class="space-y-4" @submit.prevent="saveCategory">
          <div class="space-y-1.5">
            <label class="block text-xs font-medium text-slate-300" for="name">
              Name
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-50 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
            />
          </div>

          <div
            v-if="!editing"
            class="space-y-1.5"
          >
            <label class="block text-xs font-medium text-slate-300" for="type">
              Type
            </label>
            <select
              id="type"
              v-model="form.type"
              required
              class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-50 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
            >
              <option value="expense">Expense</option>
              <option value="income">Income</option>
            </select>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-medium text-slate-300" for="color">
              Color (hex)
            </label>
            <input
              id="color"
              v-model="form.color"
              type="text"
              placeholder="#22c55e"
              class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-50 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
            />
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-200 ring-1 ring-slate-600 transition hover:bg-slate-800"
              @click="closeModal"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="rounded-lg bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white shadow-sm ring-1 ring-indigo-500/60 transition hover:bg-indigo-500 hover:shadow-md"
            >
              {{ editing ? 'Save Changes' : 'Create Category' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { api } from '@/bootstrap';

const categories = ref([]);
const loading = ref(false);
const error = ref(null);

const modalOpen = ref(false);
const editing = ref(false);
const currentId = ref(null);

const form = reactive({
  name: '',
  type: 'expense',
  color: '',
});

const loadCategories = async () => {
  loading.value = true;
  error.value = null;
  try {
    const { data } = await api.get('/categories');
    categories.value = data.data || data;
  } catch (e) {
    error.value = 'Failed to load categories.';
  } finally {
    loading.value = false;
  }
};

onMounted(loadCategories);

const openCreate = () => {
  editing.value = false;
  currentId.value = null;
  form.name = '';
  form.type = 'expense';
  form.color = '';
  modalOpen.value = true;
};

const openEdit = (cat) => {
  editing.value = true;
  currentId.value = cat.id;
  form.name = cat.name;
  form.type = 'expense'; // cannot change type on edit
  form.color = cat.color || '';
  modalOpen.value = true;
};

const closeModal = () => {
  modalOpen.value = false;
};

const saveCategory = async () => {
  try {
    if (editing.value && currentId.value) {
      await api.put(`/categories/${currentId.value}`, {
        name: form.name,
        color: form.color || null,
      });
    } else {
      await api.post('/categories', {
        name: form.name,
        type: form.type,
        color: form.color || null,
      });
    }
    await loadCategories();
    closeModal();
  } catch (e) {
    error.value =
      e.response?.data?.message || 'Failed to save category. Check your input.';
  }
};

const confirmDelete = async (cat) => {
  if (!window.confirm('Delete this category? It must not be in use.')) return;
  try {
    await api.delete(`/categories/${cat.id}`);
    await loadCategories();
  } catch (e) {
    error.value =
      e.response?.data?.message ||
      'Failed to delete category. It may be in use.';
  }
};
</script>


