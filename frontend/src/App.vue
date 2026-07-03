<template>
  <div id="app" class="flex flex-col min-h-screen">
    <Header />

    <main class="flex-1">
      <router-view />
    </main>

    <Footer />

    <Toast />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import Header from '@/components/common/Header.vue';
import Footer from '@/components/common/Footer.vue';
import Toast from '@/components/common/Toast.vue';

const authStore = useAuthStore();

onMounted(async () => {
  await authStore.initializeAuth();
});
</script>

<style scoped></style>
