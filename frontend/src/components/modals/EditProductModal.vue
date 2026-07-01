<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import BaseModal from './BaseModal.vue';
import { useProductsStore } from '@/stores/productsStore';
import type { Product } from '@/types';

interface Props {
  isOpen: boolean;
  product: Product | null;
}

interface Emits {
  (e: 'close'): void;
  (e: 'success'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const productsStore = useProductsStore();
const isLoading = ref(false);
const errors = reactive({
  name: '',
  price: '',
  quantity: '',
  description: ''
});

const formData = reactive({
  name: '',
  price: 0,
  quantity: 0,
  description: '',
  active: true
});

watch(
  () => props.product,
  (product) => {
    if (product) {
      formData.name = product.name;
      formData.price = product.price;
      formData.quantity = product.quantity;
      formData.description = product.description || '';
      formData.active = product.active;
      clearErrors();
    }
  },
  { immediate: true }
);

const clearErrors = () => {
  errors.name = '';
  errors.price = '';
  errors.quantity = '';
  errors.description = '';
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

  if (formData.quantity < 0) {
    errors.quantity = 'Quantidade não pode ser negativa';
    isValid = false;
  }

  return isValid;
};

const handleSave = async () => {
  if (!validateForm() || !props.product) return;

  isLoading.value = true;
  try {
    await productsStore.updateProduct(props.product.id, {
      name: formData.name,
      price: formData.price,
      quantity: formData.quantity,
      description: formData.description,
      active: formData.active,
    });
    alert('Produto atualizado com sucesso!');
    emit('close');
    emit('success');
  } catch {
    alert(`Erro ao atualizar: ${productsStore.error}`);
  } finally {
    isLoading.value = false;
  }
};

const closeModal = () => {
  emit('close');
};
</script>

<template>
  <BaseModal
    :is-open="isOpen"
    title="Editar Produto"
    confirm-text="Salvar Alterações"
    :is-loading="isLoading"
    @close="closeModal"
    @confirm="handleSave"
  >
    <form class="space-y-4" @submit.prevent="handleSave">
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

      <!-- Price -->
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

      <!-- Quantity -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade em Estoque</label>
        <input
          v-model.number="formData.quantity"
          type="number"
          min="0"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="0"
        />
        <p v-if="errors.quantity" class="text-sm text-red-600 mt-1">{{ errors.quantity }}</p>
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
        <textarea
          v-model="formData.description"
          rows="4"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Digite a descrição do produto..."
        ></textarea>
        <p v-if="errors.description" class="text-sm text-red-600 mt-1">{{ errors.description }}</p>
      </div>

      <!-- Status -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select
          v-model="formData.active"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option :value="true">Ativo</option>
          <option :value="false">Inativo</option>
        </select>
      </div>
    </form>
  </BaseModal>
</template>
