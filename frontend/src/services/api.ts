import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
  },
});

// Interceptor para adicionar token em requisições
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Se for FormData, configurar corretamente
    if (config.data instanceof FormData) {
      // Deixar navegador/Axios detectar Content-Type automaticamente para FormData
      // NÃO tocar em Content-Type permite que o navegador configure multipart/form-data
      delete config.headers['Content-Type'];
      // Desabilitar transformação de dados para FormData
      config.transformRequest = [(data) => data];
    } else if (!config.headers['Content-Type']) {
      config.headers['Content-Type'] = 'application/json';
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor para tratar respostas
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const isLoginRequest = error.config?.url?.includes('/auth/login');

    if (error.response?.status === 401 && !isLoginRequest) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }

    if (error.response?.status === 403 && !isLoginRequest) {
      window.location.href = '/';
    }

    return Promise.reject(error);
  }
);

export default api;
