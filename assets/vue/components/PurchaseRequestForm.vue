<template>
  <div class="flex flex-col lg:flex-row h-full gap-6 p-6">

    <!-- LEFT SIDE: HISTORY TABLE -->
    <div class="lg:w-1/2 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
      <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="font-bold text-gray-700">{{ $t('purchase.history_title') }}</h3>
        <button @click="fetchHistory" class="text-indigo-600 hover:text-indigo-800 text-sm">
          <i class="fas fa-sync-alt"></i> {{ $t('absence.refresh') }}
        </button>
      </div>
      
      <div class="flex-1 overflow-auto p-0">
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-50 text-gray-500 font-medium border-b">
            <tr>
              <th class="px-4 py-3">{{ $t('purchase.table_title') }}</th>
              <th class="px-4 py-3">{{ $t('purchase.table_type') }}</th>
              <th class="px-4 py-3">{{ $t('purchase.table_price') }}</th>
              <th class="px-4 py-3">{{ $t('purchase.table_status') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="history.length === 0">
              <td colspan="4" class="px-4 py-8 text-center text-gray-400">{{ $t('absence.no_records') }}</td>
            </tr>
            <tr v-for="item in history" :key="item.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-900">{{ item.title }}</td>
              <td class="px-4 py-3">
                <span :class="{'bg-purple-100 text-purple-800': item.type === 'asset', 'bg-green-100 text-green-800': item.type === 'consumable'}" class="px-2 py-1 rounded text-xs">
                  {{ item.type === 'asset' ? $t('purchase.type_asset') : $t('purchase.type_consumable') }}
                </span>
              </td>
              <td class="px-4 py-3">{{ item.price }}</td>
              <td class="px-4 py-3">
                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs">{{ item.status }}</span>
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
          {{ $t('purchase.title') }}
        </h2>

        <form @submit.prevent="submitForm" class="space-y-5">
          
          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('purchase.item_name') }}</label>
            <input 
              v-model="form.title" 
              type="text" 
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
              required
            />
          </div>

          <!-- Type (New Field) -->
          <div>
             <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('purchase.type_label') }}</label>
             <div class="flex space-x-4">
               <label class="flex items-center space-x-2 cursor-pointer p-3 border rounded-lg hover:bg-gray-50 flex-1" :class="{'border-indigo-500 bg-indigo-50': form.type === 'asset'}">
                 <input v-model="form.type" type="radio" value="asset" class="text-indigo-600 focus:ring-indigo-500" />
                 <span>{{ $t('purchase.type_asset') }}</span>
               </label>
               <label class="flex items-center space-x-2 cursor-pointer p-3 border rounded-lg hover:bg-gray-50 flex-1" :class="{'border-indigo-500 bg-indigo-50': form.type === 'consumable'}">
                 <input v-model="form.type" type="radio" value="consumable" class="text-indigo-600 focus:ring-indigo-500" />
                 <span>{{ $t('purchase.type_consumable') }}</span>
               </label>
             </div>
             <p class="text-xs text-gray-400 mt-1" v-if="form.type==='asset'">{{ $t('purchase.hint_asset') }}</p>
             <p class="text-xs text-gray-400 mt-1" v-if="form.type==='consumable'">{{ $t('purchase.hint_consumable') }}</p>
          </div>

          <!-- Category & Price Row -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('purchase.category') }}</label>
              <VueMultiselect 
                v-model="selectedCategory" 
                :options="categories"
                track-by="value"
                placeholder="Select Category"
              >
                  <template #singleLabel="{ option }">
                        {{ $t('categories.' + option.value) }}
                  </template>
                  <template #option="{ option }">
                        {{ $t('categories.' + option.value) }}
                  </template>
              </VueMultiselect>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('purchase.price') }} (UZS)</label>
              <input 
                v-model="form.price" 
                type="number" 
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
                placeholder="0"
                required
              />
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('purchase.description') }}</label>
            <textarea 
              v-model="form.description" 
              rows="3" 
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
            ></textarea>
          </div>

          <!-- Link -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('purchase.link') }}</label>
            <div class="flex items-center border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500">
               <div class="bg-gray-50 px-3 py-2 border-r text-gray-500">
                 <i class="fas fa-link"></i>
               </div>
               <input 
                v-model="form.link" 
                type="url" 
                class="w-full px-4 py-2 outline-none"
                placeholder="https://..."
              />
            </div>
          </div>

          <!-- Error -->
          <div v-if="error" class="p-3 bg-red-50 text-red-600 text-sm rounded-lg">
            {{ error }}
          </div>

          <!-- Submit -->
          <div class="flex justify-end pt-4">
            <button 
              type="submit" 
              :disabled="loading"
              class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition disabled:opacity-50"
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
import { ref, reactive, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import VueMultiselect from 'vue-multiselect';

const { t } = useI18n();

const categoryKeys = ['office_equip', 'furniture', 'stationery', 'software', 'household', 'other'];
const categories = categoryKeys.map(key => ({ value: key })); // We will use a function to get label reactively

const selectedCategory = ref({ value: 'office_equip' });

const form = reactive({
  title: '',
  category: 'office_equip',
  type: 'asset',
  price: '',
  description: '',
  link: ''
});

const loading = ref(false);
const error = ref(null);
const history = ref([]);

const fetchHistory = async () => {
  try {
    const res = await axios.get('/api/v1/purchase/my-history');
    history.value = res.data;
  } catch (e) {
    console.error("Failed to fetch purchase history", e);
  }
};

const submitForm = async () => {
  // Sync selected category
  form.category = selectedCategory.value ? selectedCategory.value.value : 'office_equip';

  loading.value = true;
  error.value = null;

  try {
    const response = await axios.post('/api/v1/purchase/create', form);
    if (response.data.success) {
      alert(t('purchase.success_msg'));
      form.title = '';
      form.price = '';
      form.description = '';
      form.link = '';
      // Refresh history
      fetchHistory();
    }
  } catch (err) {
    error.value = err.response?.data?.errors?.join(', ') || 'Error creating purchase';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchHistory();
});
</script>
