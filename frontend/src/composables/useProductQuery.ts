import { computed, type Ref } from 'vue';
import { useQuery } from '@tanstack/vue-query';
import api from '@/services/api';
import type { Product } from '@/types';

export function useProductQuery(id: Ref<number | null>) {
  const { data, isLoading, isError, error } = useQuery({
    queryKey: computed(() => ['product', id.value]),
    queryFn: () => api.get(`/products/${id.value}`).then((r) => r.data.data as Product),
    enabled: computed(() => !!id.value),
    staleTime: 5 * 60 * 1000,
  });

  const product = computed(() => data.value ?? null);

  return { product, isLoading, isError, error };
}
