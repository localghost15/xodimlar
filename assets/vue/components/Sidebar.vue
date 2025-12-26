<template>
  <aside class="w-64 bg-gray-900 text-white min-h-screen flex flex-col transition-all duration-300">
    <div class="p-6 border-b border-gray-800">
      <h1 class="text-2xl font-bold tracking-wider text-indigo-400">Xodimlar</h1>
      <p class="text-xs text-gray-500 mt-1">Corporate System</p>
    </div>

    <nav class="flex-1 p-4 space-y-2">
      
      <!-- Common for everyone -->
      <!-- We use checking role helper or props. Assuming authStore is available -->
      
      <a href="#/dashboard" class="block px-4 py-3 rounded-lg hover:bg-gray-800 transition flex items-center space-x-3" :class="{'bg-indigo-900 text-white': currentHash === '#/dashboard'}">
         <i class="fas fa-home w-5"></i>
         <span>{{ $t('nav.dashboard') }}</span>
      </a>

      <!-- Absence Module -->
      <!-- Absence Module -->
      <a v-if="role !== 'ROLE_CEO'" href="#/absence/create" class="block px-4 py-3 rounded-lg hover:bg-gray-800 transition flex items-center space-x-3 text-gray-300 hover:text-white" :class="{'bg-indigo-900': currentHash === '#/absence/create'}">
         <i class="fas fa-calendar-minus w-5"></i>
         <span>{{ $t('nav.absence_create') }}</span>
      </a>

      <!-- Purchase Module (If not just simple employee or logic dependent) -->
      <a href="#/purchase/create" class="block px-4 py-3 rounded-lg hover:bg-gray-800 transition flex items-center space-x-3 text-gray-300 hover:text-white">
         <i class="fas fa-shopping-cart w-5"></i>
         <span>{{ $t('nav.purchase_create') }}</span>
      </a>

      <!-- CEO/Head Specific -->
      <div v-if="role === 'ROLE_CEO' || role === 'ROLE_DEPT_HEAD' || role === 'ROLE_HR'" class="pt-4 pb-2">
        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ $t('nav.management') }}</p>
        <a v-if="role === 'ROLE_CEO'" href="#/ceo/dashboard" class="block px-4 py-3 rounded-lg hover:bg-gray-800 transition flex items-center space-x-3 text-yellow-500">
           <i class="fas fa-chart-line w-5"></i>
           <span>CEO Dashboard</span>
        </a>
        <a href="#/approvals" class="block px-4 py-3 rounded-lg hover:bg-gray-800 transition flex items-center space-x-3 text-gray-300">
           <i class="fas fa-check-double w-5"></i>
           <span>{{ $t('nav.approvals') }}</span>
        </a>
      </div>

    </nav>

    <!-- Language Switcher -->
    <div class="px-6 py-2 border-t border-gray-800">
      <div class="flex items-center space-x-2 text-sm">
        <button 
          @click="changeLang('ru')" 
          :class="locale === 'ru' ? 'text-white font-bold' : 'text-gray-500 hover:text-gray-300'"
          class="transition-colors"
        >
          RU
        </button>
        <span class="text-gray-600">|</span>
        <button 
          @click="changeLang('uz')" 
          :class="locale === 'uz' ? 'text-white font-bold' : 'text-gray-500 hover:text-gray-300'"
          class="transition-colors"
        >
          UZ
        </button>
      </div>
    </div>

    <div class="p-4 border-t border-gray-800">
      <div class="flex items-center space-x-3 mb-4">
        <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-xs font-bold">
          {{ userInitials }}
        </div>
        <div class="overflow-hidden">
          <p class="text-sm font-medium truncate">{{ userName }}</p>
          <p class="text-xs text-gray-500 truncate">{{ role }}</p>
        </div>
      </div>
      <button @click="$emit('logout')" class="w-full py-2 px-4 bg-gray-800 hover:bg-red-900/50 hover:text-red-400 rounded transition text-sm flex items-center justify-center space-x-2">
         <i class="fas fa-sign-out-alt"></i>
         <span>{{ $t('nav.logout') }}</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();
const authStore = useAuthStore();
const role = computed(() => authStore.role);

const changeLang = (lang) => {
  locale.value = lang;
  localStorage.setItem('user_locale', lang);
};
const userName = computed(() => authStore.user?.full_name || 'User');
const userInitials = computed(() => userName.value.split(' ').map(n => n[0]).join('').substring(0,2));

// Simple hash listener for demo highlight (in real app use useRoute)
const currentHash = computed(() => window.location.hash || '#/');

defineEmits(['logout']);
</script>
