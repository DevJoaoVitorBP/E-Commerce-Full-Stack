<script setup lang="ts">
/// <reference lib="dom" />
/* eslint-disable @typescript-eslint/no-explicit-any */
import { ref, reactive } from 'vue';
import BaseModal from './BaseModal.vue';
import { useProductsStore } from '@/stores/productsStore';
import { useCategoriesQuery } from '@/composables/useCategoriesQuery';
import { useNotification } from '@/composables/useNotification';
import { createProductSchema } from '@/schemas/product.schema';
import { getZodErrors } from '@/utils/validation';

const { success: showSuccess, error: showError } = useNotification();

interface Props {
  isOpen: boolean;
}

interface Emits {
  (e: 'close'): void;
  (e: 'success'): void;
}

defineProps<Props>();
const emit = defineEmits<Emits>();

const productsStore = useProductsStore();
const { categories } = useCategoriesQuery();
const isLoading = ref(false);
const errors = reactive<Record<string, string>>({});
const imageFile = ref<unknown>(null);
const imagePreview = ref<string>('');

const formData = reactive({
  name: '',
  description: '',
  price: 0,
  cost_price: 0,
  quantity: 0,
  min_quantity: 0,
  category_id: 0,
  active: true,
});

const clearErrors = () => {
  Object.keys(errors).forEach((key) => {
    delete errors[key];
  });
};

const validateForm = (): boolean => {
  clearErrors();

  const result = createProductSchema.safeParse({
    name: formData.name,
    description: formData.description,
    price: formData.price,
    cost_price: formData.cost_price,
    quantity: formData.quantity,
    category_id: formData.category_id,
    min_quantity: formData.min_quantity,
    active: formData.active,
  });

  if (!result.success) {
    Object.assign(errors, getZodErrors(result.error));
    return false;
  }

  return true;
};

const handleCreate = async () => {
  if (!validateForm()) return;

  isLoading.value = true;
  try {
    const formDataToSend: any = new FormData();
    formDataToSend.append('name', formData.name);
    formDataToSend.append('description', formData.description);
    formDataToSend.append('price', formData.price.toString());
    formDataToSend.append('cost_price', formData.cost_price.toString());
    formDataToSend.append('quantity', formData.quantity.toString());
    formDataToSend.append('category_id', formData.category_id.toString());
    formDataToSend.append('active', formData.active ? '1' : '0');

    if (imageFile.value) {
      if (imageFile.value) {
        formDataToSend.append('image', imageFile.value);
      }
    }

    await productsStore.createProduct(formDataToSend);
    showSuccess('Produto criado com sucesso!');
    emit('close');
    resetForm();
    emit('success');
  } catch (err) {
    const error = err instanceof Error ? err.message : String(err);
    showError(`Erro ao criar: ${error || productsStore.error}`);
  } finally {
    isLoading.value = false;
  }
};

const closeModal = () => {
  emit('close');
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
  imagePreview.value = '';
  const input = document.querySelector('input[type="file"]') as unknown;
  if (input) {
    (input as unknown as { value?: string }).value = '';
  }
};

const resetForm = () => {
  formData.name = '';
  formData.description = '';
  imageFile.value = null;
  imagePreview.value = '';
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

      <!-- Minimal Quantity -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade Mínima</label>
        <input
          v-model.number="formData.min_quantity"
          type="number"
          min="0"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="0"
        />
        <p v-if="errors.min_quantity" class="text-sm text-red-600 mt-1">
          {{ errors.min_quantity }}
        </p>
      </div>

      <!-- Category -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
        <select
          v-model.number="formData.category_id"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="0" disabled>Selecione uma categoria</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
        <p v-if="errors.category_id" class="text-sm text-red-600 mt-1">{{ errors.category_id }}</p>
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
        <label class="flex items-center gap-2 cursor-pointer">
          <input v-model="formData.active" type="checkbox" class="w-4 h-4" />
          <span class="text-sm font-medium text-gray-700">Produto Ativo</span>
        </label>
      </div>
    </form>
  </BaseModal>
</template>
