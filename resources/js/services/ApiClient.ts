import axios, { type AxiosInstance, type AxiosResponse } from 'axios';
import type { ApiResponse } from '@/types/auth';

class ApiClient {
    private client: AxiosInstance;

    constructor() {
        this.client = axios.create({
            baseURL: '/api',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            withCredentials: true,
        });

        // Request interceptor para agregar token
        this.client.interceptors.request.use((config) => {
            const token = this.getAuthToken();
            if (token) {
                config.headers.Authorization = `Bearer ${token}`;
            }
            return config;
        });

        // Response interceptor para manejar errores globales
        this.client.interceptors.response.use(
            (response) => response,
            (error) => {
                if (error.response?.status === 401) {
                    this.clearAuthToken();
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

    // Manejo de tokens
    setAuthToken(token: string): void {
        localStorage.setItem('auth_token', token);
    }

    getAuthToken(): string | null {
        return localStorage.getItem('auth_token');
    }

    clearAuthToken(): void {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
    }

    // CSRF token setup (para Sanctum)
    async setupCsrf(): Promise<void> {
        await this.client.get('/sanctum/csrf-cookie');
    }
}

// Instancia singleton del cliente
export const apiClient = new ApiClient();