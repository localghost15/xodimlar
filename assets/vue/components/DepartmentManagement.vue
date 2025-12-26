<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">{{ $t('departments.title') }}</h1>
      <button @click="openModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition shadow-sm flex items-center">
        <i class="fas fa-plus mr-2"></i> {{ $t('departments.add_btn') }}
      </button>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-3xl">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-500 font-medium border-b">
                <tr>
                    <th class="px-6 py-4 w-20">ID</th>
                    <th class="px-6 py-4">{{ $t('departments.field_name') }}</th>
                    <th class="px-6 py-4 text-right">{{ $t('departments.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr v-if="departments.length === 0">
                    <td colspan="3" class="px-6 py-10 text-center text-gray-400">{{ $t('departments.no_data') }}</td>
                </tr>
                <tr v-for="dept in departments" :key="dept.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-500">#{{ dept.id }}</td>
                    <td class="px-6 py-4 font-bold text-gray-800">{{ dept.name }}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button @click="openModal(dept)" class="text-indigo-600 hover:text-indigo-800 bg-indigo-50 p-2 rounded">
                           <i class="fas fa-edit"></i>
                        </button>
                        <button @click="deleteDept(dept.id)" class="text-red-600 hover:text-red-800 bg-red-50 p-2 rounded">
                           <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- MODAL -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
            <button @click="closeModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
            <h2 class="text-xl font-bold mb-4">
                {{ isEdit ? $t('departments.edit_title') : $t('departments.new_title') }}
            </h2>

            <form @submit.prevent="saveDept" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('departments.field_name') }}</label>
                    <input v-model="form.name" type="text" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" @click="closeModal" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        {{ $t('common.cancel') }}
                    </button>
                    <button type="submit" :disabled="submitting" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        {{ submitting ? $t('common.sending') : $t('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const departments = ref([]);
const showModal = ref(false);
const submitting = ref(false);
const isEdit = ref(false);
const editId = ref(null);

const form = ref({
    name: ''
});

const openModal = (dept = null) => {
    if (dept) {
        isEdit.value = true;
        editId.value = dept.id;
        form.value.name = dept.name;
    } else {
        isEdit.value = false;
        editId.value = null;
        form.value.name = '';
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const fetchDepts = async () => {
    try {
        const res = await axios.get('/api/v1/departments');
        departments.value = res.data;
    } catch (e) {
        console.error(e);
    }
};

const saveDept = async () => {
    submitting.value = true;
    try {
        if (isEdit.value) {
            await axios.put(`/api/v1/departments/${editId.value}`, form.value);
        } else {
            await axios.post('/api/v1/departments/create', form.value);
        }
        closeModal();
        fetchDepts();
    } catch (e) {
        alert(e.response?.data?.error || 'Error saving department');
    } finally {
        submitting.value = false;
    }
};

const deleteDept = async (id) => {
    if (!confirm(t('common.confirm_delete'))) return;
    try {
        await axios.delete(`/api/v1/departments/${id}`);
        fetchDepts();
    } catch (e) {
        alert(e.response?.data?.error || 'Error deleting');
    }
};

onMounted(() => {
    fetchDepts();
});
</script>
