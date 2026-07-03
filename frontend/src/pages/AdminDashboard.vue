<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-12">
        <h1 class="text-2xl md:text-4xl font-bold text-gray-900 mb-2">Painel Administrativo</h1>
        <p class="text-gray-600">Bem-vindo de volta, Admin!</p>
      </div>

      <!-- Estado de Carregamento -->
      <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div v-for="i in 4" :key="i" class="bg-white rounded-lg shadow p-6 animate-pulse">
          <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
          <div class="h-8 bg-gray-300 rounded w-1/2"></div>
        </div>
      </div>

      <!-- Cartões de Estatísticas -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <!-- Total de Produtos -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total de Produtos</p>
              <p class="text-4xl font-bold text-blue-600 mt-2">{{ stats.totalProducts }}</p>
            </div>
            <svg class="w-16 h-16 text-blue-100" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M9 2C7.9 2 7 2.9 7 4v16c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2H9zm0 2h10v16H9V4z"
              />
            </svg>
          </div>
        </div>

        <!-- Total de Categorias -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Categorias</p>
              <p class="text-4xl font-bold text-green-600 mt-2">{{ stats.totalCategories }}</p>
            </div>
            <svg class="w-16 h-16 text-green-100" fill="currentColor" viewBox="0 0 24 24">
              <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm0 7h18v-2H3v2z" />
            </svg>
          </div>
        </div>

        <!-- Total de Pedidos -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Pedidos</p>
              <p class="text-4xl font-bold text-purple-600 mt-2">{{ stats.totalOrders }}</p>
            </div>
            <svg class="w-16 h-16 text-purple-100" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"
              />
            </svg>
          </div>
        </div>

        <!-- Receita Total -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Receita Total</p>
              <p class="text-4xl font-bold text-orange-600 mt-2">
                R$ {{ formatPrice(stats.totalRevenue) }}
              </p>
            </div>
            <svg class="w-16 h-16 text-orange-100" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"
              />
            </svg>
          </div>
        </div>
      </div>

      <!-- Alertas de Estoque Baixo -->
      <LowStockAlert
        :products="lowStockProducts"
        :is-loading="lowStockLoading"
        :pagination="lowStockPagination"
        :count-critical="countCritical"
        :count-low="countLow"
        @previous-page="handlePreviousPage"
        @next-page="handleNextPage"
      />

      <!-- Seções de Gerenciamento -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Links Rápidos -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Gerenciamento</h2>
          <div class="space-y-3">
            <router-link
              to="/admin/products"
              class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-300 transition"
            >
              <svg class="w-6 h-6 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
              </svg>
              <div>
                <p class="font-semibold text-gray-900">Produtos</p>
                <p class="text-sm text-gray-600">Adicionar, editar, excluir</p>
              </div>
            </router-link>

            <router-link
              to="/admin/categories"
              class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-green-50 hover:border-green-300 transition"
            >
              <svg class="w-6 h-6 text-green-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm0 7h18v-2H3v2z" />
              </svg>
              <div>
                <p class="font-semibold text-gray-900">Categorias</p>
                <p class="text-sm text-gray-600">Organizar categorias</p>
              </div>
            </router-link>

            <router-link
              to="/admin/orders"
              class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-purple-50 hover:border-purple-300 transition"
            >
              <svg class="w-6 h-6 text-purple-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"
                />
              </svg>
              <div>
                <p class="font-semibold text-gray-900">Pedidos</p>
                <p class="text-sm text-gray-600">Gerenciar pedidos</p>
              </div>
            </router-link>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Informações Rápidas</h2>
          <div class="space-y-4">
            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
              <p class="text-sm text-blue-900">📊 Sistema de Admin Completo</p>
              <p class="text-xs text-blue-600 mt-1">
                Acesso total a produtos, categorias e pedidos
              </p>
            </div>
            <div class="p-4 bg-green-50 rounded-lg border border-green-200">
              <p class="text-sm text-green-900">✅ Validação de Dados</p>
              <p class="text-xs text-green-600 mt-1">
                Todos os formulários incluem validação completa
              </p>
            </div>
            <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
              <p class="text-sm text-purple-900">🔐 Acesso Restrito</p>
              <p class="text-xs text-purple-600 mt-1">Apenas admins podem acessar esta seção</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useProductsQuery } from '@/composables/useProductsQuery';
import { useCategoriesQuery } from '@/composables/useCategoriesQuery';
import { useOrdersQuery } from '@/composables/useOrdersQuery';
import { useLowStockProducts } from '@/composables/useLowStockProducts';
import LowStockAlert from '@/components/common/LowStockAlert.vue';

const currentLowStockPage = ref(1);
const lowStockPerPage = ref(10);

const { pagination: productsPagination, isLoading: productsLoading } = useProductsQuery();
const { categories, isLoading: categoriesLoading } = useCategoriesQuery();
const { orders, isLoading: ordersLoading } = useOrdersQuery();
const {
  products: lowStockProductsData,
  isLoading: lowStockLoading,
  pagination: lowStockPagination,
  isStockCritical,
  isStockLow,
} = useLowStockProducts(currentLowStockPage, lowStockPerPage);

const isLoading = computed(
  () => productsLoading.value || categoriesLoading.value || ordersLoading.value
);

const stats = computed(() => ({
  totalProducts: productsPagination.value.total,
  totalCategories: categories.value.length,
  totalOrders: orders.value.length,
  totalRevenue: orders.value.reduce((sum, order) => sum + (order.total ?? 0), 0),
}));

const lowStockProducts = computed(() => lowStockProductsData.value);

const countCritical = computed(() => lowStockProductsData.value.filter(isStockCritical).length);

const countLow = computed(() => lowStockProductsData.value.filter(isStockLow).length);

const formatPrice = (price: number) => price.toFixed(2).replace('.', ',');

const handlePreviousPage = () => {
  if (currentLowStockPage.value > 1) {
    currentLowStockPage.value--;
  }
};

const handleNextPage = () => {
  if (currentLowStockPage.value < lowStockPagination.value.last_page) {
    currentLowStockPage.value++;
  }
};
</script>
