<template>
  <div class="min-h-screen bg-white">
    <!-- Hero -->
    <section
      class="relative bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700 text-white overflow-hidden"
    >
      <div class="absolute inset-0 opacity-10">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white rounded-full"></div>
        <div class="absolute -bottom-16 -left-16 w-72 h-72 bg-white rounded-full"></div>
      </div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="max-w-2xl">
          <span
            class="inline-block bg-white/20 text-white text-sm font-medium px-4 py-1.5 rounded-full mb-6"
          >
            🛍️ Loja Online
          </span>
          <h1 class="text-5xl md:text-6xl font-extrabold mb-6 leading-tight">
            Os melhores produtos,<br /><span class="text-blue-200">os melhores preços</span>
          </h1>
          <p class="text-xl text-blue-100 mb-10">
            Qualidade e variedade em um só lugar. Encontre tudo que você precisa.
          </p>
          <div class="flex gap-4 flex-wrap">
            <router-link
              to="/products"
              class="inline-flex items-center gap-2 px-8 py-3.5 bg-white text-blue-700 font-bold rounded-xl hover:bg-blue-50 transition shadow-lg"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                />
              </svg>
              Ver Produtos
            </router-link>
            <router-link
              v-if="!authStore.isAuthenticated"
              to="/register"
              class="inline-flex items-center gap-2 px-8 py-3.5 bg-transparent border-2 border-white text-white font-bold rounded-xl hover:bg-white/10 transition"
            >
              Criar Conta
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats bar -->
    <section class="bg-gray-900 text-white py-5">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-3 gap-4 text-center">
          <div>
            <p class="text-2xl font-bold text-blue-400">{{ products.length }}+</p>
            <p class="text-gray-400 text-sm">Produtos</p>
          </div>
          <div>
            <p class="text-2xl font-bold text-blue-400">100%</p>
            <p class="text-gray-400 text-sm">Satisfação</p>
          </div>
          <div>
            <p class="text-2xl font-bold text-blue-400">24h</p>
            <p class="text-gray-400 text-sm">Entrega</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Produtos em Destaque -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-10">
          <div>
            <p class="text-blue-600 font-semibold text-sm uppercase tracking-wide mb-2">
              Destaques
            </p>
            <h2 class="text-3xl font-bold text-gray-900">Produtos em Destaque</h2>
          </div>
          <router-link
            to="/products"
            class="text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1 text-sm"
          >
            Ver todos
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
              />
            </svg>
          </router-link>
        </div>

        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="i in 8"
            :key="i"
            class="bg-white rounded-2xl shadow-sm overflow-hidden animate-pulse"
          >
            <div class="h-52 bg-gray-200"></div>
            <div class="p-4">
              <div class="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
              <div class="h-3 bg-gray-200 rounded mb-4 w-full"></div>
              <div class="h-6 bg-gray-200 rounded w-1/2"></div>
            </div>
          </div>
        </div>

        <div
          v-else-if="products.length > 0"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"
        >
          <router-link
            v-for="product in products"
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
                  class="w-16 h-16 text-gray-300"
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
              <div class="absolute top-3 right-3">
                <span
                  v-if="product.quantity > 0"
                  class="bg-green-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full"
                  >Em estoque</span
                >
                <span
                  v-else
                  class="bg-red-500 text-white text-xs font-semibold px-2.5 py-1 rounded-full"
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
              <p class="text-gray-500 text-sm mb-3 line-clamp-2">{{ product.description }}</p>
              <div class="flex items-center justify-between">
                <span class="text-xl font-extrabold text-blue-600"
                  >R$ {{ formatPrice(product.price) }}</span
                >
                <span class="text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <div v-else class="text-center py-16">
          <svg
            class="w-16 h-16 text-gray-300 mx-auto mb-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
            />
          </svg>
          <p class="text-gray-400 text-lg">Nenhum produto encontrado</p>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-gradient-to-r from-blue-600 to-indigo-600 py-20">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Pronto para começar?</h2>
        <p class="text-blue-100 mb-8 text-lg">
          Crie uma conta gratuita e aproveite todos os benefícios
        </p>
        <router-link
          v-if="!authStore.isAuthenticated"
          to="/register"
          class="inline-flex items-center gap-2 px-10 py-4 bg-white text-blue-700 font-bold rounded-xl hover:bg-blue-50 transition shadow-lg text-lg"
        >
          Criar Conta Grátis
        </router-link>
        <router-link
          v-else
          to="/products"
          class="inline-flex items-center gap-2 px-10 py-4 bg-white text-blue-700 font-bold rounded-xl hover:bg-blue-50 transition shadow-lg text-lg"
        >
          Continue Comprando →
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useProductsQuery } from '@/composables/useProductsQuery';
import { useAuthStore } from '@/stores/authStore';

const { products: allProducts, isLoading } = useProductsQuery({ per_page: 8 });
const authStore = useAuthStore();

const products = computed(() => allProducts.value.slice(0, 8));

const formatPrice = (price: number) => {
  return price.toFixed(2).replace('.', ',');
};
</script>
