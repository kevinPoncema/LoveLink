import axios, { type AxiosInstance, type AxiosResponse } from 'axios';
import type { ApiResponse } from '@/types/auth';

class ApiClient {
    private client: AxiosInstance;

    constructor() {
        this.client = axios.create({
            baseURL: '/', // Cambio: usar raíz en lugar de /api para rutas de Fortify
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            withCredentials: true, // Importante: para que Laravel mantenga las cookies de sesión
        });

        // Request interceptor para agregar CSRF token
        this.client.interceptors.request.use(
            (config) => {
                // Obtener CSRF token desde meta tag o cookie
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (token) {
                    config.headers['X-CSRF-TOKEN'] = token;
                }

                console.log('[DEBUG] Petición API:', config.method?.toUpperCase(), config.url);

                return config;
            },
            (error) => Promise.reject(error)
        );

        // Response interceptor para manejar errores globales
        this.client.interceptors.response.use(
            (response) => {
                console.log('[DEBUG] Respuesta API:', response.status, response.config?.method?.toUpperCase(), response.config?.url);
                return response;
            },
            (error) => {
                console.error('[DEBUG] Error API:', error.response?.status, error.response?.config?.method?.toUpperCase(), error.response?.config?.url, error.message);
                
                if (error.response?.status === 401) {
                    // Redirigir al login si no está ya ahí
                    if (!window.location.pathname.includes('/login')) {
                        window.location.href = '/login';
                    }
                }
                return Promise.reject(error);
            }
        );
    }

    // Métodos HTTP básicos
    async get<T>(url: string): Promise<ApiResponse<T>> {
        const response: AxiosResponse<ApiResponse<T>> = await this.client.get(url);
        return response.data;
    }

    async post<T>(url: string, data?: any): Promise<ApiResponse<T>> {
        const response: AxiosResponse<ApiResponse<T>> = await this.client.post(url, data);
        return response.data;
    }

    async put<T>(url: string, data?: any): Promise<ApiResponse<T>> {
        const response: AxiosResponse<ApiResponse<T>> = await this.client.put(url, data);
        return response.data;
    }

    async delete<T>(url: string): Promise<ApiResponse<T>> {
        const response: AxiosResponse<ApiResponse<T>> = await this.client.delete(url);
        return response.data;
    }

    // Método especial para upload de archivos
    async postFormData<T>(url: string, formData: FormData): Promise<ApiResponse<T>> {
        const response: AxiosResponse<ApiResponse<T>> = await this.client.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        return response.data;
    }

    // CSRF token setup (para Sanctum)
    async setupCsrf(): Promise<void> {
        await this.client.get('/sanctum/csrf-cookie');
    }
}

// Instancia singleton del cliente
export const apiClient = new ApiClient();