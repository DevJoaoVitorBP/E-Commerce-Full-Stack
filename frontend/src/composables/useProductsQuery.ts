import { computed, toValue, type MaybeRefOrGetter } from 'vue';
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import api from '@/services/api';
import type { Product, PaginatedResponse } from '@/types';

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
    Object.entries(filters).filter(([, v]) => v !== undefined && v !== null && v !== '')
  ) as ProductFilters;
}

export function useProductsQuery(filters: MaybeRefOrGetter<ProductFilters> = {}) {
  const cleanedFilters = computed(() => cleanFilters(toValue(filters)));

  const { data, isLoading, isFetching, isError, error, refetch } = useQuery({
    queryKey: computed(() => ['products', cleanedFilters.value]),
    queryFn: () =>
      api
        .get('/products', { params: cleanedFilters.value })
        .then((r) => r.data as PaginatedResponse<Product>),
    staleTime: 5 * 60 * 1000,
  });

  const products = computed(() => data.value?.data ?? []);
  const pagination = computed(() => ({
    current_page: data.value?.meta?.current_page ?? 1,
    per_page: data.value?.meta?.per_page ?? 15,
    total: data.value?.meta?.total ?? 0,
    last_page: data.value?.meta?.last_page ?? 1,
  }));
  const hasNextPage = computed(() => pagination.value.current_page < pagination.value.last_page);
  const hasPrevPage = computed(() => pagination.value.current_page > 1);

  return {
    products,
    pagination,
    hasNextPage,
    hasPrevPage,
    isLoading,
    isFetching,
    isError,
    error,
    refetch,
  };
}

export function useInvalidateProducts() {
  const queryClient = useQueryClient();
  return () => queryClient.invalidateQueries({ queryKey: ['products'] });
}
