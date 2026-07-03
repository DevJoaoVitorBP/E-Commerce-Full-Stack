<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl md:text-4xl font-bold text-gray-900">Meu Perfil</h1>
        <p class="text-gray-600 mt-1">Gerencie suas informações pessoais</p>
      </div>

      <!-- Informações do usuário -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
        <div class="flex items-center gap-4 mb-8">
          <div
            class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center text-white text-2xl font-bold"
          >
            {{ userInitial }}
          </div>
          <div>
            <p class="text-xl font-bold text-gray-900">{{ authStore.user?.name }}</p>
            <p class="text-gray-500">{{ authStore.user?.email }}</p>
            <span
              v-if="authStore.isAdmin"
              class="inline-block mt-1 px-2.5 py-0.5 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full"
            >
              Administrador
            </span>
          </div>
        </div>

        <!-- Formulário de edição de perfil -->
        <form @submit.prevent="saveProfile">
          <h2 class="text-lg font-bold text-gray-900 mb-5">Informações Básicas</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Nome</label>
              <input
                v-model="profileForm.name"
                type="text"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">E-mail</label>
              <input
                v-model="profileForm.email"
                type="email"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p v-if="errors.email" class="text-red-600 text-sm mt-1">{{ errors.email }}</p>
            </div>
          </div>

          <div
            v-if="profileSuccess"
            class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm"
          >
            {{ profileSuccess }}
          </div>
          <div
            v-if="profileError"
            class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm"
          >
            {{ profileError }}
          </div>

          <button
            type="submit"
            :disabled="authStore.isLoading"
            class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition"
          >
            {{ authStore.isLoading ? 'Salvando...' : 'Salvar Alterações' }}
          </button>
        </form>
      </div>

      <!-- Alterar Senha -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-lg font-bold text-gray-900 mb-5">Alterar Senha</h2>

        <form @submit.prevent="changePassword">
          <div class="space-y-4 mb-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Senha Atual</label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p v-if="passwordErrors.current_password" class="text-red-600 text-sm mt-1">
                {{ passwordErrors.current_password }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Nova Senha</label>
              <input
                v-model="passwordForm.password"
                type="password"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p v-if="passwordErrors.password" class="text-red-600 text-sm mt-1">
                {{ passwordErrors.password }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1"
                >Confirmar Nova Senha</label
              >
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p v-if="passwordErrors.password_confirmation" class="text-red-600 text-sm mt-1">
                {{ passwordErrors.password_confirmation }}
              </p>
            </div>
          </div>

          <div
            v-if="passwordSuccess"
            class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm"
          >
            {{ passwordSuccess }}
          </div>
          <div
            v-if="passwordError"
            class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm"
          >
            {{ passwordError }}
          </div>

          <button
            type="submit"
            :disabled="authStore.isLoading"
            class="px-6 py-2.5 bg-gray-800 text-white font-semibold rounded-xl hover:bg-gray-900 disabled:opacity-60 disabled:cursor-not-allowed transition"
          >
            {{ authStore.isLoading ? 'Alterando...' : 'Alterar Senha' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import { getErrorMessage } from '@/utils/errorHandler';

const authStore = useAuthStore();

const userInitial = computed(() => {
  const name = authStore.user?.name ?? '';
  return name.charAt(0).toUpperCase() || '?';
});

// Profile form
const profileForm = reactive({
  name: authStore.user?.name ?? '',
  email: authStore.user?.email ?? '',
});
const errors = reactive<Record<string, string>>({});
const profileSuccess = ref('');
const profileError = ref('');

const saveProfile = async () => {
  Object.keys(errors).forEach((k) => delete errors[k]);
  profileSuccess.value = '';
  profileError.value = '';

  if (!profileForm.name.trim()) {
    errors.name = 'O nome é obrigatório.';
    return;
  }
  if (!profileForm.email.trim()) {
    errors.email = 'O e-mail é obrigatório.';
    return;
  }

  try {
    await authStore.updateProfile({
      name: profileForm.name,
      email: profileForm.email,
    });
    profileSuccess.value = 'Perfil atualizado com sucesso!';
  } catch (err) {
    profileError.value = getErrorMessage(err, 'Erro ao atualizar perfil');
  }
};

// Password form
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});
const passwordErrors = reactive<Record<string, string>>({});
const passwordSuccess = ref('');
const passwordError = ref('');

const changePassword = async () => {
  Object.keys(passwordErrors).forEach((k) => delete passwordErrors[k]);
  passwordSuccess.value = '';
  passwordError.value = '';

  if (!passwordForm.current_password) {
    passwordErrors.current_password = 'A senha atual é obrigatória.';
    return;
  }
  if (!passwordForm.password || passwordForm.password.length < 8) {
    passwordErrors.password = 'A nova senha deve ter no mínimo 8 caracteres.';
    return;
  }
  if (passwordForm.password !== passwordForm.password_confirmation) {
    passwordErrors.password_confirmation = 'As senhas não coincidem.';
    return;
  }

  try {
    await authStore.updateProfile({
      current_password: passwordForm.current_password,
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation,
    });
    passwordSuccess.value = 'Senha alterada com sucesso!';
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';
  } catch (err) {
    passwordError.value = getErrorMessage(err, 'Erro ao alterar senha');
  }
};
</script>
