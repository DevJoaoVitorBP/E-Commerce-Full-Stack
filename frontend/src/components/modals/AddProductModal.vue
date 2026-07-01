<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import BaseModal from './BaseModal.vue';
import { useProductsStore } from '@/stores/productsStore';

interface Props {
  isOpen: boolean;
}

interface Emits {
  (e: 'close'): void;
  (e: 'success'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

watch(() => props.isOpen, async (isOpen) => {
  if (isOpen) {
    await productsStore.fetchCategories();
  }
});

const productsStore = useProductsStore();
const isLoading = ref(false);
const errors = reactive({
  name: '',
  description: '',
  price: '',
  cost_price: '',
  quantity: '',
  category_id: '',
});

const formData = reactive({
  name: '',
  description: '',
  price: 0,
  cost_price: 0,
  quantity: 0,
  category_id: 0,
  active: true,
});

const clearErrors = () => {
  errors.name = '';
  errors.description = '';
  errors.price = '';
  errors.cost_price = '';
  errors.quantity = '';
  errors.category_id = '';
};

const validateForm = (): boolean => {
  clearErrors();
  let isValid = true;

  if (!formData.name.trim()) {
    errors.name = 'Nome é obrigatório';
    isValid = false;
  }

  if (formData.price < 0) {
    errors.price = 'Preço não pode ser negativo';
    isValid = false;
  }

  if (formData.cost_price < 0) {
    errors.cost_price = 'Custo não pode ser negativo';
    isValid = false;
  }

  if (formData.quantity < 0) {
    errors.quantity = 'Quantidade não pode ser negativa';
    isValid = false;
  }

  if (formData.category_id === 0) {
    errors.category_id = 'Selecione uma categoria';
    isValid = false;
  }

  return isValid;
};

const handleCreate = async () => {
  if (!validateForm()) return;

  isLoading.value = true;
  try {
    await productsStore.createProduct({
      name: formData.name,
      description: formData.description,
      price: formData.price,
      cost_price: formData.cost_price,
      quantity: formData.quantity,
      category_id: formData.category_id,
      active: formData.active,
    });
    alert('Produto criado com sucesso!');
    emit('close');
    resetForm();
    emit('success');
  } catch {
    alert(`Erro ao criar: ${productsStore.error}`);
  } finally {
    isLoading.value = false;
  }
};

const closeModal = () => {
  emit('close');
};

const resetForm = () => {
  formData.name = '';
  formData.description = '';
  formData.price = 0;
  formData.cost_price = 0;
  formData.quantity = 0;
  formData.category_id = 0;
  formData.active = true;
  clearErrors();
};
</script>

<template>
  <BaseModal
    :is-open="isOpen"
    title="Adicionar Novo Produto"
    confirm-text="Criar Produto"
    :is-loading="isLoading"
    @close="closeModal"
    @confirm="handleCreate"
  >
    <form class="space-y-4" @submit.prevent="handleCreate">
      <!-- Product Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Produto</label>
        <input
          v-model="formData.name"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Ex: Notebook Dell"
        />
        <p v-if="errors.name" class="text-sm text-red-600 mt-1">{{ errors.name }}</p>
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
        <textarea
          v-model="formData.description"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Descrição do produto..."
          rows="3"
        ></textarea>
        <p v-if="errors.description" class="text-sm text-red-600 mt-1">{{ errors.description }}</p>
      </div>

      <!-- Price -->
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Preço (R$)</label>
          <input
            v-model.number="formData.price"
            type="number"
            step="0.01"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="0.00"
          />
          <p v-if="errors.price" class="text-sm text-red-600 mt-1">{{ errors.price }}</p>
        </div>

        <!-- Cost Price -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Custo (R$)</label>
          <input
            v-model.number="formData.cost_price"
            type="number"
            step="0.01"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="0.00"
          />
          <p v-if="errors.cost_price" class="text-sm text-red-600 mt-1">{{ errors.cost_price }}</p>
        </div>
      </div>

      <!-- Quantity -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade Inicial</label>
        <input
          v-model.number="formData.quantity"
          type="number"
          min="0"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="0"
        />
        <p v-if="errors.quantity" class="text-sm text-red-600 mt-1">{{ errors.quantity }}</p>
      </div>

      <!-- Category -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
        <select
          v-model.number="formData.category_id"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="0" disabled>Selecione uma categoria</option>
          <option v-for="cat in productsStore.categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
        <p v-if="errors.category_id" class="text-sm text-red-600 mt-1">{{ errors.category_id }}</p>
      </div>

      <!-- Status -->
      <div>
        <label class="flex items-center gap-2 cursor-pointer">
          <input v-model="formData.active" type="checkbox" class="w-4 h-4" />
          <span class="text-sm font-medium text-gray-700">Produto Ativo</span>
        </label>
      </div>
    </form>
  </BaseModal>
</template>
