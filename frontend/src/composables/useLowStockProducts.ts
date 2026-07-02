import { ref, computed } from 'vue';
import api from '@/services/api';

export interface Product {
  id: number;
  name: string;
  quantity: number;
  min_quantity: number;
  price: number;
  image_path?: string;
}

export interface LowStockResponse {
  data: Product[];
  current_page: number;
  total: number;
  per_page: number;
  last_page: number;
}

export function useLowStockProducts() {
  const products = ref<Product[]>([]);
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  const pagination = ref({
    current_page: 1,
    total: 0,
    per_page: 10,
    last_page: 1,
  });

  const fetchLowStockProducts = async (page = 1, perPage = 10) => {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get<LowStockResponse>('/products/low-stock', {
        params: {
          page,
          per_page: perPage,
        },
      });

      products.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        total: response.data.total,
        per_page: response.data.per_page,
        last_page: response.data.last_page,
      };
    } catch (err) {
      error.value =
        err instanceof Error ? err.message : 'Erro ao buscar produtos com estoque baixo';
      console.error('Erro ao buscar produtos com estoque baixo:', err);
    } finally {
      isLoading.value = false;
    }
  };

  const stockPercentage = (product: Product): number => {
    if (product.min_quantity === 0) return 0;
    return Math.round((product.quantity / product.min_quantity) * 100);
  };

  const isStockCritical = (product: Product): boolean => {
    return product.quantity <= 0;
  };

  const isStockLow = (product: Product): boolean => {
    return product.quantity > 0 && product.quantity <= product.min_quantity;
  };

  const countCritical = computed(() => {
    return products.value.filter(isStockCritical).length;
  });

  const countLow = computed(() => {
    return products.value.filter(isStockLow).length;
  });

  return {
    products,
    isLoading,
    error,
    pagination,
    fetchLowStockProducts,
    stockPercentage,
    isStockCritical,
    isStockLow,
    countCritical,
    countLow,
  };
}
