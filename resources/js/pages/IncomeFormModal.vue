<template>
  <div
    v-if="open"
    class="fixed inset-0 z-30 flex items-center justify-center bg-black/60 backdrop-blur"
  >
    <div class="w-full max-w-md rounded-2xl bg-slate-950/95 p-6 text-slate-100 shadow-2xl ring-1 ring-slate-700">
      <h2 class="mb-4 text-lg font-semibold tracking-tight">
        {{ isEdit ? 'Edit Income' : 'Add Income' }}
      </h2>

      <form class="space-y-4" @submit.prevent="submit">
        <div class="space-y-1.5">
          <label class="mb-1 block text-xs font-medium text-slate-300" for="date">
            Date
          </label>
          <input
            id="date"
            v-model="localForm.date"
            type="date"
            required
            class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-50 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
          />
        </div>

        <div class="space-y-1.5">
          <label class="mb-1 block text-xs font-medium text-slate-300" for="amount">
            Amount
          </label>
          <input
            id="amount"
            v-model.number="localForm.amount"
            type="number"
            step="0.01"
            min="0"
            required
            class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-50 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
          />
        </div>

        <div class="space-y-1.5">
          <label class="mb-1 block text-xs font-medium text-slate-300" for="source">
            Source
          </label>
          <input
            id="source"
            v-model="localForm.source"
            type="text"
            class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-50 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
          />
        </div>

        <div class="space-y-1.5">
          <label class="mb-1 block text-xs font-medium text-slate-300" for="note">
            Note
          </label>
          <textarea
            id="note"
            v-model="localForm.note"
            rows="3"
            class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-50 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/60"
          ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-200 ring-1 ring-slate-600 transition hover:bg-slate-800"
            @click="$emit('close')"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="rounded-lg bg-emerald-600 px-4 py-1.5 text-sm font-semibold text-white shadow-sm ring-1 ring-emerald-500/60 transition hover:bg-emerald-500 hover:shadow-md"
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


