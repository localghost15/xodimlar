<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $t('dashboard.welcome') }}, {{ userName }}!</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $t('dashboard.overview_subtitle') }}</p>
      </div>
      <div class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg font-medium text-sm">
        {{ roleName }}
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      
      <!-- Widget: Absence Stats -->
      <div class="bg-white p-5 rounded-xl shadow-sm border border-indigo-100 relative overflow-hidden group hover:shadow-md transition">
        <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition">
          <i class="fas fa-clock text-6xl text-indigo-500"></i>
        </div>
        <p class="text-sm text-gray-500 mb-1">{{ $t('nav.absence_create') }}</p>
        <h3 class="text-3xl font-bold text-gray-800">{{ absencePending }}</h3>
        <p class="text-xs text-yellow-600 mt-2 font-medium">
          <i class="fas fa-hourglass-half"></i> {{ $t('dashboard.status_pending') }}
        </p>
        <a href="#/absence/create" class="absolute inset-0"></a>
      </div>

      <!-- Widget: Purchase Stats -->
      <div class="bg-white p-5 rounded-xl shadow-sm border border-green-100 relative overflow-hidden group hover:shadow-md transition">
        <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition">
          <i class="fas fa-shopping-bag text-6xl text-green-500"></i>
        </div>
        <p class="text-sm text-gray-500 mb-1">{{ $t('nav.purchase_create') }}</p>
        <h3 class="text-3xl font-bold text-gray-800">{{ purchasePending }}</h3>
        <p class="text-xs text-yellow-600 mt-2 font-medium">
          <i class="fas fa-hourglass-half"></i> {{ $t('dashboard.status_pending') }}
        </p>
        <a href="#/purchase/create" class="absolute inset-0"></a>
      </div>

    </div>

    <!-- Recent Activity / Quick Links -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Quick Actions -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-700 mb-4">{{ $t('dashboard.quick_actions') }}</h3>
        <div class="space-y-3">
          <a href="#/absence/create" class="block w-full text-left p-3 rounded-lg border hover:bg-indigo-50 hover:border-indigo-200 transition group">
            <div class="flex items-center justify-between">
              <span class="font-medium text-gray-700 group-hover:text-indigo-700">{{ $t('absence.title') }}</span>
              <i class="fas fa-arrow-right text-gray-300 group-hover:text-indigo-500"></i>
            </div>
          </a>
          <a href="#/purchase/create" class="block w-full text-left p-3 rounded-lg border hover:bg-green-50 hover:border-green-200 transition group">
             <div class="flex items-center justify-between">
              <span class="font-medium text-gray-700 group-hover:text-green-700">{{ $t('purchase.title') }}</span>
              <i class="fas fa-arrow-right text-gray-300 group-hover:text-green-500"></i>
            </div>
          </a>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const userName = computed(() => authStore.user?.full_name || 'User');
const roleName = computed(() => authStore.role === 'ROLE_CEO' ? 'CEO' : (authStore.role === 'ROLE_DEPT_HEAD' ? 'Department Head' : 'Employee'));

const absencePending = ref(0);
const purchasePending = ref(0);

const fetchStats = async () => {
  try {
    // Fetch absences
    const absRes = await axios.get('/api/v1/absence/my-history');
    if (Array.isArray(absRes.data)) {
        absencePending.value = absRes.data.filter(a => a.status === 'pending').length;
    }

    // Fetch purchases
    const purRes = await axios.get('/api/v1/purchase/my-history');
    if (Array.isArray(purRes.data)) {
        purchasePending.value = purRes.data.filter(p => p.status === 'new' || p.status.includes('pending')).length;
    }
  } catch (e) {
    console.error("Failed to fetch user stats", e);
  }
};

onMounted(() => {
  fetchStats();
});
</script>
