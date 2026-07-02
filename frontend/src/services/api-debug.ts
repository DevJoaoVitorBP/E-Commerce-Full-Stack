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

    // Se for FormData, NÃO definir Content-Type (deixar axios/navegador detectar)
    if (!(config.data instanceof FormData)) {
      config.headers['Content-Type'] = 'application/json';
    }

    // DEBUG
    console.log('🔍 REQUEST CONFIG:', {
      method: config.method,
      url: config.url,
      hasFormData: config.data instanceof FormData,
      contentType: config.headers['Content-Type'],
      dataKeys:
        config.data instanceof FormData
          ? Array.from((config.data as FormData).entries())
          : Object.keys(config.data || {}),
      fullData: config.data,
    });

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor para tratar respostas
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    // Se for erro 401, limpar token e redirecionar para login
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }

    // Se for erro 403, redirecionar para home
    if (error.response?.status === 403) {
      window.location.href = '/';
    }

    return Promise.reject(error);
  }
);

export default api;
