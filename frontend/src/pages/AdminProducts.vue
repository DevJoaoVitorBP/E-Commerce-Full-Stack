<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-3">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-4xl font-bold text-gray-900">Gerenciar Produtos</h1>
          <p class="text-gray-600 mt-1">
            Total: {{ productsStore.pagination.total }} produtos
          </p>
        </div>
        <div class="flex gap-3">
          <button
            @click="openAddModal"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
          >
            ➕ Novo Produto
          </button>
          <router-link
            to="/admin"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
          >
            ← Voltar
          </router-link>
        </div>
      </div>
 
      <!-- Search & Filter -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex gap-4">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar produtos..."
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            @keyup.enter="handleSearch"
          />
          <button
            @click="handleSearch"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            🔍 Buscar
          </button>
        </div>
      </div>
 
      <!-- Loading State -->
      <div v-if="productsStore.isLoading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600"></div>
      </div>
 
      <!-- Products Table -->
      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Produto</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Categoria</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Preço</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Estoque</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="product in productsStore.products"
              :key="product.id"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <p class="font-semibold text-gray-900">{{ product.name }}</p>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <p class="text-gray-600">{{ product.category?.name || '-' }}</p>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <p class="font-semibold text-blue-600">R$ {{ formatPrice(product.price) }}</p>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="{
                    'bg-green-100 text-green-800': product.quantity > 10,
                    'bg-yellow-100 text-yellow-800': product.quantity <= 10 && product.quantity > 0,
                    'bg-red-100 text-red-800': product.quantity === 0,
                  }"
                  class="px-3 py-1 rounded-full text-sm font-semibold inline-block"
                >
                  {{ product.quantity }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="product.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  class="px-3 py-1 rounded-full text-sm font-semibold inline-block"
                >
                  {{ product.active ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex gap-2">
                  <button
                    @click="openEditModal(product)"
                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                  >
                    Editar
                  </button>
                  <button
                    @click="deleteProduct(product.id)"
                    class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                  >
                    Deletar
                  </button>
                </div>
              </td>
            </tr>
 
            <!-- Empty state -->
            <tr v-if="productsStore.products.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                Nenhum produto encontrado.
              </td>
            </tr>
          </tbody>
        </table>
 
        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
          <div class="text-sm text-gray-600">
            Exibindo
            <span class="font-semibold">{{ rangeStart }}</span>
            –
            <span class="font-semibold">{{ rangeEnd }}</span>
            de
            <span class="font-semibold">{{ productsStore.pagination.total }}</span>
            produtos
          </div>
 
          <div class="flex items-center gap-2">
            <!-- Primeira página -->
            <button
              @click="goToPage(1)"
              :disabled="!productsStore.hasPrevPage"
              class="px-3 py-1.5 rounded text-sm font-medium border border-gray-300 bg-white hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
              title="Primeira página"
            >
              «
            </button>
 
            <!-- Página anterior -->
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="!productsStore.hasPrevPage"
              class="px-3 py-1.5 rounded text-sm font-medium border border-gray-300 bg-white hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
            >
              ← Anterior
            </button>
 
            <!-- Números de página -->
            <div class="flex gap-1">
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="goToPage(page)"
                :class="[
                  'px-3 py-1.5 rounded text-sm font-medium border transition',
                  page === currentPage
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100',
                ]"
              >
                {{ page }}
              </button>
            </div>
 
            <!-- Próxima página -->
            <button
              @click="goToPage(currentPage + 1)"
              :disabled="!productsStore.hasNextPage"
              class="px-3 py-1.5 rounded text-sm font-medium border border-gray-300 bg-white hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
            >
              Próxima →
            </button>
 
            <!-- Última página -->
            <button
              @click="goToPage(productsStore.pagination.last_page)"
              :disabled="!productsStore.hasNextPage"
              class="px-3 py-1.5 rounded text-sm font-medium border border-gray-300 bg-white hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
              title="Última página"
            >
              »
            </button>
          </div>
 
          <!-- Itens por página -->
          <div class="flex items-center gap-2 text-sm text-gray-600">
            <span>Itens por página:</span>
            <select
              v-model="perPage"
              @change="handlePerPageChange"
              class="px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option :value="10">10</option>
              <option :value="15">15</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
          </div>
        </div>
      </div>
    </div>
 
    <!-- Modals -->
    <EditProductModal
      :is-open="isEditOpen"
      :product="selectedProduct"
      @close="closeEditModal"
      @success="loadProducts"
    />
    <AddProductModal
      :is-open="isAddOpen"
      @close="closeAddModal"
      @success="loadProducts"
    />
  </div>
</template>
 
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useProductsStore } from '@/stores/productsStore';
import EditProductModal from '@/components/modals/EditProductModal.vue';
import AddProductModal from '@/components/modals/AddProductModal.vue';
import type { Product } from '@/types';
 
const productsStore = useProductsStore();
 
const searchQuery = ref('');
const isEditOpen = ref(false);
const isAddOpen = ref(false);
const selectedProduct = ref<Product | null>(null);
const currentPage = ref(1);
const perPage = ref(15);
 
// ─── Helpers ────────────────────────────────────────────────────────────────
 
const formatPrice = (price: number) => price.toFixed(2).replace('.', ',');
 
// ─── Paginação ───────────────────────────────────────────────────────────────
 
const rangeStart = computed(() => {
  const { current_page, per_page, total } = productsStore.pagination;
  if (total === 0) return 0;
  return (current_page - 1) * per_page + 1;
});
 
const rangeEnd = computed(() => {
  const { current_page, per_page, total } = productsStore.pagination;
  return Math.min(current_page * per_page, total);
});
 
/** Exibe no máximo 5 páginas centradas na página atual */
const visiblePages = computed(() => {
  const total = productsStore.pagination.last_page;
  const current = currentPage.value;
  const delta = 2;
 
  const start = Math.max(1, current - delta);
  const end = Math.min(total, current + delta);
 
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});
 
// ─── Ações ───────────────────────────────────────────────────────────────────
 
const loadProducts = async () => {
  await productsStore.fetchProducts({
    page: currentPage.value,
    per_page: perPage.value,
    search: searchQuery.value || undefined,
  });
};
 
const goToPage = async (page: number) => {
  if (page < 1 || page > productsStore.pagination.last_page) return;
  currentPage.value = page;
  await loadProducts();
};
 
const handleSearch = async () => {
  currentPage.value = 1; // volta pra primeira página ao buscar
  await loadProducts();
};
 
const handlePerPageChange = async () => {
  currentPage.value = 1;
  await loadProducts();
};
 
const openEditModal = (product: Product) => {
  selectedProduct.value = product;
  isEditOpen.value = true;
};
 
const closeEditModal = () => {
  isEditOpen.value = false;
  selectedProduct.value = null;
};
 
const openAddModal = () => (isAddOpen.value = true);
const closeAddModal = () => (isAddOpen.value = false);
 
const deleteProduct = async (productId: number) => {
  if (!confirm('Tem certeza que deseja deletar este produto?')) return;
  try {
    await productsStore.deleteProduct(productId);
    alert('Produto deletado com sucesso!');
    // Se deletou o último item da página, volta uma página
    if (productsStore.products.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
    await loadProducts();
  } catch {
    alert(`Erro ao deletar: ${productsStore.error}`);
  }
};
 
// ─── Init ────────────────────────────────────────────────────────────────────
 
onMounted(() => loadProducts());
</script>