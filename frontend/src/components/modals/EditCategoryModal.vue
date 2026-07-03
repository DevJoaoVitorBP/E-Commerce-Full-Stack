<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import BaseModal from './BaseModal.vue';
import { useProductsStore } from '@/stores/productsStore';
import { useCategoriesQuery } from '@/composables/useCategoriesQuery';
import { useNotification } from '@/composables/useNotification';
import { updateCategorySchemaWithValidation, apiErrorSchema } from '@/schemas/category.schema';
import { getZodErrors } from '@/utils/validation';
import type { Category } from '@/types';

const { success: showSuccess } = useNotification();

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
const { categories } = useCategoriesQuery();
const isLoading = ref(false);
const errors = reactive<Record<string, string>>({});
const serverError = ref('');

const formData = reactive({
  name: '',
});

watch(
  () => props.category,
  (category) => {
    if (category) {
      formData.name = category.name;
      clearErrors();
      serverError.value = '';
    }
  },
  { immediate: true }
);

const clearErrors = () => {
  Object.keys(errors).forEach((key) => {
    delete errors[key];
  });
};

const validateForm = (): boolean => {
  clearErrors();

  const schema = updateCategorySchemaWithValidation(categories.value, props.category?.id || 0);

  const result = schema.safeParse({
    name: formData.name,
  });

  if (!result.success) {
    Object.assign(errors, getZodErrors(result.error));
    return false;
  }

  return true;
};

const handleSave = async () => {
  if (!validateForm() || !props.category) return;

  isLoading.value = true;
  serverError.value = '';
  try {
    await productsStore.updateCategory(props.category.id, formData.name);
    showSuccess('Categoria atualizada com sucesso!');
    emit('close');
    emit('success');
  } catch (err) {
    const parsed = apiErrorSchema.safeParse(err);
    if (parsed.success && parsed.data.response.data.errors) {
      Object.entries(parsed.data.response.data.errors).forEach(([field, messages]) => {
        errors[field] = messages[0];
      });
    } else {
      serverError.value = productsStore.error ?? 'Erro ao atualizar categoria';
    }
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

      <p
        v-if="serverError"
        class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"
      >
        {{ serverError }}
      </p>
    </form>
  </BaseModal>
</template>
