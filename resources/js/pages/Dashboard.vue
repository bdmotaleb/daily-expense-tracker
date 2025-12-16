<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-semibold">Dashboard</h2>
        <p class="text-sm text-gray-600">
          Overview of your income and expenses.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-700" for="month">Month</label>
        <input
          id="month"
          v-model="month"
          type="month"
          class="rounded border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring focus:ring-indigo-200"
          @change="loadDashboard"
        />
      </div>
    </div>

    <div v-if="error" class="rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
      {{ error }}
    </div>

    <div class="grid gap-4 md:grid-cols-4">
      <div class="rounded-lg bg-white p-4 shadow-sm">
        <h3 class="text-xs font-semibold uppercase text-gray-500">
          Today Balance
        </h3>
        <p class="mt-2 text-2xl font-semibold">
          {{ formatMoney(today.balance) }}
        </p>
        <p class="mt-1 text-xs text-gray-500">
          Income: {{ formatMoney(today.income) }} · Expense:
          {{ formatMoney(today.expense) }}
        </p>
      </div>
      <div class="rounded-lg bg-white p-4 shadow-sm">
        <h3 class="text-xs font-semibold uppercase text-gray-500">
          Monthly Balance
        </h3>
        <p class="mt-2 text-2xl font-semibold">
          {{ formatMoney(monthly.balance) }}
        </p>
        <p class="mt-1 text-xs text-gray-500">
          Income: {{ formatMoney(monthly.income) }} · Expense:
          {{ formatMoney(monthly.expense) }}
        </p>
      </div>
      <div class="rounded-lg bg-white p-4 shadow-sm">
        <h3 class="text-xs font-semibold uppercase text-gray-500">
          Monthly Net Position
        </h3>
        <p
          class="mt-2 text-2xl font-semibold"
          :class="monthly.balance >= 0 ? 'text-emerald-600' : 'text-red-600'"
        >
          {{ formatMoney(monthly.balance) }}
        </p>
        <p class="mt-1 text-xs text-gray-500">
          For {{ month }}
        </p>
      </div>
      <div class="rounded-lg bg-white p-4 shadow-sm">
        <h3 class="text-xs font-semibold uppercase text-gray-500">
          Total Balance
        </h3>
        <p
          class="mt-2 text-2xl font-semibold"
          :class="overall.balance >= 0 ? 'text-emerald-600' : 'text-red-600'"
        >
          {{ formatMoney(overall.balance) }}
        </p>
        <p class="mt-1 text-xs text-gray-500">
          Income: {{ formatMoney(overall.income) }} · Expense:
          {{ formatMoney(overall.expense) }}
        </p>
      </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
      <div class="rounded-lg bg-white p-4 shadow-sm">
        <div class="mb-2 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-800">
            Expense by Category
          </h3>
        </div>
        <canvas id="expenseCategoryChart" class="w-full"></canvas>
      </div>

      <div class="rounded-lg bg-white p-4 shadow-sm">
        <div class="mb-2 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-800">
            Daily Expense ({{ month }})
          </h3>
        </div>
        <canvas id="dailyExpenseChart" class="w-full"></canvas>
      </div>

      <div class="rounded-lg bg-white p-4 shadow-sm md:col-span-2">
        <div class="mb-2 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-800">
            Month-wise Income &amp; Expense (Last 12 Months)
          </h3>
        </div>
        <canvas id="monthlyIncomeExpenseChart" class="w-full"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
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

let pieChart = null;
let lineChart = null;
let monthlyChart = null;

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

onMounted(loadDashboard);

const formatMoney = (value) => {
  return Number(value || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};
</script>


