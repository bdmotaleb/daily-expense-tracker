<template>
  <div class="min-h-full space-y-8 rounded-2xl bg-slate-100/90 px-4 py-6 shadow-xl ring-1 ring-slate-800 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col gap-4 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="space-y-1">
        <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
          <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
          At a glance
        </div>
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
          Dashboard
        </h2>
        <p class="max-w-xl text-sm text-slate-500">
          Overview of your income, expenses, and balance so you can see where your money is going.
        </p>
      </div>

      <div
        ref="monthPickerWrapperRef"
        class="relative flex flex-wrap items-center gap-3"
      >
        <div class="flex flex-col text-right text-xs text-slate-500 sm:text-sm">
          <span>Selected month</span>
          <span class="font-medium text-slate-800">
            {{ formattedSelectedMonth }}
          </span>
        </div>

        <!-- Custom month picker trigger -->
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-slate-200 transition-colors hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-50"
          @click="toggleMonthPicker"
        >
          <span class="hidden text-xs font-medium text-slate-600 sm:inline">Change month</span>
          <span class="inline-flex items-center gap-1 text-xs text-slate-500">
            <svg
              class="h-4 w-4 text-slate-400"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <rect
                x="3"
                y="4"
                width="18"
                height="18"
                rx="2"
                class="stroke-current"
                stroke-width="1.5"
              />
              <path
                d="M3 9H21"
                class="stroke-current"
                stroke-width="1.5"
                stroke-linecap="round"
              />
              <path
                d="M8 3V7"
                class="stroke-current"
                stroke-width="1.5"
                stroke-linecap="round"
              />
              <path
                d="M16 3V7"
                class="stroke-current"
                stroke-width="1.5"
                stroke-linecap="round"
              />
            </svg>
            <span>{{ formattedSelectedMonth }}</span>
          </span>
        </button>

        <!-- Native input (for accessibility & mobile) -->
        <input
          id="month"
          v-model="month"
          type="month"
          class="sr-only"
          @change="handleNativeMonthChange"
        />

        <!-- Month picker popover -->
        <div
          v-if="isMonthPickerOpen"
          ref="monthPickerRef"
          class="absolute right-0 top-12 z-20 w-72 rounded-xl bg-white p-3 text-xs shadow-lg ring-1 ring-slate-200"
        >
          <div class="mb-2 flex items-center justify-between">
            <button
              type="button"
              class="rounded-full p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
              @click="changeYear(-1)"
            >
              ‹
            </button>
            <span class="text-sm font-medium text-slate-800">
              {{ currentYear }}
            </span>
            <button
              type="button"
              class="rounded-full p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
              @click="changeYear(1)"
            >
              ›
            </button>
          </div>
          <div class="grid grid-cols-3 gap-1.5">
            <button
              v-for="(label, index) in monthLabels"
              :key="label"
              type="button"
              class="rounded-lg px-2 py-1.5 text-center text-xs font-medium transition-colors"
              :class="
                index === selectedMonthIndex && currentYear === selectedYear
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'bg-slate-50 text-slate-700 hover:bg-slate-100'
              "
              @click="selectMonth(index)"
            >
              {{ label }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Error banner -->
    <div
      v-if="error"
      class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
    >
      <span class="mt-0.5 h-5 w-5 flex-shrink-0 rounded-full bg-red-100 text-center text-xs font-semibold leading-5">!</span>
      <p>{{ error }}</p>
    </div>

    <!-- Loading indicator -->
    <div v-if="loading" class="rounded-xl border border-slate-200 bg-white/70 px-4 py-3 text-sm text-slate-500 shadow-sm">
      Loading your dashboard…
    </div>

    <!-- Key metrics -->
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <!-- Today summary -->
      <div
        class="group flex flex-col justify-between rounded-xl bg-white/90 p-4 shadow-sm ring-1 ring-slate-200/80 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white hover:shadow-md hover:ring-indigo-100"
      >
        <div class="flex items-start justify-between gap-2">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
              Today
            </p>
            <p class="mt-1 text-xs text-slate-400">
              Income · Expense · Balance
            </p>
          </div>
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-600">
            T
          </span>
        </div>
        <div class="mt-4 space-y-1.5 text-sm">
          <div class="flex items-center justify-between text-slate-600">
            <span>Income</span>
            <span class="font-medium text-emerald-600">
              {{ formatMoney(today.income) }}
            </span>
          </div>
          <div class="flex items-center justify-between text-slate-600">
            <span>Expense</span>
            <span class="font-medium text-red-600">
              {{ formatMoney(today.expense) }}
            </span>
          </div>
          <div class="mt-1 flex items-center justify-between border-t border-dashed border-slate-200 pt-2 text-xs uppercase tracking-wide">
            <span class="text-slate-500">Balance</span>
            <span
              class="text-sm font-semibold"
              :class="today.balance >= 0 ? 'text-emerald-600' : 'text-red-600'"
            >
              {{ formatMoney(today.balance) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Monthly income -->
      <div
        class="group flex flex-col justify-between rounded-xl bg-white/90 p-4 shadow-sm ring-1 ring-slate-200/80 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white hover:shadow-md hover:ring-indigo-100"
      >
        <div class="flex items-start justify-between gap-2">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
              This Month
            </p>
            <p class="mt-1 text-xs text-slate-400">
              Total income across all categories
            </p>
          </div>
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-xs font-semibold text-emerald-600">
            +
          </span>
        </div>
        <p class="mt-4 text-2xl font-semibold tracking-tight text-emerald-600">
          {{ formatMoney(monthly.income) }}
        </p>
        <p class="mt-1 text-xs text-slate-400">
          Compared to expenses below.
        </p>
      </div>

      <!-- Monthly expense -->
      <div
        class="group flex flex-col justify-between rounded-xl bg-white/90 p-4 shadow-sm ring-1 ring-slate-200/80 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white hover:shadow-md hover:ring-indigo-100"
      >
        <div class="flex items-start justify-between gap-2">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
              This Month
            </p>
            <p class="mt-1 text-xs text-slate-400">
              Total expense across all categories
            </p>
          </div>
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-50 text-xs font-semibold text-red-600">
            −
          </span>
        </div>
        <p class="mt-4 text-2xl font-semibold tracking-tight text-red-600">
          {{ formatMoney(monthly.expense) }}
        </p>
        <p class="mt-1 text-xs text-slate-400">
          Try to keep this below your income.
        </p>
      </div>

      <!-- Monthly net + overall -->
      <div
        class="group flex flex-col justify-between rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 p-4 text-indigo-50 shadow-sm ring-1 ring-indigo-500/40 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:ring-indigo-300"
      >
        <div class="flex items-start justify-between gap-2">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-100">
              Net Balance
            </p>
            <p class="mt-1 text-xs text-indigo-100/80">
              For {{ month }}
            </p>
          </div>
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-xs font-semibold text-white">
            Σ
          </span>
        </div>
        <p
          class="mt-4 text-2xl font-semibold tracking-tight"
          :class="monthly.balance >= 0 ? 'text-emerald-100' : 'text-red-100'"
        >
          {{ formatMoney(monthly.balance) }}
        </p>
        <div class="mt-3 rounded-lg bg-black/10 px-3 py-2 text-xs">
          <p class="flex items-center justify-between">
            <span class="text-indigo-100/80">Overall balance</span>
            <span
              class="font-semibold"
              :class="overall.balance >= 0 ? 'text-emerald-100' : 'text-rose-100'"
            >
              {{ formatMoney(overall.balance) }}
            </span>
          </p>
          <p class="mt-1 text-[11px] text-indigo-100/70">
            Income: {{ formatMoney(overall.income) }} · Expense:
            {{ formatMoney(overall.expense) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid gap-6 md:grid-cols-2">
      <!-- Expense by category -->
      <div
        class="group rounded-xl bg-white/90 p-4 shadow-sm ring-1 ring-slate-200/80 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white hover:shadow-md hover:ring-indigo-100"
      >
        <div class="mb-3 flex items-center justify-between gap-2">
          <div>
            <h3 class="text-sm font-semibold text-slate-900">
              Expense by Category
            </h3>
            <p class="text-xs text-slate-500">
              See where most of your spending goes.
            </p>
          </div>
        </div>
        <div class="mt-2 min-h-[260px]">
          <canvas id="expenseCategoryChart" class="h-full w-full"></canvas>
        </div>
      </div>

      <!-- Daily expense -->
      <div
        class="group rounded-xl bg-white/90 p-4 shadow-sm ring-1 ring-slate-200/80 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white hover:shadow-md hover:ring-indigo-100"
      >
        <div class="mb-3 flex items-center justify-between gap-2">
          <div>
            <h3 class="text-sm font-semibold text-slate-900">
              Daily Expense ({{ month }})
            </h3>
            <p class="text-xs text-slate-500">
              Track how your daily spending changes over the month.
            </p>
          </div>
        </div>
        <div class="mt-2 min-h-[260px]">
          <canvas id="dailyExpenseChart" class="h-full w-full"></canvas>
        </div>
      </div>

      <!-- Monthly income & expense trend -->
      <div
        class="group rounded-xl bg-white/90 p-4 shadow-sm ring-1 ring-slate-200/80 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white hover:shadow-md hover:ring-indigo-100 md:col-span-2"
      >
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
          <div>
            <h3 class="text-sm font-semibold text-slate-900">
              Month-wise Income &amp; Expense (Last 12 Months)
            </h3>
            <p class="text-xs text-slate-500">
              Visualize your longer-term trends and patterns.
            </p>
          </div>
        </div>
        <div class="mt-2 min-h-[280px]">
          <canvas id="monthlyIncomeExpenseChart" class="h-full w-full"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { api } from '@/bootstrap';
import Chart from 'chart.js/auto';

const month = ref(new Date().toISOString().slice(0, 7)); // YYYY-MM
const today = ref({ income: 0, expense: 0, balance: 0 });
const monthly = ref({ income: 0, expense: 0, balance: 0 });
const overall = ref({ income: 0, expense: 0, balance: 0 });
const expenseByCategory = ref([]);
const dailyExpense = ref([]);
const monthlyTrend = ref([]);
const error = ref(null);
const loading = ref(false);

// Month picker state
const isMonthPickerOpen = ref(false);
const monthPickerRef = ref(null);
const monthPickerWrapperRef = ref(null);
const currentYear = ref(new Date().getFullYear());
const selectedYear = ref(currentYear.value);
const selectedMonthIndex = ref(new Date().getMonth());
const monthLabels = [
  'Jan',
  'Feb',
  'Mar',
  'Apr',
  'May',
  'Jun',
  'Jul',
  'Aug',
  'Sep',
  'Oct',
  'Nov',
  'Dec',
];

const formattedSelectedMonth = computed(() => {
  const [y, m] = month.value.split('-');
  const idx = Number(m) - 1;
  return `${monthLabels[idx] || ''} ${y}`;
});

let pieChart = null;
let lineChart = null;
let monthlyChart = null;

const syncPickerFromValue = () => {
  if (!month.value) return;
  const [y, m] = month.value.split('-');
  const year = Number(y);
  const monthIdx = Number(m) - 1;
  if (!Number.isNaN(year) && monthIdx >= 0 && monthIdx <= 11) {
    currentYear.value = year;
    selectedYear.value = year;
    selectedMonthIndex.value = monthIdx;
  }
};

const handleNativeMonthChange = () => {
  syncPickerFromValue();
  loadDashboard();
};

const toggleMonthPicker = () => {
  if (!isMonthPickerOpen.value) {
    syncPickerFromValue();
  }
  isMonthPickerOpen.value = !isMonthPickerOpen.value;
};

const changeYear = (delta) => {
  currentYear.value += delta;
  selectedYear.value = currentYear.value;
};

const selectMonth = (index) => {
  selectedMonthIndex.value = index;
  const monthNumber = String(index + 1).padStart(2, '0');
  month.value = `${selectedYear.value}-${monthNumber}`;
  isMonthPickerOpen.value = false;
  loadDashboard();
};

const handleClickOutside = (event) => {
  if (!isMonthPickerOpen.value) return;
  const wrapper = monthPickerWrapperRef.value;
  if (wrapper && !wrapper.contains(event.target)) {
    isMonthPickerOpen.value = false;
  }
};

const loadDashboard = async () => {
  loading.value = true;
  error.value = null;
  try {
    const { data } = await api.get('/dashboard', {
      params: { month: month.value },
    });

    today.value = data.today || today.value;
    monthly.value = data.monthly || monthly.value;
    overall.value = data.overall || overall.value;
    expenseByCategory.value = data.expense_by_category || [];
    dailyExpense.value = data.daily_expense || [];
    monthlyTrend.value = data.monthly_trend || [];

    renderCharts();
  } catch (e) {
    error.value = 'Failed to load dashboard data.';
  } finally {
    loading.value = false;
  }
};

const renderCharts = () => {
  const pieCtx = document.getElementById('expenseCategoryChart');
  const lineCtx = document.getElementById('dailyExpenseChart');
  const monthlyCtx = document.getElementById('monthlyIncomeExpenseChart');

  if (!pieCtx || !lineCtx || !monthlyCtx) return;

  if (pieChart) {
    pieChart.destroy();
  }
  if (lineChart) {
    lineChart.destroy();
  }
  if (monthlyChart) {
    monthlyChart.destroy();
  }

  const labels = expenseByCategory.value.map((x) => x.name);
  const values = expenseByCategory.value.map((x) => x.amount);
  const colors = expenseByCategory.value.map(
    (x, idx) =>
      x.color ||
      ['#6366f1', '#22c55e', '#ef4444', '#eab308', '#14b8a6', '#f97316'][
        idx % 6
      ]
  );

  pieChart = new Chart(pieCtx, {
    type: 'pie',
    data: {
      labels,
      datasets: [
        {
          data: values,
          backgroundColor: colors,
        },
      ],
    },
    options: {
      plugins: {
        legend: { position: 'bottom' },
      },
    },
  });

  const lineLabels = dailyExpense.value.map((x) => x.date);
  const lineValues = dailyExpense.value.map((x) => x.total);

  lineChart = new Chart(lineCtx, {
    type: 'line',
    data: {
      labels: lineLabels,
      datasets: [
        {
          label: 'Daily Expense',
          data: lineValues,
          borderColor: '#ef4444',
          backgroundColor: 'rgba(239,68,68,0.1)',
          tension: 0.25,
          fill: true,
        },
      ],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });

  const monthlyLabels = monthlyTrend.value.map((x) => x.month);
  const monthlyIncomeValues = monthlyTrend.value.map((x) => x.income);
  const monthlyExpenseValues = monthlyTrend.value.map((x) => x.expense);

  monthlyChart = new Chart(monthlyCtx, {
    type: 'bar',
    data: {
      labels: monthlyLabels,
      datasets: [
        {
          label: 'Income',
          data: monthlyIncomeValues,
          backgroundColor: 'rgba(34,197,94,0.6)',
        },
        {
          label: 'Expense',
          data: monthlyExpenseValues,
          backgroundColor: 'rgba(239,68,68,0.6)',
        },
      ],
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
};

onMounted(() => {
  syncPickerFromValue();
  document.addEventListener('click', handleClickOutside);
  loadDashboard();
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

const formatMoney = (value) => {
  return Number(value || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};
</script>


