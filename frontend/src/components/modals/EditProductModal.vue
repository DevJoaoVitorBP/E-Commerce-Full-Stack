<script setup lang="ts">
/// <reference lib="dom" />
/* eslint-disable @typescript-eslint/no-explicit-any */
import { ref, reactive, watch } from 'vue';
import BaseModal from './BaseModal.vue';
import { useProductsStore } from '@/stores/productsStore';
import { updateProductSchema } from '@/schemas/product.schema';
import { getZodErrors } from '@/utils/validation';
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
const errors = reactive<Record<string, string>>({});
const imageFile = ref<unknown>(null);
const imagePreview = ref<string>('');

const formData = reactive({
  name: '',
  price: 0,
  quantity: 0,
  description: '',
  category_id: 0,
  active: true,
});

watch(
  () => props.product,
  (product) => {
    if (product) {
      formData.name = product.name;
      formData.price = product.price;
      formData.quantity = product.quantity;
      formData.description = product.description || '';
      formData.category_id = product.category_id;
      formData.active = product.active;
      imagePreview.value = product.image_url || '';
      imageFile.value = null;
      clearErrors();
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

  const result = updateProductSchema.safeParse({
    name: formData.name,
    price: formData.price,
    quantity: formData.quantity,
    active: formData.active,
  });

  if (!result.success) {
    Object.assign(errors, getZodErrors(result.error));
    return false;
  }

  return true;
};

const handleSave = async () => {
  if (!validateForm() || !props.product) return;

  isLoading.value = true;
  try {
    const formDataToSend: any = new FormData();
    formDataToSend.append('name', formData.name);
    formDataToSend.append('price', formData.price.toString());
    formDataToSend.append('quantity', formData.quantity.toString());
    formDataToSend.append('description', formData.description);
    formDataToSend.append('category_id', formData.category_id.toString());
    formDataToSend.append('active', formData.active.toString());
    formDataToSend.append('_method', 'PUT');

    if (imageFile.value) {
      if (imageFile.value) {
        formDataToSend.append('image', imageFile.value);
      }
    }

    await productsStore.updateProduct(props.product.id, formDataToSend);
    alert('Produto atualizado com sucesso!');
    emit('close');
    emit('success');
  } catch (err) {
    const error = err instanceof Error ? err.message : String(err);
    alert(`Erro ao atualizar: ${error || productsStore.error}`);
  } finally {
    isLoading.value = false;
  }
};

const handleImageUpload = (event: unknown) => {
  const target = (
    event as unknown as { target?: { files?: { length: number; [key: number]: unknown } } }
  )?.target as unknown;
  const file = (target as unknown as { files?: { length: number; [key: number]: unknown } })
    ?.files?.[0];

  if (file) {
    imageFile.value = file;
    const reader: any = new FileReader();
    reader.onload = (e: unknown) => {
      imagePreview.value = (e as any)?.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
};

const removeImage = () => {
  imageFile.value = null;
  imagePreview.value = props.product?.image_url || '';
  const input = document.querySelector('input[type="file"]') as unknown;
  if (input) {
    (input as unknown as { value?: string }).value = '';
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

      <!-- Image Upload -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Imagem do Produto</label>
        <div class="relative">
          <input
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleImageUpload"
            id="image-upload"
          />
          <label
            for="image-upload"
            class="w-full px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition flex items-center justify-center min-h-32"
          >
            <div v-if="!imagePreview" class="text-center">
              <svg
                class="mx-auto h-8 w-8 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                ></path>
              </svg>
              <p class="mt-2 text-sm text-gray-600">Clique para carregar imagem</p>
            </div>
            <img v-else :src="imagePreview" alt="Preview" class="max-h-32 max-w-full" />
          </label>
        </div>
        <div v-if="imageFile" class="mt-2">
          <p class="text-sm text-gray-600">
            Arquivo: <strong>{{ (imageFile as any).name }}</strong> ({{
              ((imageFile as any).size / 1024).toFixed(2)
            }}
            KB)
          </p>
          <button
            type="button"
            @click="removeImage"
            class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium"
          >
            Remover imagem
          </button>
        </div>
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
