<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import BaseModal from './BaseModal.vue';
import { useProductsStore } from '@/stores/productsStore';
import type { Category } from '@/types';

interface Props {
  isOpen: boolean;
  category: Category | null;
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
});

const formData = reactive({
  name: '',
});

watch(
  () => props.category,
  (category) => {
    if (category) {
      formData.name = category.name;
      clearErrors();
    }
  },
  { immediate: true }
);

const clearErrors = () => {
  errors.name = '';
};

const validateForm = (): boolean => {
  clearErrors();

  if (!formData.name.trim()) {
    errors.name = 'Nome é obrigatório';
    return false;
  }

  return true;
};

const handleSave = async () => {
  if (!validateForm() || !props.category) return;

  isLoading.value = true;
  try {
    await productsStore.updateCategory(props.category.id, formData.name);
    alert('Categoria atualizada com sucesso!');
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
    title="Editar Categoria"
    confirm-text="Salvar Alterações"
    :is-loading="isLoading"
    @close="closeModal"
    @confirm="handleSave"
  >
    <form class="space-y-4" @submit.prevent="handleSave">
      <!-- Category Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Categoria</label>
        <input
          v-model="formData.name"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
          placeholder="Ex: Eletrônicos"
        />
        <p v-if="errors.name" class="text-sm text-red-600 mt-1">{{ errors.name }}</p>
      </div>
    </form>
  </BaseModal>
</template>
