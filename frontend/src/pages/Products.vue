<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900">Produtos</h1>
        <p class="text-gray-500 mt-1">
          {{ productsStore.pagination?.total ?? 0 }} produtos encontrados
        </p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex gap-8">
        <!-- Filtros (Sidebar) -->
        <aside class="hidden lg:block w-64 flex-shrink-0">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="font-bold text-gray-900">Filtros</h2>
              <button @click="clearFilters" class="text-xs text-blue-600 hover:underline">
                Limpar
              </button>
            </div>

            <!-- Busca -->
            <div class="mb-5">
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2"
                >Buscar</label
              >
              <div class="relative">
                <svg
                  class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
                </svg>
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Nome do produto..."
                  class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>

            <!-- Categoria -->
            <div class="mb-5">
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2"
                >Categoria</label
              >
              <select
                v-model="filters.category_id"
                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
              >
                <option value="">Todas as categorias</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <!-- Preço -->
            <div class="mb-5">
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2"
                >Faixa de Preço</label
              >
              <div class="flex gap-2">
                <input
                  v-model.number="filters.min_price"
                  type="number"
                  placeholder="Mín"
                  class="w-1/2 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <input
                  v-model.number="filters.max_price"
                  type="number"
                  placeholder="Máx"
                  class="w-1/2 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <button
              @click="applyFilters"
              class="w-full py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition text-sm"
            >
              Aplicar Filtros
            </button>
          </div>
        </aside>

        <!-- Produtos -->
        <main class="flex-1 min-w-0">
          <!-- Esqueleto -->
          <div
            v-if="productsStore.isLoading"
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5"
          >
            <div
              v-for="i in 9"
              :key="i"
              class="bg-white rounded-2xl overflow-hidden animate-pulse shadow-sm"
            >
              <div class="h-52 bg-gray-200"></div>
              <div class="p-4">
                <div class="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded mb-4"></div>
                <div class="h-6 bg-gray-200 rounded w-1/3"></div>
              </div>
            </div>
          </div>

          <div
            v-else-if="productsStore.products.length > 0"
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5"
          >
            <router-link
              v-for="product in productsStore.products"
              :key="product.id"
              :to="`/products/${product.id}`"
              class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100"
            >
              <div class="relative h-52 bg-gray-100 overflow-hidden">
                <img
                  v-if="product.image_url"
                  :src="product.image_url"
                  :alt="product.name"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <svg
                    class="w-14 h-14 text-gray-300"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                </div>
                <div class="absolute top-3 left-3">
                  <span
                    v-if="product.quantity > 0"
                    class="bg-green-500/90 backdrop-blur-sm text-white text-xs font-semibold px-2.5 py-1 rounded-full"
                    >Em estoque</span
                  >
                  <span
                    v-else
                    class="bg-red-500/90 backdrop-blur-sm text-white text-xs font-semibold px-2.5 py-1 rounded-full"
                    >Esgotado</span
                  >
                </div>
              </div>
              <div class="p-4">
                <h3
                  class="font-bold text-gray-900 mb-1 truncate group-hover:text-blue-600 transition-colors"
                >
                  {{ product.name }}
                </h3>
                <p class="text-gray-400 text-sm mb-3 line-clamp-2 leading-relaxed">
                  {{ product.description }}
                </p>
                <div class="flex items-center justify-between">
                  <span class="text-xl font-extrabold text-blue-600"
                    >R$ {{ formatPrice(product.price) }}</span
                  >
                  <span
                    class="w-8 h-8 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                      />
                    </svg>
                  </span>
                </div>
              </div>
            </router-link>
          </div>

          <div v-else class="flex flex-col items-center justify-center py-24 text-center">
            <svg
              class="w-20 h-20 text-gray-200 mb-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <p class="text-gray-400 text-lg font-medium">Nenhum produto encontrado</p>
            <button @click="clearFilters" class="mt-4 text-blue-600 hover:underline text-sm">
              Limpar filtros
            </button>
          </div>

          <!-- Paginação -->
          <div
            v-if="productsStore.products.length > 0"
            class="flex items-center justify-center gap-3 mt-10"
          >
            <button
              :disabled="!productsStore.hasPrevPage"
              @click="previousPage"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition text-sm font-medium shadow-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 19l-7-7 7-7"
                />
              </svg>
              Anterior
            </button>
            <span class="px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold">
              {{ productsStore.pagination?.current_page ?? 1 }} /
              {{ productsStore.pagination?.last_page ?? 1 }}
            </span>
            <button
              :disabled="!productsStore.hasNextPage"
              @click="nextPage"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition text-sm font-medium shadow-sm"
            >
              Próxima
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </button>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, computed, onMounted } from 'vue';
import { useProductsStore } from '@/stores/productsStore';

const productsStore = useProductsStore();

const filters = reactive({
  search: '',
  category_id: '' as string | number,
  min_price: undefined as number | undefined,
  max_price: undefined as number | undefined,
  per_page: 12,
  page: 1,
});

const categories = computed(() => productsStore.categories);

const applyFilters = async () => {
  filters.page = 1;
  if (filters.category_id) {
    filters.category_id = Number(filters.category_id);
  }
  await productsStore.fetchProducts(filters);
};

const clearFilters = async () => {
  filters.search = '';
  filters.category_id = '';
  filters.min_price = undefined;
  filters.max_price = undefined;
  filters.page = 1;
  await productsStore.fetchProducts(filters);
};

const nextPage = async () => {
  filters.page = productsStore.pagination.current_page + 1;
  if (filters.category_id) {
    filters.category_id = Number(filters.category_id);
  }
  await productsStore.fetchProducts(filters);
};

const previousPage = async () => {
  filters.page = productsStore.pagination.current_page - 1;
  if (filters.category_id) {
    filters.category_id = Number(filters.category_id);
  }
  await productsStore.fetchProducts(filters);
};

onMounted(async () => {
  await productsStore.fetchCategories();
  if (filters.category_id) {
    filters.category_id = Number(filters.category_id);
  }
  await productsStore.fetchProducts(filters);
});

const formatPrice = (price: number) => {
  return price.toFixed(2).replace('.', ',');
};
</script>
