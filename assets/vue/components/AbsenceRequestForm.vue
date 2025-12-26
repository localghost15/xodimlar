<template>
  <div class="flex flex-col lg:flex-row h-full gap-6 p-6">
    
    <!-- LEFT SIDE: HISTORY TABLE -->
    <div class="lg:w-1/2 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
      <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="font-bold text-gray-700">{{ $t('absence.history_title') }}</h3>
        <button @click="fetchHistory" class="text-indigo-600 hover:text-indigo-800 text-sm">
          <i class="fas fa-sync-alt"></i> {{ $t('absence.refresh') }}
        </button>
      </div>
      
      <div class="flex-1 overflow-auto p-0">
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-50 text-gray-500 font-medium border-b">
            <tr>
              <th class="px-4 py-3">{{ $t('absence.table_date') }}</th>
              <th class="px-4 py-3">{{ $t('absence.table_type') }}</th>
              <th class="px-4 py-3">{{ $t('absence.table_reason') }}</th>
              <th class="px-4 py-3">{{ $t('absence.table_hours') }}</th>
              <th class="px-4 py-3">{{ $t('absence.table_status') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="history.length === 0">
              <td colspan="5" class="px-4 py-8 text-center text-gray-400">{{ $t('absence.no_records') }}</td>
            </tr>
            <tr v-for="item in history" :key="item.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 whitespace-nowrap">{{ item.date }}</td>
              <td class="px-4 py-3">
                <span :class="{'bg-blue-100 text-blue-800': item.type === 'full_day', 'bg-purple-100 text-purple-800': item.type === 'hours'}" class="px-2 py-1 rounded text-xs">
                  {{ item.type === 'full_day' ? $t('absence.type_full_day') : $t('absence.type_hours') }}
                </span>
              </td>
              <td class="px-4 py-3 truncate max-w-xs" :title="item.reason">{{ item.reason }}</td>
              <td class="px-4 py-3">{{ item.calculated_hours || '-' }}</td>
              <td class="px-4 py-3">
                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">{{ item.status }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- RIGHT SIDE: FORM -->
    <div class="lg:w-1/2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-y-auto">
      <div class="p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-4">
          {{ $t('absence.title') }}
        </h2>

        <form @submit.prevent="submitForm" class="space-y-6">
          
          <!-- Date Picker -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ $t('absence.date_label') }}
            </label>
            <input 
              v-model="form.date" 
              type="date" 
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
              required
            />
          </div>

          <!-- Type Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ $t('absence.type_label') }}
            </label>
            <div class="flex space-x-4">
              <label class="flex items-center space-x-2 cursor-pointer p-3 border rounded-lg hover:bg-gray-50 flex-1" :class="{'border-indigo-500 bg-indigo-50': form.type === 'full_day'}">
                <input v-model="form.type" type="radio" value="full_day" class="text-indigo-600 focus:ring-indigo-500" />
                <span>{{ $t('absence.type_full_day') }}</span>
              </label>
              <label class="flex items-center space-x-2 cursor-pointer p-3 border rounded-lg hover:bg-gray-50 flex-1" :class="{'border-indigo-500 bg-indigo-50': form.type === 'hours'}">
                <input v-model="form.type" type="radio" value="hours" class="text-indigo-600 focus:ring-indigo-500" />
                <span>{{ $t('absence.type_hours') }}</span>
              </label>
            </div>
          </div>

          <!-- Time Interval (Conditional) -->
          <div v-if="form.type === 'hours'" class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg animate-fade-in">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ $t('absence.time_start') }}
              </label>
              <input 
                v-model="form.time_start" 
                type="time" 
                class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-indigo-500"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ $t('absence.time_end') }}
              </label>
              <input 
                v-model="form.time_end" 
                type="time" 
                class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-indigo-500"
                required
              />
            </div>
            <div class="col-span-2 text-xs text-gray-500">
              {{ $t('absence.step_info') }}
            </div>
          </div>

          <!-- Reason -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ $t('absence.reason_label') }}
              <span class="text-xs text-red-500" v-if="form.reason.length < 20">
                ({{ form.reason.length }}/20)
              </span>
            </label>
            <textarea 
              v-model="form.reason" 
              rows="3" 
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
              :placeholder="$t('absence.reason_placeholder')"
              required
            ></textarea>
            <p class="text-xs text-gray-400 mt-1">{{ $t('absence.reason_hint') }}</p>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="p-3 bg-red-50 text-red-600 text-sm rounded-lg">
            {{ error }}
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end pt-4">
            <button 
              type="submit" 
              :disabled="loading || !isValid"
              class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading">{{ $t('common.sending') }}</span>
              <span v-else>{{ $t('common.submit') }}</span>
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { t } = useI18n();

const form = reactive({
  date: new Date().toISOString().split('T')[0],
  type: 'full_day',
  time_start: '',
  time_end: '',
  reason: ''
});

const loading = ref(false);
const error = ref(null);
const history = ref([]);

const isValid = computed(() => {
  if (form.reason.length < 20) return false;
  if (form.type === 'hours') {
    return form.time_start && form.time_end && form.time_start < form.time_end;
  }
  return true;
});

const fetchHistory = async () => {
  try {
    const res = await axios.get('/api/v1/absence/my-history');
    history.value = res.data;
  } catch (e) {
    console.error("Failed to fetch history", e);
  }
};

const submitForm = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await axios.post('/api/v1/absence/create', form);
    if (response.data.success) {
      alert(t('absence.success_msg'));
      // Reset form
      form.reason = '';
      form.type = 'full_day';
      // Refresh history
      fetchHistory();
    }
  } catch (err) {
    console.error(err);
    error.value = err.response?.data?.errors?.join(', ') || 'Error occurred';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchHistory();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
