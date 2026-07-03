import { computed, type Ref, ref } from 'vue';
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import api from '@/services/api';
import type { Order, PaginatedResponse } from '@/types';

export function useOrdersQuery(
  page: Ref<number> = ref(1),
  perPage: Ref<number> = ref(10),
  status: Ref<string> = ref('')
) {
  const { data, isLoading, isFetching, refetch } = useQuery({
    queryKey: computed(() => ['orders', page.value, perPage.value, status.value]),
    queryFn: () => {
      const params: Record<string, unknown> = {
        page: page.value,
        per_page: perPage.value,
      };
      if (status.value) params.status = status.value;
      return api.get('/orders', { params }).then((r) => r.data as PaginatedResponse<Order>);
    },
    staleTime: 5 * 60 * 1000,
  });

  const orders = computed(() => data.value?.data ?? []);
  const pagination = computed(() => ({
    current_page: data.value?.meta?.current_page ?? 1,
    per_page: data.value?.meta?.per_page ?? 10,
    total: data.value?.meta?.total ?? 0,
    last_page: data.value?.meta?.last_page ?? 1,
  }));

  return { orders, pagination, isLoading, isFetching, refetch };
}

export function useInvalidateOrders() {
  const queryClient = useQueryClient();
  return () => queryClient.invalidateQueries({ queryKey: ['orders'] });
}
