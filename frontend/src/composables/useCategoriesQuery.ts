import { computed } from 'vue';
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import api from '@/services/api';
import type { Category } from '@/types';

export function useCategoriesQuery() {
  const { data, isLoading, refetch } = useQuery({
    queryKey: ['categories'],
    queryFn: () => api.get('/categories').then((r) => r.data.data as Category[]),
    staleTime: 24 * 60 * 60 * 1000, // 24 hours
  });

  const categories = computed(() => data.value ?? []);

  return { categories, isLoading, refetch };
}

export function useInvalidateCategories() {
  const queryClient = useQueryClient();
  return () => queryClient.invalidateQueries({ queryKey: ['categories'] });
}
