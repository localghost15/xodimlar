<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">CEO Dashboard</h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      
      <!-- Widget 1: Absent Today -->
      <div class="bg-white p-5 rounded-xl shadow-sm border border-orange-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 p-3 opacity-10">
          <i class="fas fa-user-slash text-6xl text-orange-500"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-700 mb-3">{{ stats.stat_date }} - {{ $t('dashboard.absent_title') }}</h3>
        <div v-if="stats.absent_today && stats.absent_today.length > 0" class="space-y-3">
          <div v-for="item in stats.absent_today" :key="item.id" class="flex items-center space-x-3 text-sm p-2 bg-orange-50 rounded-lg">
             <div class="w-2 h-2 rounded-full bg-orange-500"></div>
             <div>
               <p class="font-medium">{{ item.user_name }}</p>
               <p class="text-xs text-gray-500">{{ item.type === 'full_day' ? 'Весь день' : item.time_info }} - {{ item.reason }}</p>
             </div>
          </div>
        </div>
        <div v-else class="text-gray-400 text-sm">
           {{ $t('dashboard.no_absences') }}
        </div>
      </div>

      <!-- Widget 2: Purchases to Approve -->
      <div class="bg-white p-5 rounded-xl shadow-sm border border-blue-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 p-3 opacity-10">
          <i class="fas fa-file-invoice-dollar text-6xl text-blue-500"></i>
        </div>
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-lg font-semibold text-gray-700">{{ $t('dashboard.purchases_title') }}</h3>
            <button v-if="selectedPurchases.length > 0" @click="massApprove" class="text-xs bg-blue-600 text-white px-2 py-1 rounded shadow hover:bg-blue-700">
                Approve ({{ selectedPurchases.length }})
            </button>
        </div>
        
        <div v-if="stats.waiting_purchases && stats.waiting_purchases.length > 0" class="space-y-2 max-h-60 overflow-y-auto pr-1">
          <div v-for="p in stats.waiting_purchases" :key="p.id" class="p-3 bg-blue-50 rounded-lg border border-blue-100">
             <div class="flex justify-between items-start">
                 <div class="flex items-start space-x-2">
                     <input type="checkbox" :value="p.id" v-model="selectedPurchases" class="mt-1 text-blue-600 rounded">
                     <div>
                        <p class="font-medium text-sm text-gray-800">{{ p.title }}</p>
                        <p class="text-xs text-blue-600 font-bold">{{ p.price }} UZS</p>
                        <p class="text-[10px] text-gray-500">{{ p.user_name }} • {{ p.category }}</p>
                     </div>
                 </div>
                 <button @click="approveOne(p.id)" class="text-xs text-green-600 hover:text-green-800" title="Approve">
                    <i class="fas fa-check-circle text-lg"></i>
                 </button>
             </div>
          </div>
        </div>
         <div v-else class="text-gray-400 text-sm">
           {{ $t('dashboard.no_purchases') }}
        </div>
      </div>

      <!-- Widget 3: Discipline Top -->
      <div class="bg-white p-5 rounded-xl shadow-sm border border-purple-100 relative overflow-hidden">
        <h3 class="text-lg font-semibold text-gray-700 mb-3">{{ $t('dashboard.discipline_top') }}</h3>
        <div class="space-y-3">
           <div v-for="(user, index) in stats.discipline_top" :key="index" class="flex items-center justify-between p-2 hover:bg-purple-50 rounded-lg transition">
              <div class="flex items-center space-x-3">
                  <span class="w-6 h-6 flex items-center justify-center bg-purple-100 text-purple-600 rounded-full text-xs font-bold">{{ index + 1 }}</span>
                  <div>
                      <p class="text-sm font-medium">{{ user.user_name }}</p>
                      <p class="text-xs text-gray-500">{{ user.department }}</p>
                  </div>
              </div>
              <span class="text-sm font-bold text-gray-700">{{ user.total_hours }}h</span>
           </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const stats = ref({
    absent_today: [],
    waiting_purchases: [],
    discipline_top: [],
    stat_date: ''
});

const selectedPurchases = ref([]);

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/v1/ceo/stats');
        stats.value = response.data;
    } catch (e) {
        console.error("Failed to load CEO stats", e);
    }
};

const approveOne = async (id) => {
    if (!confirm('Approve this purchase?')) return;
    try {
        await axios.post(`/api/v1/purchase/${id}/approve`);
        fetchStats();
    } catch (e) {
        alert('Error approving');
    }
};

const massApprove = async () => {
    if (!confirm(`Approve ${selectedPurchases.value.length} items?`)) return;
    try {
        await axios.post('/api/v1/purchase/mass-approve', { ids: selectedPurchases.value });
        selectedPurchases.value = [];
        fetchStats();
    } catch (e) {
        alert('Error in mass approve');
    }
};

onMounted(() => {
    fetchStats();
});
</script>
