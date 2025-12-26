<template>
  <div class="min-h-screen bg-gray-100 font-sans text-gray-900 flex">
    
    <!-- Sidebar (Only if logged in) -->
    <Sidebar v-if="authStore.isAuthenticated" @logout="handleLogout" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col relative transition-all duration-300" :class="{'ml-0': !authStore.isAuthenticated}">
      
      <!-- Top Bar (Mobile Menu Trigger could go here) -->
      
      <!-- Router View Simulation -->
      <div class="flex-1 overflow-y-auto">
         <!-- If not logged in, show Login -->
         <Login v-if="!authStore.isAuthenticated" />

         <!-- If logged in, show content based on hash -->
         <div v-else class="container mx-auto">
            <template v-if="currentHash === '#/dashboard'">
               <DashboardCEO v-if="authStore.role === 'ROLE_CEO'" />
               <DashboardCommon v-else />
            </template>

            <template v-if="currentHash === '#/absence/create'">
               <AbsenceRequestForm />
            </template>

            <template v-if="currentHash === '#/purchase/create'">
               <PurchaseRequestForm />
            </template>

            <template v-if="currentHash === '#/ceo/dashboard'">
               <DashboardCEO />
            </template>

            <!-- Add other views here -->
         </div>
      </div>

    </main>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from './stores/auth';
import Login from './components/Login.vue';
import Sidebar from './components/Sidebar.vue';
import AbsenceRequestForm from './components/AbsenceRequestForm.vue';
import PurchaseRequestForm from './components/PurchaseRequestForm.vue';
import DashboardCommon from './components/DashboardCommon.vue';

const authStore = useAuthStore();
const currentHash = ref(window.location.hash || '#/');

// Listen to hash changes for simple routing
onMounted(() => {
  window.addEventListener('hashchange', () => {
    currentHash.value = window.location.hash;
  });
  
  // Check auth persistence
  const savedUser = localStorage.getItem('user_data');
  const savedRole = localStorage.getItem('user_role');
  if (savedUser && savedRole) {
      authStore.user = JSON.parse(savedUser);
      authStore.role = savedRole;
      authStore.isAuthenticated = true;
  }
});

const handleLogout = () => {
  authStore.logout();
  localStorage.removeItem('user_data');
  localStorage.removeItem('user_role');
  window.location.hash = '#/';
};
</script>
