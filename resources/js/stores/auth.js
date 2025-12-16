import { defineStore } from 'pinia';
import axios, { api } from '@/bootstrap';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: false,
    initialized: false,
    error: null,
  }),

  actions: {
    async csrf() {
      await axios.get('/sanctum/csrf-cookie');
    },

    async initializeAuth() {
      if (this.initialized) return;
      this.loading = true;
      this.error = null;
      try {
        const { data } = await api.get('/me');
        this.user = data.user;
      } catch {
        this.user = null;
      } finally {
        this.loading = false;
        this.initialized = true;
      }
    },

    async login(payload) {
      this.loading = true;
      this.error = null;
      try {
        await this.csrf();
        const { data } = await api.post('/login', payload);
        this.user = data.user;
        return true;
      } catch (error) {
        this.error =
          error.response?.data?.message ||
          'Unable to login. Please check your credentials.';
        return false;
      } finally {
        this.loading = false;
      }
    },

    async register(payload) {
      this.loading = true;
      this.error = null;
      try {
        await this.csrf();
        const { data } = await api.post('/register', payload);
        this.user = data.user;
        return true;
      } catch (error) {
        if (error.response?.data?.errors) {
          this.error = Object.values(error.response.data.errors)
            .flat()
            .join(' ');
        } else {
          this.error = error.response?.data?.message || 'Registration failed.';
        }
        return false;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      this.loading = true;
      this.error = null;
      try {
        await api.post('/logout');
      } catch {
        // ignore
      } finally {
        this.user = null;
        this.loading = false;
      }
    },
  },
});
