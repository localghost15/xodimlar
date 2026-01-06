<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">{{ $t('reports.title') }}</h1>
      <button @click="fetchData" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-sync-alt" :class="{'fa-spin': loading}"></i>
      </button>
    </div>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <div class="text-gray-500 text-xs uppercase tracking-wider font-semibold">{{ $t('reports.total_requests') }}</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_requests }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <div class="text-gray-500 text-xs uppercase tracking-wider font-semibold">{{ $t('reports.total_hours') }}</div>
            <div class="text-2xl font-bold text-blue-600 mt-1">{{ stats.total_hours }} h</div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <div class="text-gray-500 text-xs uppercase tracking-wider font-semibold">{{ $t('reports.approval_rate') }}</div>
            <div class="text-2xl font-bold text-green-600 mt-1">{{ stats.approval_rate }}%</div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
             <div class="text-gray-500 text-xs uppercase tracking-wider font-semibold">{{ $t('reports.pending') }}</div>
             <div class="text-2xl font-bold text-yellow-500 mt-1">{{ stats.status_breakdown?.pending || 0 }}</div>
        </div>
    </div>

    <!-- FILTERS -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ $t('reports.date_from') }}</label>
            <input v-model="filters.date_from" type="date" class="border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ $t('reports.date_to') }}</label>
            <input v-model="filters.date_to" type="date" class="border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
             <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ $t('reports.department') }}</label>
             <VueMultiselect
                v-model="selectedDept"
                :options="departments"
                label="name"
                track-by="id"
                placeholder="All Departments"
                class="w-48"
             />
        </div>
        <div>
             <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ $t('reports.status') }}</label>
             <VueMultiselect
                v-model="selectedStatus"
                :options="statusOptions"
                label="label"
                track-by="value"
                placeholder="All Statuses"
                class="w-40"
             />
        </div>
        <div class="flex-1">
             <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ $t('reports.search') }}</label>
             <input v-model="filters.search" type="text" :placeholder="$t('reports.search_ph')" class="w-full border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <button @click="fetchData" class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 font-medium text-sm">
            {{ $t('reports.apply') }}
        </button>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-500 font-medium border-b">
                <tr>
                    <th class="px-6 py-4">{{ $t('reports.col_employee') }}</th>
                    <th class="px-6 py-4">{{ $t('reports.col_dept') }}</th>
                    <th class="px-6 py-4">{{ $t('reports.col_date') }}</th>
                    <th class="px-6 py-4">{{ $t('reports.col_type') }}</th>
                    <th class="px-6 py-4">{{ $t('reports.col_hours') }}</th>
                    <th class="px-6 py-4">{{ $t('reports.col_status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr v-if="reportData.length === 0">
                    <td colspan="6" class="px-6 py-10 text-center text-gray-400">{{ $t('reports.no_data') }}</td>
                </tr>
                <tr v-for="item in reportData" :key="item.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ item.user_name }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ item.department }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ item.date }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded text-xs" :class="item.type === 'full_day' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'">
                             {{ item.type === 'full_day' ? $t('absence.type_full_day') : $t('absence.type_hours') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-mono text-gray-600">{{ item.calculated_hours }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-bold uppercase"
                            :class="{
                                'bg-yellow-100 text-yellow-700': item.status === 'pending',
                                'bg-green-100 text-green-700': item.status === 'approved',
                                'bg-red-100 text-red-700': item.status === 'rejected'
                            }"
                        >
                            {{ item.status }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';
import VueMultiselect from 'vue-multiselect';

const { t } = useI18n();
const loading = ref(false);

const reportData = ref([]);
const departments = ref([]);
const stats = ref({});

const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
];

const selectedDept = ref(null);
const selectedStatus = ref(null);

const filters = reactive({
    date_from: '',
    date_to: '',
    department_id: '',
    status: '',
    search: ''
});

const loadDepartments = async () => {
   try {
     const res = await axios.get('/api/v1/users/departments'); // Re-using existing endpoint
     departments.value = res.data;
   } catch (e) {
     console.error("Dept load error", e);
   }
};

const fetchData = async () => {
    loading.value = true;
    try {
        // Sync filters
        filters.department_id = selectedDept.value ? selectedDept.value.id : '';
        filters.status = selectedStatus.value ? selectedStatus.value.value : '';

        const params = { ...filters };
        const [repRes, statRes] = await Promise.all([
             axios.get('/api/v1/absence/report', { params }),
             axios.get('/api/v1/absence/statistics', { params })
        ]);

        reportData.value = repRes.data;
        stats.value = statRes.data;
    } catch (e) {
        console.error("Report error", e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadDepartments();
    fetchData();
});
</script>
