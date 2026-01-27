import type { ApiResponse } from '@/types/auth';
import { apiClient } from '../ApiClient';

// Tipos específicos para Theme
export type Theme = {
    id: number;
    name: string;
    description?: string;
    primary_color: string;
    secondary_color: string;
    bg_color: string;
    bg_image_url?: string;
    css_class: string;
    is_system: boolean;
    user_id?: number;
    created_at: string;
    updated_at: string;
};

export type CreateThemeData = {
    name: string;
    description?: string;
    primary_color: string;
    secondary_color: string;
    bg_color: string;
    bg_image_file?: File;
    bg_image_url?: string;
    css_class: string;
};

export type UpdateThemeData = Partial<CreateThemeData>;

export type ThemeUploadResponse = {
    theme: Theme;
    message: string;
};

export class ThemeService {
    /**
     * Obtener todos los temas disponibles (sistema + usuario)
     */
    async getAvailableThemes(): Promise<Theme[]> {
        const response = await apiClient.get<Theme[]>('/themes');
        return response.data || [];
    }

    /**
     * Obtener un tema específico por ID
     */
    async getTheme(id: number): Promise<Theme> {
        const response = await apiClient.get<Theme>(`/themes/${id}`);
        if (!response.data) {
            throw new Error('Tema no encontrado');
        }
        return response.data;
    }

    /**
     * Crear nuevo tema personalizado
     */
    async createTheme(data: CreateThemeData): Promise<Theme> {
        let response: ApiResponse<ThemeUploadResponse>;

        if (data.bg_image_file) {
            // Si hay archivo, usar FormData
            const formData = new FormData();
            formData.append('name', data.name);
            formData.append('primary_color', data.primary_color);
            formData.append('secondary_color', data.secondary_color);
            formData.append('bg_color', data.bg_color);
            formData.append('css_class', data.css_class);
            
            if (data.description) {
                formData.append('description', data.description);
            }
            
            formData.append('bg_image_file', data.bg_image_file);

            response = await apiClient.postFormData<ThemeUploadResponse>('/themes', formData);
        } else {
            // Sin archivo, usar JSON normal
            response = await apiClient.post<ThemeUploadResponse>('/themes', data);
        }

        if (!response.data?.theme) {
            throw new Error('Error creando el tema');
        }

        return response.data.theme;
    }

    /**
     * Actualizar tema existente
     */
    async updateTheme(id: number, data: UpdateThemeData): Promise<Theme> {
        let response: ApiResponse<ThemeUploadResponse>;

        if (data.bg_image_file) {
            // Si hay archivo, usar FormData
            const formData = new FormData();
            
            Object.entries(data).forEach(([key, value]) => {
                if (key === 'bg_image_file' && value instanceof File) {
                    formData.append(key, value);
                } else if (value !== undefined && value !== null) {
                    formData.append(key, String(value));
                }
            });

            response = await apiClient.postFormData<ThemeUploadResponse>(`/themes/${id}`, formData);
        } else {
            // Sin archivo, usar JSON normal
            response = await apiClient.put<ThemeUploadResponse>(`/themes/${id}`, data);
        }

        if (!response.data?.theme) {
            throw new Error('Error actualizando el tema');
        }

        return response.data.theme;
    }

    /**
     * Eliminar tema personalizado
     */
    async deleteTheme(id: number): Promise<void> {
        await apiClient.delete(`/themes/${id}`);
    }

    /**
     * Filtrar temas del sistema
     */
    getSystemThemes(themes: Theme[]): Theme[] {
        return themes.filter(theme => theme.is_system);
    }

    /**
     * Filtrar temas del usuario
     */
    getUserThemes(themes: Theme[]): Theme[] {
        return themes.filter(theme => !theme.is_system);
    }

    /**
     * Validar colores hex
     */
    isValidHexColor(color: string): boolean {
        return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color);
    }

    /**
     * Validar datos del tema
     */
    validateThemeData(data: CreateThemeData | UpdateThemeData): { valid: boolean; errors: string[] } {
        const errors: string[] = [];

        if ('name' in data && data.name && data.name.length > 100) {
            errors.push('El nombre del tema no puede exceder 100 caracteres');
        }

        if ('primary_color' in data && data.primary_color && !this.isValidHexColor(data.primary_color)) {
            errors.push('Color primario debe ser un color hex válido (#RRGGBB)');
        }

        if ('secondary_color' in data && data.secondary_color && !this.isValidHexColor(data.secondary_color)) {
            errors.push('Color secundario debe ser un color hex válido (#RRGGBB)');
        }

        if ('bg_color' in data && data.bg_color && !this.isValidHexColor(data.bg_color)) {
            errors.push('Color de fondo debe ser un color hex válido (#RRGGBB)');
        }

        if ('css_class' in data && data.css_class && data.css_class.length > 100) {
            errors.push('La clase CSS no puede exceder 100 caracteres');
        }

        return {
            valid: errors.length === 0,
            errors
        };
    }

    /**
     * Aplicar tema a elementos del DOM
     */
    applyTheme(theme: Theme, selector: string = ':root'): void {
        const element = document.querySelector(selector) as HTMLElement;
        if (!element) return;

        element.style.setProperty('--primary-color', theme.primary_color);
        element.style.setProperty('--secondary-color', theme.secondary_color);
        element.style.setProperty('--bg-color', theme.bg_color);

        if (theme.bg_image_url) {
            element.style.setProperty('--bg-image', `url(${theme.bg_image_url})`);
        }

        if (theme.css_class) {
            element.className = `${element.className} ${theme.css_class}`.trim();
        }
    }
}

// Instancia singleton del servicio
export const themeService = new ThemeService();