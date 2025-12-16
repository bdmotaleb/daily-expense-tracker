<template>
  <div
    v-if="open"
    class="fixed inset-0 z-30 flex items-center justify-center bg-black/40"
  >
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg">
      <h2 class="mb-4 text-lg font-semibold">
        {{ isEdit ? 'Edit Income' : 'Add Income' }}
      </h2>

      <form @submit.prevent="submit">
        <div class="mb-3">
          <label class="mb-1 block text-sm font-medium" for="date">
            Date
          </label>
          <input
            id="date"
            v-model="localForm.date"
            type="date"
            required
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
          />
        </div>

        <div class="mb-3">
          <label class="mb-1 block text-sm font-medium" for="amount">
            Amount
          </label>
          <input
            id="amount"
            v-model.number="localForm.amount"
            type="number"
            step="0.01"
            min="0"
            required
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
          />
        </div>

        <div class="mb-3">
          <label class="mb-1 block text-sm font-medium" for="source">
            Source
          </label>
          <input
            id="source"
            v-model="localForm.source"
            type="text"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
          />
        </div>

        <div class="mb-4">
          <label class="mb-1 block text-sm font-medium" for="note">
            Note
          </label>
          <textarea
            id="note"
            v-model="localForm.note"
            rows="3"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
          ></textarea>
        </div>

        <div class="flex justify-end gap-2">
          <button
            type="button"
            class="rounded border border-gray-300 px-3 py-1.5 text-sm"
            @click="$emit('close')"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="rounded bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-700"
          >
            {{ isEdit ? 'Save Changes' : 'Add Income' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, reactive } from 'vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  income: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['save', 'close']);

const isEdit = computed(() => !!props.income);

const localForm = reactive({
  id: null,
  date: '',
  amount: '',
  source: '',
  note: '',
});

watch(
  () => props.income,
  (value) => {
    if (value) {
      localForm.id = value.id;
      localForm.date = value.date ?? '';
      localForm.amount = value.amount ?? '';
      localForm.source = value.source ?? '';
      localForm.note = value.note ?? '';
    } else {
      localForm.id = null;
      localForm.date = '';
      localForm.amount = '';
      localForm.source = '';
      localForm.note = '';
    }
  },
  { immediate: true }
);

const submit = () => {
  emit('save', {
    id: localForm.id,
    date: localForm.date,
    amount: localForm.amount,
    source: localForm.source,
    note: localForm.note,
  });
};
</script>


