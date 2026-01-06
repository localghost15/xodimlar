<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">{{ $t('users.title') }}</h1>
      <button @click="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition shadow-sm flex items-center">
        <i class="fas fa-user-plus mr-2"></i> {{ $t('users.add_btn') }}
      </button>
    </div>

    <!-- USERS TABLE -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-500 font-medium border-b">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">{{ $t('users.table_name') }}</th>
                    <th class="px-6 py-4">{{ $t('users.table_role') }}</th>
                    <th class="px-6 py-4">{{ $t('users.table_dept') }}</th>
                    <th class="px-6 py-4">{{ $t('users.table_manager') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr v-if="users.length === 0">
                    <td colspan="5" class="px-6 py-10 text-center text-gray-400">{{ $t('users.no_data') }}</td>
                </tr>
                <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-500">#{{ user.id }}</td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-gray-800">{{ user.full_name }}</p>
                        <p class="text-xs text-gray-500">{{ user.phone }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded text-xs font-bold" 
                            :class="{
                                'bg-purple-100 text-purple-700': user.role === 'ROLE_CEO',
                                'bg-blue-100 text-blue-700': user.role === 'ROLE_HR',
                                'bg-indigo-100 text-indigo-700': user.role === 'ROLE_DEPT_HEAD',
                                'bg-green-100 text-green-700': user.role === 'ROLE_ACCOUNTANT',
                                'bg-gray-100 text-gray-600': user.role === 'ROLE_EMPLOYEE'
                            }">
                            {{ user.role }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ user.department }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ user.manager }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- MODAL -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 relative">
            <button @click="closeModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
            <h2 class="text-xl font-bold mb-4">{{ $t('users.modal_title') }}</h2>

            <form @submit.prevent="createUser" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('users.field_name') }}</label>
                    <input v-model="form.full_name" type="text" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('users.field_phone') }}</label>
                    <input v-model="form.phone" type="text" required placeholder="998901234567" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('users.field_role') }}</label>
                    <VueMultiselect 
                        v-model="selectedRole" 
                        :options="rolesWithOptions"
                        label="label"
                        track-by="value"
                        placeholder="Select Role"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('users.field_dept') }}</label>
                    <VueMultiselect
                        v-model="selectedDept"
                        :options="departments"
                        label="name"
                        track-by="id"
                        placeholder="Search Department"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('users.field_manager') }}</label>
                     <VueMultiselect
                        v-model="selectedManager"
                        :options="heads"
                        label="full_name"
                        track-by="id"
                        placeholder="Search Manager"
                     >
                        <template #singleLabel="{ option }">
                            <strong>{{ option.full_name }}</strong> <span class="text-gray-500 text-xs">({{ option.department }})</span>
                        </template>
                        <template #option="{ option }">
                             <div class="flex justify-between">
                                <span>{{ option.full_name }}</span>
                                <span class="text-gray-400 text-xs">{{ option.department }}</span>
                             </div>
                        </template>
                     </VueMultiselect>
                    <p class="text-xs text-gray-500 mt-1">{{ $t('users.manager_hint') }}</p>
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
import VueMultiselect from 'vue-multiselect';

const { t } = useI18n();

const users = ref([]);
const heads = ref([]);
const departments = ref([]);
const showModal = ref(false);
const submitting = ref(false);

const rolesWithOptions = [
    { value: 'ROLE_EMPLOYEE', label: 'Employee' },
    { value: 'ROLE_DEPT_HEAD', label: 'Department Head' },
    { value: 'ROLE_HR', label: 'HR Manager' },
    { value: 'ROLE_ACCOUNTANT', label: 'Accountant' }
];

const selectedRole = ref(rolesWithOptions[0]);
const selectedDept = ref(null);
const selectedManager = ref(null);

const form = ref({
    full_name: '',
    phone: '',
    role: 'ROLE_EMPLOYEE',
    department_id: null,
    manager_id: null
});

const openModal = () => {
    form.value = {
        full_name: '',
        phone: '',
        role: 'ROLE_EMPLOYEE',
        department_id: null,
        manager_id: null
    };
    selectedRole.value = rolesWithOptions[0];
    selectedDept.value = null;
    selectedManager.value = null;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const fetchData = async () => {
    try {
        const [uRes, hRes, dRes] = await Promise.all([
            axios.get('/api/v1/users'),
            axios.get('/api/v1/users/heads'),
            axios.get('/api/v1/users/departments')
        ]);
        users.value = uRes.data;
        heads.value = hRes.data;
        departments.value = dRes.data;
    } catch (e) {
        console.error("Error loading users", e);
    }
};

const createUser = async () => {
    // Map selected objects to values
    form.value.role = selectedRole.value ? selectedRole.value.value : 'ROLE_EMPLOYEE';
    form.value.department_id = selectedDept.value ? selectedDept.value.id : null;
    form.value.manager_id = selectedManager.value ? selectedManager.value.id : null;

    submitting.value = true;
    try {
        await axios.post('/api/v1/users/create', form.value);
        closeModal();
        fetchData();
        alert(t('users.created_success'));
    } catch (e) {
        alert(e.response?.data?.error || 'Error creating user');
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    fetchData();
});
</script>
