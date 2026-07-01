<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-4xl font-bold text-gray-900">Gerenciar Categorias</h1>
          <p class="text-gray-600 mt-1">Total: {{ productsStore.categories.length }} categorias</p>
        </div>

        <router-link
          to="/admin"
          class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
        >
          ← Voltar
        </router-link>
      </div>

      <!-- Add Category Form -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Nova Categoria</h2>

        <div class="flex gap-4">
          <input
            v-model="newCategory"
            type="text"
            placeholder="Nome da categoria..."
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
          />

          <button
            @click="addCategory"
            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
          >
            ➕ Adicionar
          </button>
        </div>
      </div>

      <!-- Categories Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">ID</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Nome</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">
                Produtos
              </th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Ações</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="category in productsStore.categories"
              :key="category.id"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <p class="text-gray-600">#{{ category.id }}</p>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <p class="font-semibold text-gray-900">
                  {{ category.name }}
                </p>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <p class="text-gray-600">
                  {{ getProductCount(category.id) }}
                </p>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex gap-2">
                  <button
                    @click="openEditModal(category)"
                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                  >
                    Editar
                  </button>

                  <button
                    @click="deleteCategory(category.id)"
                    class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                  >
                    Deletar
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <EditCategoryModal
      :is-open="isEditOpen"
      :category="selectedCategory"
      @close="closeEditModal"
      @success="loadCategories"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useProductsStore } from '@/stores/productsStore';
import EditCategoryModal from '@/components/modals/EditCategoryModal.vue';
import type { Category } from '@/types';

const productsStore = useProductsStore();

const newCategory = ref('');
const isEditOpen = ref(false);
const selectedCategory = ref<Category | null>(null);

/*
Para respeitar o teste técnico, estou carregando todas as categorias e produtos de uma vez só.
Em um cenário real, seria melhor implementar um endpoint específico para contar produtos por categoria, evitando o carregamento desnecessário de todos os produtos.
Como o teste técnico não especificou um endpoint para contar produtos por categoria, optei por esta abordagem para manter a simplicidade e atender aos requisitos do teste.
*/
const loadCategories = async () => {
  await Promise.all([
    productsStore.fetchCategories(),
    productsStore.fetchProducts({ per_page: 1000 }),
  ]);
};

const getProductCount = (categoryId: number) => {
  return productsStore.products.filter((product) => product.category_id === categoryId).length;
};

const addCategory = async () => {
  if (!newCategory.value.trim()) return;

  try {
    await productsStore.createCategory(newCategory.value);
    alert('Categoria criada com sucesso!');
    newCategory.value = '';
    await loadCategories();
  } catch {
    alert(`Erro ao criar: ${productsStore.error}`);
  }
};

const openEditModal = (category: Category) => {
  selectedCategory.value = category;
  isEditOpen.value = true;
};

const closeEditModal = () => {
  isEditOpen.value = false;
  selectedCategory.value = null;
};

const deleteCategory = async (categoryId: number) => {
  if (!confirm('Tem certeza que deseja deletar esta categoria?')) {
    return;
  }

  try {
    await productsStore.deleteCategory(categoryId);
    alert('Categoria deletada com sucesso!');
    await loadCategories();
  } catch {
    alert(`Erro ao deletar: ${productsStore.error}`);
  }
};

onMounted(() => {
  loadCategories();
});
</script>
