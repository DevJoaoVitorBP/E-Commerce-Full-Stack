<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <nav class="flex items-center gap-2 text-sm">
          <router-link to="/" class="text-gray-400 hover:text-gray-600 transition"
            >Início</router-link
          >
          <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5l7 7-7 7"
            />
          </svg>
          <router-link to="/products" class="text-gray-400 hover:text-gray-600 transition"
            >Produtos</router-link
          >
          <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5l7 7-7 7"
            />
          </svg>
          <span class="text-gray-800 font-medium truncate max-w-xs">{{
            product?.name ?? '...'
          }}</span>
        </nav>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <!-- Estado de Carregamento -->
      <div
        v-if="productsStore.isLoading"
        class="grid grid-cols-1 md:grid-cols-2 gap-10 animate-pulse"
      >
        <div class="bg-gray-200 rounded-3xl h-[500px]"></div>
        <div class="space-y-4 pt-4">
          <div class="h-8 bg-gray-200 rounded-xl w-3/4"></div>
          <div class="h-12 bg-gray-200 rounded-xl w-1/2"></div>
          <div class="h-4 bg-gray-200 rounded w-full"></div>
          <div class="h-4 bg-gray-200 rounded w-5/6"></div>
          <div class="h-4 bg-gray-200 rounded w-4/6"></div>
        </div>
      </div>

      <!-- Estado de Erro -->
      <div
        v-else-if="productsStore.error"
        class="bg-red-50 border border-red-200 rounded-2xl p-8 text-center"
      >
        <svg
          class="w-12 h-12 text-red-400 mx-auto mb-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <p class="text-red-600 font-medium">{{ productsStore.error }}</p>
      </div>

      <!-- Detalhes do Produto -->
      <div v-else-if="product" class="grid grid-cols-1 md:grid-cols-2 gap-12">
        <!-- Imagem do Produto -->
        <div class="relative">
          <div
            class="bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 aspect-square flex items-center justify-center"
          >
            <img
              v-if="product.image_url"
              :src="product.image_url"
              :alt="product.name"
              class="w-full h-full object-cover"
            />
            <div v-else class="flex flex-col items-center gap-3 text-gray-300">
              <svg class="w-28 h-28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                />
              </svg>
              <span class="text-sm">Sem imagem</span>
            </div>
          </div>
        </div>

        <!-- Informações do Produto -->
        <div class="flex flex-col py-2">
          <!-- Categoria -->
          <div v-if="product.category" class="mb-4">
            <router-link
              :to="`/products?category_id=${product.category.id}`"
              class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-sm font-semibold px-3 py-1.5 rounded-full hover:bg-blue-100 transition"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                />
              </svg>
              {{ product.category.name }}
            </router-link>
          </div>

          <h1 class="text-2xl md:text-4xl font-extrabold text-gray-900 mb-4 leading-tight">
            {{ product.name }}
          </h1>

          <!-- Preço -->
          <div class="mb-6">
            <p class="text-3xl md:text-5xl font-extrabold text-blue-600">
              R$ {{ formatPrice(product.price) }}
            </p>
          </div>

          <!-- Status do Estoque -->
          <div class="mb-6">
            <div
              v-if="product.quantity > 0"
              class="inline-flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2.5 rounded-xl font-semibold text-sm"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                  clip-rule="evenodd"
                />
              </svg>
              Em Estoque — {{ product.quantity }} unidades disponíveis
            </div>
            <div
              v-else
              class="inline-flex items-center gap-2 bg-red-50 text-red-700 px-4 py-2.5 rounded-xl font-semibold text-sm"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                  clip-rule="evenodd"
                />
              </svg>
              Fora de Estoque
            </div>
          </div>

          <!-- Divisor -->
          <hr class="border-gray-100 mb-6" />

          <!-- Descrição -->
          <div class="mb-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">
              Descrição
            </h2>
            <p class="text-gray-600 leading-relaxed">{{ product.description }}</p>
          </div>

          <!-- Tags -->
          <div v-if="product.tags && product.tags.length > 0" class="mb-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Tags</h2>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="tag in product.tags"
                :key="tag.id"
                class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-medium"
              >
                #{{ tag.name }}
              </span>
            </div>
          </div>

          <!-- Formulário Adicionar ao Carrinho -->
          <div v-if="product.quantity > 0" class="mt-auto pt-4">
            <div class="flex gap-3">
              <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
                <button
                  @click="quantity > 1 && quantity--"
                  class="px-4 py-3.5 text-gray-500 hover:bg-gray-50 transition font-bold text-lg"
                >
                  −
                </button>
                <input
                  v-model.number="quantity"
                  type="number"
                  min="1"
                  :max="product.quantity"
                  class="w-14 text-center py-3.5 border-0 focus:outline-none font-semibold text-gray-800"
                />
                <button
                  @click="quantity < product.quantity && quantity++"
                  class="px-4 py-3.5 text-gray-500 hover:bg-gray-50 transition font-bold text-lg"
                >
                  +
                </button>
              </div>
              <button
                @click="addToCart"
                :disabled="isAddingToCart"
                class="flex-1 flex items-center justify-center gap-2 px-6 py-3.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 disabled:bg-gray-300 transition text-base shadow-lg shadow-blue-200"
              >
                <svg
                  v-if="!isAddingToCart"
                  class="w-5 h-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                  />
                </svg>
                <svg
                  v-else
                  class="w-5 h-5 animate-spin"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                  />
                </svg>
                {{ isAddingToCart ? 'Adicionando...' : 'Adicionar ao Carrinho' }}
              </button>
            </div>
          </div>
          <div v-else class="mt-auto pt-4">
            <button
              disabled
              class="w-full py-3.5 bg-gray-100 text-gray-400 font-bold rounded-xl cursor-not-allowed"
            >
              Produto Indisponível
            </button>
          </div>
        </div>
      </div>

      <!-- Não Encontrado -->
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
        <p class="text-gray-400 text-xl font-medium">Produto não encontrado</p>
        <router-link to="/products" class="mt-4 text-blue-600 hover:underline"
          >Voltar aos produtos</router-link
        >
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useProductsStore } from '@/stores/productsStore';
import { useCartStore } from '@/stores/cartStore';
import { useNotification } from '@/composables/useNotification';

const route = useRoute();
const productsStore = useProductsStore();
const cartStore = useCartStore();
const { success: showSuccess, error: showError } = useNotification();

const quantity = ref(1);
const isAddingToCart = ref(false);

const product = computed(() => productsStore.currentProduct);

const formatPrice = (price: number) => {
  return price.toFixed(2).replace('.', ',');
};

const addToCart = async () => {
  if (!product.value) return;

  isAddingToCart.value = true;
  try {
    await cartStore.addItem(product.value.id, quantity.value);
    showSuccess(`${product.value.name} adicionado ao carrinho! (${quantity.value} un.)`);
    quantity.value = 1;
    // Vou deixar comentado por enquanto, pois não quero redirecionar para o carrinho imediatamente
    // await router.push('/cart')
  } catch (error) {
    console.error('Error adding to cart:', error);
    showError('Erro ao adicionar ao carrinho. Tente novamente.');
  } finally {
    isAddingToCart.value = false;
  }
};

onMounted(async () => {
  const id = Number(route.params.id);
  if (id) {
    productsStore.currentProduct = null;
    await productsStore.fetchProductById(id);
  }
});
</script>
