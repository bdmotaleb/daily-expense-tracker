import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const Login = () => import('@/pages/Login.vue');
const Register = () => import('@/pages/Register.vue');
const Dashboard = () => import('@/pages/Dashboard.vue');
const IncomeList = () => import('@/pages/IncomeList.vue');
const ExpenseList = () => import('@/pages/ExpenseList.vue');
const CategoryList = () => import('@/pages/CategoryList.vue');
const Budget = () => import('@/pages/Budget.vue');
const Reports = () => import('@/pages/Reports.vue');

const routes = [
  {
    path: '/',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true },
  },
  {
    path: '/incomes',
    name: 'incomes',
    component: IncomeList,
    meta: { requiresAuth: true },
  },
  {
    path: '/expenses',
    name: 'expenses',
    component: ExpenseList,
    meta: { requiresAuth: true },
  },
  {
    path: '/categories',
    name: 'categories',
    component: CategoryList,
    meta: { requiresAuth: true },
  },
  {
    path: '/budgets',
    name: 'budgets',
    component: Budget,
    meta: { requiresAuth: true },
  },
  {
    path: '/reports',
    name: 'reports',
    component: Reports,
    meta: { requiresAuth: true },
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guestOnly: true },
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { guestOnly: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to) => {
  const auth = useAuthStore();

  if (!auth.initialized) {
    await auth.initializeAuth();
  }

  if (to.meta.requiresAuth && !auth.user) {
    return { name: 'login', query: { redirect: to.fullPath } };
  }

  if (to.meta.guestOnly && auth.user) {
    return { name: 'dashboard' };
  }

  return true;
});

export default router;

