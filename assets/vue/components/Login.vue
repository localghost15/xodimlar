<template>
  <div class="h-screen w-full flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-lg border border-gray-100">
      
      <!-- Logo placeholder -->
      <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-indigo-600">Xodimlar</h1>
        <p class="text-gray-500 mt-2">{{ $t('login.subtitle') }}</p>
      </div>

      <!-- Language Switcher -->
      <div class="flex justify-center mb-6 space-x-4">
        <button 
          @click="changeLang('ru')"
          :class="locale === 'ru' ? 'text-indigo-600 font-bold' : 'text-gray-400'"
          class="transition-colors"
        >
          Русский
        </button>
        <span class="text-gray-300">|</span>
        <button 
          @click="changeLang('uz')"
          :class="locale === 'uz' ? 'text-indigo-600 font-bold' : 'text-gray-400'"
          class="transition-colors"
        >
          O'zbekcha
        </button>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="space-y-6">
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $t('login.phone_label') }}
          </label>
          <input 
            v-model="phone"
            type="tel"
            placeholder="+998 90 123 45 67"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all"
            :class="{'border-red-500': error}"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $t('login.password_label') }}
          </label>
          <input 
            v-model="password"
            type="password"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all"
            required
          />
        </div>

        <div v-if="error" class="text-red-500 text-sm text-center">
          {{ error }}
        </div>

        <button 
          type="submit" 
          :disabled="authStore.loading"
          class="w-full bg-indigo-600 text-white py-3 rounded-lg font-medium hover:bg-indigo-700 transition-colors flex justify-center"
        >
          <span v-if="authStore.loading">{{ $t('login.loading') }}</span>
          <span v-else>{{ $t('login.submit_btn') }}</span>
        </button>

      </form>

      <div class="mt-6 text-center text-xs text-gray-400">
        &copy; 2023 Xodimlar System
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../stores/auth';
// import { useRouter } from 'vue-router'; 

const { t, locale } = useI18n();
const authStore = useAuthStore();
// const router = useRouter();

const phone = ref('');
const password = ref('');
const error = ref(null);

const changeLang = (lang) => {
  locale.value = lang;
  // In real app, sync this with user profile or API
};

const handleLogin = async () => {
  error.value = null;

  // Basic Frontend Validation
  const phoneRegex = /^\+?998\d{9}$|^\d{9,12}$/;
  // Allow simple formats for user convenience, but backend validates strict
  // Requirement: Validate phone number.
  if (!phone.value || phone.value.length < 9) {
      error.value = t('login.error_phone');
      return;
  }

  const success = await authStore.login(phone.value, password.value);
  
  if (success) {
      // console.log('Logged in as:', authStore.role);
      // In real app, router is available.
      // router.push('/dashboard'); 
      // For this environment demo:
      console.log("Redirecting to /dashboard...");
      window.location.hash = "#/dashboard"; // Simple hash simulated nav if no router
  } else {
      error.value = authStore.error || 'Ошибка входа';
  }
};
</script>
