import axios, { type AxiosInstance, type AxiosResponse, type AxiosRequestConfig } from 'axios';
import type { ApiResponse } from '@/types/auth';

class ApiClient {
    private client: AxiosInstance;

    constructor() {
        this.client = axios.create({
            baseURL: '/', // Use root to support both API and Auth routes
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest', // Critical for Laravel to return JSON errors
            },
            withCredentials: true, // Important: allows Laravel to handle session cookies
        });

        // Request interceptor to add CSRF token if not handled by cookie
        this.client.interceptors.request.use(
            (config) => {
                // Try to get CSRF token from meta tag
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (token && config.headers) {
                    config.headers['X-CSRF-TOKEN'] = token;
                }
                return config;
            },
            (error) => Promise.reject(error)
        );

        // Response interceptor for global error handling
        this.client.interceptors.response.use(
            (response) => response,
            (error) => {
                // Handle 401 Unauthorized globally if needed
                if (error.response && error.response.status === 401) {
                    console.warn('Unauthorized access - Session may have expired.');
                }
                return Promise.reject(error);
            }
        );
    }

    /**
     * Generic GET method
     */
    async get<T>(url: string, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
        const response: AxiosResponse<ApiResponse<T>> = await this.client.get(url, config);
        return response.data;
    }

    /**
     * Generic POST method
     */
    async post<T>(url: string, data?: any, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
        const response: AxiosResponse<ApiResponse<T>> = await this.client.post(url, data, config);
        return response.data;
    }

    /**
     * Generic PUT method
     */
    async put<T>(url: string, data?: any, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
        const response: AxiosResponse<ApiResponse<T>> = await this.client.put(url, data, config);
        return response.data;
    }

    /**
     * Generic DELETE method
     */
    async delete<T>(url: string, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
        const response: AxiosResponse<ApiResponse<T>> = await this.client.delete(url, config);
        return response.data;
    }

    /**
     * Special method for file uploads (FormData)
     */
    async postFormData<T>(url: string, formData: FormData): Promise<ApiResponse<T>> {
        const response: AxiosResponse<ApiResponse<T>> = await this.client.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        return response.data;
    }

    /**
     * Initialize CSRF protection (Sanctum)
     */
    async setupCsrf(): Promise<void> {
        await this.client.get('/sanctum/csrf-cookie');
    }
}

// Instancia singleton del cliente
export const apiClient = new ApiClient();