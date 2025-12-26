<template>
  <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-sm border border-gray-100 mt-6">
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

      <!-- Category & Price Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('purchase.category') }}</label>
          <select 
            v-model="form.category" 
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none bg-white"
          >
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
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
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { t } = useI18n();

const categories = ['Офисная техника', 'Мебель', 'Канцелярия', 'ПО и Лицензии', 'Хоз. товары', 'Другое'];

const form = reactive({
  title: '',
  category: 'Офисная техника',
  price: '',
  description: '',
  link: ''
});

const loading = ref(false);
const error = ref(null);

const submitForm = async () => {
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
    }
  } catch (err) {
    error.value = err.response?.data?.errors?.join(', ') || 'Error creating purchase';
  } finally {
    loading.value = false;
  }
};
</script>
