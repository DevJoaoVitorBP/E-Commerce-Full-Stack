<template>
  <header class="bg-white dark:bg-gray-900 shadow dark:shadow-gray-800">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <!-- Logo -->
      <router-link to="/" class="flex items-center gap-2">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold">EC</span>
        </div>
        <span class="font-bold text-lg dark:text-white">E-Commerce</span>
      </router-link>

      <!-- Menu central (desktop) -->
      <div class="hidden md:flex items-center gap-8">
        <router-link
          to="/products"
          class="text-gray-700 hover:text-blue-600 transition dark:text-white"
        >
          Produtos
        </router-link>
        <router-link
          to="/orders"
          class="text-gray-700 hover:text-blue-600 transition dark:text-white"
        >
          Meus Pedidos
        </router-link>
        <router-link
          v-if="authStore.isAdmin"
          to="/admin"
          class="text-gray-700 hover:text-blue-600 transition dark:text-white"
        >
          Admin
        </router-link>
      </div>

      <!-- Menu direito (desktop) -->
      <div class="hidden md:flex items-center gap-6">
        <!-- Cart -->
        <router-link v-if="authStore.isAuthenticated" to="/cart" class="relative">
          <svg
            class="w-6 h-6 text-gray-700 hover:text-blue-600 transition dark:text-white"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.8 5M7 13l-2.293 2.293a1 1 0 00.117 1.497A6 6 0 0012 21c1.657 0 3.157-.671 4.243-1.757a1 1 0 00.117-1.497L17 13M17 13l4-8m-4 8h2.9a2 2 0 002-2V5.9a2 2 0 00-2-2h-.9"
            />
          </svg>
          <span
            v-if="cartStore.itemCount > 0"
            class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
          >
            {{ cartStore.itemCount }}
          </span>
        </router-link>

        <!-- Menu do usuário -->
        <div v-if="authStore.isAuthenticated" class="flex items-center gap-4">
          <router-link
            to="/profile"
            class="text-sm text-gray-700 hover:text-blue-600 transition font-medium dark:text-white"
          >
            {{ authStore.user?.name }}
          </router-link>
          <button
            @click="handleLogout"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm"
          >
            Sair
          </button>
        </div>

        <!-- Links de autenticação -->
        <div v-else class="flex items-center gap-4">
          <router-link
            to="/login"
            class="text-gray-700 hover:text-blue-600 transition font-medium dark:text-white"
          >
            Login
          </router-link>
          <router-link
            to="/register"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm"
          >
            Registrar
          </router-link>
        </div>
      </div>

      <!-- Botão hamburguer (mobile) -->
      <div class="flex md:hidden items-center gap-4">
        <!-- Cart (mobile) -->
        <router-link v-if="authStore.isAuthenticated" to="/cart" class="relative">
          <svg
            class="w-6 h-6 text-gray-700 dark:text-white"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.8 5M7 13l-2.293 2.293a1 1 0 00.117 1.497A6 6 0 0012 21c1.657 0 3.157-.671 4.243-1.757a1 1 0 00.117-1.497L17 13M17 13l4-8m-4 8h2.9a2 2 0 002-2V5.9a2 2 0 00-2-2h-.9"
            />
          </svg>
          <span
            v-if="cartStore.itemCount > 0"
            class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
          >
            {{ cartStore.itemCount }}
          </span>
        </router-link>

        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="p-2 text-white hover:text-blue-600 transition"
          aria-label="Abrir menu"
        >
          <svg
            v-if="!mobileMenuOpen"
            class="w-6 h-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>
    </nav>

    <!-- Menu mobile expandido -->
    <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-100 bg-white shadow-md">
      <div class="px-4 py-4 space-y-3">
        <router-link
          to="/profile"
          class="block py-2 text-gray-700 hover:text-blue-600 transition font-medium dark:text-gray-700"
          @click="mobileMenuOpen = false"
        >
          Meu perfil
        </router-link>
        <router-link
          to="/products"
          class="block py-2 text-gray-700 hover:text-blue-600 transition font-medium dark:text-gray-700"
          @click="mobileMenuOpen = false"
        >
          Produtos
        </router-link>
        <router-link
          to="/orders"
          class="block py-2 text-gray-700 hover:text-blue-600 transition font-medium dark:text-gray-700"
          @click="mobileMenuOpen = false"
        >
          Meus Pedidos
        </router-link>
        <router-link
          v-if="authStore.isAdmin"
          to="/admin"
          class="block py-2 text-gray-700 hover:text-blue-600 transition font-medium"
          @click="mobileMenuOpen = false"
        >
          Admin
        </router-link>
        <div class="border-t border-gray-100 pt-3">
          <template v-if="authStore.isAuthenticated">
            <p class="text-sm text-gray-500 mb-2">{{ authStore.user?.name }}</p>
            <button
              @click="handleLogout"
              class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm text-left"
            >
              Sair
            </button>
          </template>
          <template v-else>
            <router-link
              to="/login"
              class="block py-2 text-gray-700 hover:text-blue-600 transition font-medium"
              @click="mobileMenuOpen = false"
            >
              Login
            </router-link>
            <router-link
              to="/register"
              class="block mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm text-center"
              @click="mobileMenuOpen = false"
            >
              Registrar
            </router-link>
          </template>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useCartStore } from '@/stores/cartStore';

const router = useRouter();
const authStore = useAuthStore();
const cartStore = useCartStore();
const mobileMenuOpen = ref(false);

const handleLogout = async () => {
  mobileMenuOpen.value = false;
  try {
    await authStore.logout();
    router.push('/');
  } catch (error) {
    console.error('Erro ao fazer logout:', error);
  }
};
</script>
