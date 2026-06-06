import axios from 'axios';

const api = axios.create({
    // Sesuaikan dengan URL server Laravel Anda
    baseURL: 'http://localhost:8000/api/v1',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// Interceptor: Otomatis menyisipkan Token Sanctum di setiap request jika user sudah login
api.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default api;