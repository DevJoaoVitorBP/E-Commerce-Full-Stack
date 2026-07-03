<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-2xl md:text-4xl font-bold text-gray-900">Gerenciar Categorias</h1>
          <p class="text-gray-600 mt-1">Total: {{ categories.length }} categorias</p>
        </div>

        <router-link
          to="/admin"
          class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
        >
          ← Voltar
        </router-link>
      </div>

      <!-- Adicionar Categoria -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Nova Categoria</h2>

        <div class="space-y-3">
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
          <p v-if="errors.name" class="text-sm text-red-600">{{ errors.name }}</p>
        </div>
      </div>

      <!-- Tabela de Categorias -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
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
              <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50">
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
                      :disabled="getProductCount(category.id) > 0"
                      :title="
                        getProductCount(category.id) > 0
                          ? `Remova os ${getProductCount(category.id)} produtos desta categoria antes de deletar`
                          : 'Deletar categoria'
                      "
                      :class="{
                        'opacity-50 cursor-not-allowed': getProductCount(category.id) > 0,
                        'hover:bg-red-700': getProductCount(category.id) === 0,
                      }"
                      class="px-3 py-1 bg-red-600 text-white rounded text-sm transition"
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
    </div>

    <!-- Modal de Edição -->
    <EditCategoryModal
      :is-open="isEditOpen"
      :category="selectedCategory"
      @close="closeEditModal"
      @success="loadCategories"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useCategoriesQuery, useInvalidateCategories } from '@/composables/useCategoriesQuery';
import { useProductsQuery, useInvalidateProducts } from '@/composables/useProductsQuery';
import { useProductsStore } from '@/stores/productsStore';
import { useNotification } from '@/composables/useNotification';
import EditCategoryModal from '@/components/modals/EditCategoryModal.vue';
import { createCategorySchemaWithValidation } from '@/schemas/category.schema';
import { getZodErrors } from '@/utils/validation';
import type { Category } from '@/types';

const { success: showSuccess, error: showError } = useNotification();
const productsStore = useProductsStore();
const invalidateCategories = useInvalidateCategories();
const invalidateProducts = useInvalidateProducts();

const { categories } = useCategoriesQuery();
const { products: allProducts } = useProductsQuery({ per_page: 1000 });

const newCategory = ref('');
const isEditOpen = ref(false);
const selectedCategory = ref<Category | null>(null);
const errors = reactive<Record<string, string>>({});

const loadCategories = () => {
  invalidateCategories();
  invalidateProducts();
};

const getProductCount = (categoryId: number) => {
  return allProducts.value.filter((product) => product.category_id === categoryId).length;
};

const clearErrors = () => {
  Object.keys(errors).forEach((key) => {
    delete errors[key];
  });
};

const validateForm = (): boolean => {
  clearErrors();

  const schema = createCategorySchemaWithValidation(categories.value);
  const result = schema.safeParse({ name: newCategory.value });

  if (!result.success) {
    Object.assign(errors, getZodErrors(result.error));
    return false;
  }

  return true;
};

const addCategory = async () => {
  if (!validateForm()) return;

  try {
    await productsStore.createCategory(newCategory.value);
    showSuccess('Categoria criada com sucesso!');
    newCategory.value = '';
    clearErrors();
    invalidateCategories();
  } catch {
    showError(`Erro ao criar: ${productsStore.error}`);
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
    showSuccess('Categoria deletada com sucesso!');
    invalidateCategories();
  } catch {
    showError(`Erro ao deletar: ${productsStore.error}`);
  }
};
</script>
