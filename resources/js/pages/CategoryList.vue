<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-semibold">Categories</h2>
      <button
        type="button"
        class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
        @click="openCreate"
      >
        Add Category
      </button>
    </div>

    <div v-if="error" class="rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
      {{ error }}
    </div>

    <div class="overflow-x-auto rounded border border-gray-200 bg-white">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left font-medium text-gray-700">Name</th>
            <th class="px-4 py-2 text-left font-medium text-gray-700">Scope</th>
            <th class="px-4 py-2 text-left font-medium text-gray-700">Color</th>
            <th class="px-4 py-2 text-right font-medium text-gray-700">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="4" class="px-4 py-4 text-center text-gray-500">
              Loading categories...
            </td>
          </tr>
          <tr v-else-if="!categories.length">
            <td colspan="4" class="px-4 py-4 text-center text-gray-500">
              No categories found.
            </td>
          </tr>
          <tr
            v-else
            v-for="cat in categories"
            :key="cat.id"
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-2 align-top text-gray-900">
              {{ cat.name }}
            </td>
            <td class="px-4 py-2 align-top text-gray-700">
              <span
                v-if="!cat.user_id"
                class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700"
              >
                Global
              </span>
              <span
                v-else
                class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700"
              >
                My category
              </span>
            </td>
            <td class="px-4 py-2 align-top text-gray-700">
              <span
                v-if="cat.color"
                class="inline-flex items-center gap-2 text-xs"
              >
                <span
                  class="inline-block h-3 w-3 rounded-full border"
                  :style="{ backgroundColor: cat.color }"
                ></span>
                {{ cat.color }}
              </span>
              <span v-else class="text-xs text-gray-400">None</span>
            </td>
            <td class="px-4 py-2 align-top text-right">
              <template v-if="cat.user_id">
                <button
                  type="button"
                  class="mr-2 text-xs font-medium text-indigo-600 hover:underline"
                  @click="openEdit(cat)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="text-xs font-medium text-red-600 hover:underline"
                  @click="confirmDelete(cat)"
                >
                  Delete
                </button>
              </template>
              <span v-else class="text-xs text-gray-400">
                Read-only
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      v-if="modalOpen"
      class="fixed inset-0 z-30 flex items-center justify-center bg-black/40"
    >
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg">
        <h3 class="mb-4 text-lg font-semibold">
          {{ editing ? 'Edit Category' : 'Add Category' }}
        </h3>

        <form @submit.prevent="saveCategory">
          <div class="mb-3">
            <label class="mb-1 block text-sm font-medium" for="name">
              Name
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
            />
          </div>

          <div class="mb-3" v-if="!editing">
            <label class="mb-1 block text-sm font-medium" for="type">
              Type
            </label>
            <select
              id="type"
              v-model="form.type"
              required
              class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
            >
              <option value="expense">Expense</option>
              <option value="income">Income</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="mb-1 block text-sm font-medium" for="color">
              Color (hex)
            </label>
            <input
              id="color"
              v-model="form.color"
              type="text"
              placeholder="#22c55e"
              class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
            />
          </div>

          <div class="flex justify-end gap-2">
            <button
              type="button"
              class="rounded border border-gray-300 px-3 py-1.5 text-sm"
              @click="closeModal"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="rounded bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-700"
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


