import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api';
import type { Product, Category, PaginatedResponse } from '@/types';
import { getErrorMessage } from '@/utils/errorHandler';

export interface ProductFilters {
  category_id?: number | string;
  search?: string;
  min_price?: number;
  max_price?: number;
  sort?: string;
  per_page?: number;
  page?: number;
}

function cleanFilters(filters: ProductFilters): ProductFilters {
  return Object.fromEntries(
    Object.entries(filters).filter(
      ([, value]) => value !== undefined && value !== null && value !== ''
    )
  ) as ProductFilters;
}

const CACHE_DURATION = 5 * 60 * 1000; // 5 minutos em ms

interface ProductsPagination {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

interface ProductsCacheData {
  products: Product[];
  pagination: ProductsPagination;
}

interface CacheEntry<T> {
  timestamp: number;
  data: T;
}

export const useProductsStore = defineStore('products', () => {
  const products = ref<Product[]>([]);
  const categories = ref<Category[]>([]);
  const currentProduct = ref<Product | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  const pagination = ref<ProductsPagination>({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1,
  });

  const productsCache = ref<Map<string, CacheEntry<ProductsCacheData>>>(new Map());
  const categoriesCache = ref<CacheEntry<Category[]> | null>(null);

  const hasNextPage = computed(() => pagination.value.current_page < pagination.value.last_page);
  const hasPrevPage = computed(() => pagination.value.current_page > 1);

  function isCacheValid<T>(
    cacheEntry: CacheEntry<T> | null | undefined
  ): cacheEntry is CacheEntry<T> {
    if (!cacheEntry) return false;
    return Date.now() - cacheEntry.timestamp < CACHE_DURATION;
  }

  async function fetchProducts(filters: ProductFilters = {}) {
    const cacheKey = JSON.stringify(cleanFilters(filters));
    const cached = productsCache.value.get(cacheKey);

    if (isCacheValid(cached)) {
      products.value = cached.data.products;
      pagination.value = cached.data.pagination;
      return;
    }

    isLoading.value = true;
    error.value = null;
    try {
      const cleanedFilters = cleanFilters(filters);
      const response = await api.get('/products', { params: cleanedFilters });
      const data = response.data as PaginatedResponse<Product>;
      products.value = data.data;
      pagination.value = {
        current_page: data.meta.current_page,
        per_page: data.meta.per_page,
        total: data.meta.total,
        last_page: data.meta.last_page,
      };

      productsCache.value.set(cacheKey, {
        timestamp: Date.now(),
        data: { products: data.data, pagination: pagination.value },
      });
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao carregar produtos');
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchProductById(id: number) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.get(`/products/${id}`);
      currentProduct.value = response.data.data;
      return currentProduct.value;
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao carregar produto');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchCategories() {
    if (isCacheValid(categoriesCache.value)) {
      categories.value = categoriesCache.value.data;
      return;
    }

    error.value = null;
    try {
      const response = await api.get('/categories');
      categories.value = response.data.data;

      categoriesCache.value = {
        timestamp: Date.now(),
        data: response.data.data,
      };
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao carregar categorias');
    }
  }

  async function fetchCategoryProducts(
    categoryId: number,
    filters: Omit<ProductFilters, 'category_id'> = {}
  ) {
    isLoading.value = true;
    error.value = null;
    try {
      const cleanedFilters = cleanFilters(filters as ProductFilters);
      const response = await api.get(`/categories/${categoryId}/products`, {
        params: cleanedFilters,
      });
      const data = response.data as PaginatedResponse<Product>;
      products.value = data.data;
      pagination.value = {
        current_page: data.meta.current_page,
        per_page: data.meta.per_page,
        total: data.meta.total,
        last_page: data.meta.last_page,
      };
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao carregar produtos da categoria');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function createProduct(productData: Partial<Product> | FormData) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await api.post('/products', productData);
      products.value.push(response.data.data);
      return response.data.data;
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao criar produto');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateProduct(id: number, productData: Partial<Product> | FormData) {
    isLoading.value = true;
    error.value = null;
    try {
      let response;
      if (productData instanceof FormData) {
        // PHP não suporta multipart/form-data em PUT - enviar como POST com _method: PUT
        response = await api.post(`/products/${id}`, productData);
      } else {
        response = await api.put(`/products/${id}`, productData);
      }
      const index = products.value.findIndex((p) => p.id === id);
      if (index !== -1) {
        products.value[index] = response.data.data;
      }
      if (currentProduct.value?.id === id) {
        currentProduct.value = response.data.data;
      }
      return response.data.data;
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao atualizar produto');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function deleteProduct(id: number) {
    isLoading.value = true;
    error.value = null;
    try {
      await api.delete(`/products/${id}`);
      products.value = products.value.filter((p) => p.id !== id);
      if (currentProduct.value?.id === id) {
        currentProduct.value = null;
      }
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao deletar produto');
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function createCategory(name: string) {
    error.value = null;
    try {
      const response = await api.post('/categories', { name });
      categories.value.push(response.data.data);
      return response.data.data;
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao criar categoria');
      throw err;
    }
  }

  async function updateCategory(id: number, name: string) {
    error.value = null;
    try {
      const response = await api.put(`/categories/${id}`, { name });
      const index = categories.value.findIndex((c) => c.id === id);
      if (index !== -1) {
        categories.value[index] = response.data.data;
      }
      return response.data.data;
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao atualizar categoria');
      throw err;
    }
  }

  async function deleteCategory(id: number) {
    error.value = null;
    try {
      await api.delete(`/categories/${id}`);
      categories.value = categories.value.filter((c) => c.id !== id);
    } catch (err: unknown) {
      error.value = getErrorMessage(err, 'Erro ao deletar categoria');
      throw err;
    }
  }

  function reset() {
    products.value = [];
    categories.value = [];
    currentProduct.value = null;
    error.value = null;
    pagination.value = {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    };
  }

  function clearCache() {
    productsCache.value.clear();
    categoriesCache.value = null;
  }

  return {
    products,
    categories,
    currentProduct,
    isLoading,
    error,
    pagination,
    hasNextPage,
    hasPrevPage,
    fetchProducts,
    fetchProductById,
    fetchCategories,
    fetchCategoryProducts,
    createProduct,
    updateProduct,
    deleteProduct,
    createCategory,
    updateCategory,
    deleteCategory,
    reset,
    clearCache,
  };
});
