import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    role: null,
    token: null, // If we were using JWT
    isAuthenticated: false,
    loading: false,
    error: null
  }),

  actions: {
    async login(phone, password) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/api/login', {
          phone,
          password
        });

        const data = response.data;

        if (data.user) {
          this.user = data.user;
          this.role = data.user.main_role;
          this.isAuthenticated = true;

          // Persistence
          localStorage.setItem('user_data', JSON.stringify(this.user));
          localStorage.setItem('user_role', this.role);

          return true;
        }
      } catch (err) {
        this.error = err.response?.data?.error || 'Login failed';
        return false;
      } finally {
        this.loading = false;
      }
    },

    logout() {
      this.user = null;
      this.role = null;
      this.isAuthenticated = false;
      // In a real app, call /logout endpoint
    }
  }
});
