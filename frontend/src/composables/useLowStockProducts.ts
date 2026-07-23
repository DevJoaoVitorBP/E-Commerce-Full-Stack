import { computed, ref, type Ref } from 'vue';
import { useQuery } from '@tanstack/vue-query';
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
  total_critical: number;
  total_low: number;
}

export function useLowStockProducts(page: Ref<number> = ref(1), perPage: Ref<number> = ref(10)) {
  const {
    data,
    isLoading,
    error: queryError,
    refetch,
  } = useQuery({
    queryKey: computed(() => ['products', 'low-stock', page.value, perPage.value]),
    queryFn: () =>
      api
        .get<LowStockResponse>('/products/low-stock', {
          params: { page: page.value, per_page: perPage.value },
        })
        .then((r) => r.data),
    staleTime: 2 * 60 * 1000,
  });

  const products = computed(() => data.value?.data ?? []);
  const pagination = computed(() => ({
    current_page: data.value?.current_page ?? 1,
    total: data.value?.total ?? 0,
    per_page: data.value?.per_page ?? 10,
    last_page: data.value?.last_page ?? 1,
  }));

  const error = computed(() =>
    queryError.value instanceof Error ? queryError.value.message : null
  );

  const stockPercentage = (product: Product): number => {
    if (product.min_quantity === 0) return 100;
    return Math.min(Math.round((product.quantity / product.min_quantity) * 100), 100);
  };

  const isStockCritical = (product: Product): boolean => product.quantity <= 0;

  const isStockLow = (product: Product): boolean =>
    product.quantity > 0 && product.quantity <= product.min_quantity;

  const countCritical = computed(() => data.value?.total_critical ?? 0);
  const countLow = computed(() => data.value?.total_low ?? 0);

  return {
    products,
    isLoading,
    error,
    pagination,
    fetchLowStockProducts: refetch,
    stockPercentage,
    isStockCritical,
    isStockLow,
    countCritical,
    countLow,
  };
}
