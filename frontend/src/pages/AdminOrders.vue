<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-2xl md:text-4xl font-bold text-gray-900">Gerenciar Pedidos</h1>
          <p class="text-gray-600 mt-1">Total: {{ ordersStore.orders.length }} pedidos</p>
        </div>
        <router-link
          to="/admin"
          class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
        >
          ← Voltar
        </router-link>
      </div>

      <!-- Filtro de Pedidos -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex gap-4 flex-wrap">
          <select
            v-model="filterStatus"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os Status</option>
            <option value="pending">Pendente</option>
            <option value="processing">Processando</option>
            <option value="shipped">Enviado</option>
            <option value="delivered">Entregue</option>
            <option value="cancelled">Cancelado</option>
          </select>
          <button
            @click="loadOrders"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            🔄 Recarregar
          </button>
        </div>
      </div>

      <!-- Estado de Carregamento -->
      <div v-if="ordersStore.isLoading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600"></div>
      </div>

      <!-- Tabela de Pedidos -->
      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">
                  Pedido
                </th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Data</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Total</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Ações</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <router-link
                    :to="`/orders/${order.id}`"
                    class="font-semibold text-blue-600 hover:underline"
                  >
                    #{{ order.id }}
                  </router-link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-gray-600">{{ formatDate(order.created_at) }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="font-semibold text-blue-600">R$ {{ formatPrice(order.total) }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="getStatusClass(order.status)"
                    class="px-3 py-1 rounded-full text-sm font-semibold inline-block"
                  >
                    {{ translateStatus(order.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex gap-2">
                    <select
                      :value="order.status"
                      @change="
                        updateOrderStatus(order.id, ($event.target as HTMLSelectElement).value)
                      "
                      class="px-3 py-1 border border-gray-300 rounded hover:border-blue-500 text-sm"
                    >
                      <option value="pending">Pendente</option>
                      <option value="processing">Processando</option>
                      <option value="shipped">Enviado</option>
                      <option value="delivered">Entregue</option>
                      <option value="cancelled">Cancelado</option>
                    </select>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useOrdersStore } from '@/stores/ordersStore';

const ordersStore = useOrdersStore();
const filterStatus = ref('');

const formatDate = (date: string | Date) => {
  const d = new Date(date);
  return d.toLocaleDateString('pt-BR', { year: 'numeric', month: '2-digit', day: '2-digit' });
};

const formatPrice = (price: number) => {
  return price.toFixed(2).replace('.', ',');
};

const translateStatus = (status: string): string => {
  const statusMap: Record<string, string> = {
    pending: 'Pendente',
    processing: 'Processando',
    shipped: 'Enviado',
    delivered: 'Entregue',
    cancelled: 'Cancelado',
  };
  return statusMap[status] || status;
};

const getStatusClass = (status: string): string => {
  const statusClasses: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    shipped: 'bg-purple-100 text-purple-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return statusClasses[status] || 'bg-gray-100 text-gray-800';
};

const filteredOrders = computed(() => {
  if (!filterStatus.value) return ordersStore.orders;
  return ordersStore.orders.filter((order) => order.status === filterStatus.value);
});

const loadOrders = async () => {
  await ordersStore.fetchOrders();
};

const updateOrderStatus = async (orderId: number, newStatus: string) => {
  try {
    await ordersStore.updateOrderStatus(orderId, newStatus);
    alert(`Pedido #${orderId} atualizado para ${translateStatus(newStatus)}`);
  } catch {
    alert(`Erro ao atualizar: ${ordersStore.error}`);
  }
};

onMounted(() => {
  loadOrders();
});
</script>
