<template>
  <div
    v-if="!props.isLoading && props.products.length > 0"
    class="bg-white rounded-lg shadow p-6 mb-8"
  >
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <div class="p-3 bg-red-100 rounded-lg">
          <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
            <path
              d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"
            />
          </svg>
        </div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Alertas de Estoque Baixo</h2>
          <p class="text-sm text-gray-600 mt-1">
            {{ props.countCritical }} crítico(s) · {{ props.countLow }} baixo(s) ·
            {{ props.products.length }} total(is)
          </p>
        </div>
      </div>
      <router-link
        to="/admin/products"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium"
      >
        Ver Todos os Produtos
      </router-link>
    </div>

    <!-- Cards de resumo -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <!-- Crítico -->
      <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-red-900">Estoque Crítico</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ props.countCritical }}</p>
            <p class="text-xs text-red-600 mt-2">Sem estoque disponível</p>
          </div>
          <svg class="w-12 h-12 text-red-200" fill="currentColor" viewBox="0 0 24 24">
            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z" />
          </svg>
        </div>
      </div>

      <!-- Baixo -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-yellow-900">Estoque Baixo</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ props.countLow }}</p>
            <p class="text-xs text-yellow-600 mt-2">Próximo do mínimo</p>
          </div>
          <svg class="w-12 h-12 text-yellow-200" fill="currentColor" viewBox="0 0 24 24">
            <path
              d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"
            />
          </svg>
        </div>
      </div>
    </div>

    <!-- Tabela de produtos -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b border-gray-200">
            <th class="text-left py-3 px-4 font-semibold text-gray-700">Produto</th>
            <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
            <th class="text-center py-3 px-4 font-semibold text-gray-700">Quantidade Atual</th>
            <!-- Vamos descartar por enquanto essas colunas, pois não são necessárias para o alerta de estoque baixo
            <th class="text-center py-3 px-4 font-semibold text-gray-700">Mínimo</th>
            <th class="text-center py-3 px-4 font-semibold text-gray-700">%</th>
                        -->
            <th class="text-right py-3 px-4 font-semibold text-gray-700">Preço</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="product in props.products"
            :key="product.id"
            class="border-b border-gray-100 hover:bg-gray-50"
          >
            <td class="py-3 px-4">
              <div>
                <p class="font-medium text-gray-900">{{ product.name }}</p>
                <p class="text-xs text-gray-500 mt-1">ID: #{{ product.id }}</p>
              </div>
            </td>
            <td class="py-3 px-4">
              <span
                v-if="isStockCritical(product)"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
              >
                <span class="w-2 h-2 bg-red-600 rounded-full mr-1"></span>
                Crítico
              </span>
              <span
                v-else
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
              >
                <span class="w-2 h-2 bg-yellow-600 rounded-full mr-1"></span>
                Baixo
              </span>
            </td>
            <td class="py-3 px-4 text-center">
              <span class="font-semibold text-gray-900">{{ product.quantity }}</span>
            </td>
            <!--
            <td class="py-3 px-4 text-center">
              <span class="text-gray-600">{{ product.min_quantity }}</span>
            </td>
            <td class="py-3 px-4">
              <div class="flex items-center gap-2">
                <div class="w-full bg-gray-200 rounded-full h-2 max-w-xs">
                  <div
                    :class="[
                      'h-2 rounded-full transition-all',
                      clampedStockPercentage(product) <= 0
                        ? 'bg-red-600'
                        : clampedStockPercentage(product) <= 50
                          ? 'bg-yellow-500'
                          : 'bg-green-500',
                    ]"
                    :style="{ width: `${clampedStockPercentage(product)}%` }"
                  ></div>
                </div>
                <span class="text-xs font-medium text-gray-600 min-w-8"
                  >{{ clampedStockPercentage(product) }}%</span
                >
              </div>
            </td>
            -->
            <td class="py-3 px-4 text-right">
              <span class="font-medium text-gray-900">R$ {{ formatPrice(product.price) }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <div v-if="props.pagination.last_page > 1" class="mt-6 flex items-center justify-between">
      <p class="text-sm text-gray-600">
        Mostrando {{ (props.pagination.current_page - 1) * props.pagination.per_page + 1 }} a
        {{
          Math.min(
            props.pagination.current_page * props.pagination.per_page,
            props.pagination.total
          )
        }}
        de
        {{ props.pagination.total }}
      </p>
      <div class="flex gap-2">
        <button
          @click="$emit('previousPage')"
          :disabled="props.pagination.current_page === 1"
          class="px-3 py-1 rounded border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Anterior
        </button>
        <button
          @click="$emit('nextPage')"
          :disabled="props.pagination.current_page === props.pagination.last_page"
          class="px-3 py-1 rounded border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Próximo
        </button>
      </div>
    </div>
  </div>

  <!-- Estado de carregamento -->
  <div v-if="props.isLoading" class="bg-white rounded-lg shadow p-6 mb-8 animate-pulse">
    <div class="h-8 bg-gray-200 rounded w-1/3 mb-4"></div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <div class="h-24 bg-gray-200 rounded"></div>
      <div class="h-24 bg-gray-200 rounded"></div>
    </div>
    <div class="space-y-2">
      <div class="h-4 bg-gray-200 rounded"></div>
      <div class="h-4 bg-gray-200 rounded"></div>
      <div class="h-4 bg-gray-200 rounded"></div>
    </div>
  </div>

  <!-- Mensagem quando não há produtos com estoque baixo -->
  <div
    v-if="!props.isLoading && props.products.length === 0"
    class="bg-white rounded-lg shadow p-6 mb-8"
  >
    <div class="text-center py-12">
      <svg class="w-16 h-16 text-green-200 mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24">
        <path
          d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
        />
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Tudo sob controle!</h3>
      <p class="text-gray-600">Nenhum produto com estoque abaixo do mínimo no momento.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Product } from '@/composables/useLowStockProducts';

interface Props {
  products: Product[];
  isLoading: boolean;
  pagination: {
    current_page: number;
    total: number;
    per_page: number;
    last_page: number;
  };
  countCritical: number;
  countLow: number;
}

const props = defineProps<Props>();

defineEmits<{
  previousPage: [];
  nextPage: [];
}>();
/*
const stockPercentage = (product: Product): number => {
  if (product.min_quantity === 0) return 0;
  return Math.round((product.quantity / product.min_quantity) * 100);
};

 const clampedStockPercentage = (product: Product): number => {
   return Math.min(Math.max(stockPercentage(product), 0), 100);
};
*/
const isStockCritical = (product: Product): boolean => {
  return product.quantity <= 0;
};

const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(price);
};
</script>
