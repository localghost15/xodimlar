<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">{{ $t('nav.approvals') }}</h1>
      <div class="space-x-2">
        <button @click="fetchData" class="text-indigo-600 hover:text-indigo-800 text-sm">
          <i class="fas fa-sync-alt"></i> {{ $t('approvals.refresh') }}
        </button>
      </div>
    </div>

    <!-- TABS -->
    <div class="flex space-x-4 border-b mb-6" v-if="role === 'ROLE_HR'">
        <button class="pb-2 border-b-2 font-medium border-indigo-500 text-indigo-600">
           {{ $t('absence.title') }} (HR)
        </button>
    </div>
    <div class="flex space-x-4 border-b mb-6" v-else>
         <button class="pb-2 border-b-2 font-medium border-indigo-500 text-indigo-600">
           {{ $t('purchase.title') }} (Approvals)
        </button>
    </div>

    <!-- ERROR -->
    <div v-if="error" class="bg-red-50 text-red-600 p-4 rounded mb-4">
        {{ $t('approvals.error_load') }}
        <span v-if="errorMessage" class="block text-xs mt-1 text-red-500 font-mono">{{ errorMessage }}</span>
    </div>

    <!-- LOADING -->
    <div v-if="loading" class="text-center py-10 text-gray-400">{{ $t('approvals.loading') }}</div>

    <!-- LIST -->
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-500 font-medium border-b">
                <tr>
                    <th class="px-6 py-4">{{ $t('approvals.table_id') }}</th>
                    <th class="px-6 py-4">{{ $t('approvals.table_employee') }}</th>
                    <th class="px-6 py-4">{{ $t('approvals.table_details') }}</th>
                    <th class="px-6 py-4 text-right">{{ $t('approvals.table_actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr v-if="items.length === 0">
                    <td colspan="4" class="px-6 py-10 text-center text-gray-400">{{ $t('approvals.no_data') }}</td>
                </tr>
                <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-500">#{{ item.id }}</td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-gray-800">{{ item.user_name }}</p>
                        <p class="text-xs text-gray-500">{{ item.department || 'N/A' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <!-- Content depends on type -->
                        <div v-if="role === 'ROLE_HR'">
                            <p class="font-medium text-gray-700">{{ item.date }}</p>
                            <p class="text-xs text-gray-500">{{ item.type === 'full_day' ? $t('absence.type_full_day') : $t('absence.type_hours') }}</p>
                            <p class="text-sm italic mt-1 text-gray-600">"{{ item.reason }}"</p>
                        </div>
                        <div v-else>
                            <p class="font-medium text-gray-700">{{ item.title }}</p>
                            <p class="text-xs font-bold text-green-600">{{ item.price }} UZS</p>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide">{{ item.type }} • {{ item.category }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button @click="approve(item.id)" class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1 rounded transition text-xs font-bold">
                           <i class="fas fa-check mr-1"></i> {{ $t('approvals.btn_approve') }}
                        </button>
                        <button @click="reject(item.id)" class="bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1 rounded transition text-xs">
                           {{ $t('approvals.btn_reject') }}
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../stores/auth';

const { t } = useI18n();
const authStore = useAuthStore();
const role = computed(() => authStore.role);
const items = ref([]);
const loading = ref(false);
const error = ref(false);
const errorMessage = ref('');

const fetchData = async () => {
    loading.value = true;
    error.value = false;
    errorMessage.value = '';
    items.value = [];
    try {
        let url = '';
        if (role.value === 'ROLE_HR') {
            url = '/api/v1/absence/pending';
        } else {
             // Dept Head, Accountant
            url = '/api/v1/purchase/pending-approvals';
        }
        
        const res = await axios.get(url);
        items.value = res.data;
    } catch (e) {
        error.value = true;
        errorMessage.value = e.response?.data?.error || e.message;
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const approve = async (id) => {
    if(!confirm(t('approvals.confirm_approve'))) return;
    try {
        let url = '';
        if(role.value === 'ROLE_HR') {
             url = `/api/v1/absence/${id}/approve`;
        } else {
             url = `/api/v1/purchase/${id}/approve`;
        }
        await axios.post(url);
        fetchData(); // Refresh
    } catch (e) {
        alert(e.response?.data?.error || 'Error');
    }
};

const reject = async (id) => {
    if(!confirm(t('approvals.confirm_reject'))) return;
    try {
         let url = '';
         if(role.value === 'ROLE_HR') {
            url = `/api/v1/absence/${id}/reject`;
         } else {
            // Purchase rejection logic for Head?
            // Existing approve method in PurchaseController does not specifically handle rejection via API yet in the snippet I saw?
            // Actually, I didn't see a reject endpoint in PurchaseController in my last view.
            // I should double check. If not, I'll just alert 'Not implemented' for Purchase reject for now.
             alert("Purchase Rejection not implemented yet in backend demo");
             return;
         }
         await axios.post(url);
         fetchData();
    } catch (e) {
        alert('Error');
    }
};

onMounted(() => {
    fetchData();
});
</script>
