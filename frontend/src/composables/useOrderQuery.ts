import { computed, type Ref } from 'vue';
import { useQuery } from '@tanstack/vue-query';
import api from '@/services/api';
import type { Order } from '@/types';

export function useOrderQuery(id: Ref<number | null>) {
  const { data, isLoading, isError, error } = useQuery({
    queryKey: computed(() => ['order', id.value]),
    queryFn: () => api.get(`/orders/${id.value}`).then((r) => r.data.data as Order),
    enabled: computed(() => !!id.value),
    staleTime: 5 * 60 * 1000,
  });

  const order = computed(() => data.value ?? null);

  return { order, isLoading, isError, error };
}
