import type { ApiResponse } from '@/types/auth';
import { apiClient } from '../ApiClient';

// Tipos específicos para Landing
export type Landing = {
    id: number;
    couple_names: string;
    slug: string;
    anniversary_date: string;
    theme_id: number;
    bio_text?: string;
    user_id: number;
    created_at: string;
    updated_at: string;
    media?: Media[];
    theme?: Theme;
};

export type CreateLandingData = {
    couple_names: string;
    slug?: string;
    anniversary_date: string;
    theme_id: number;
    bio_text?: string;
};

export type UpdateLandingData = Partial<CreateLandingData>;

// Tipos compartidos (se definirán mejor en tipos separados después)
type Media = {
    id: number;
    filename: string;
    path: string;
    mime_type: string;
    size: number;
    pivot?: {
        sort_order: number;
    };
};

type Theme = {
    id: number;
    name: string;
    primary_color: string;
    secondary_color: string;
    bg_color: string;
    bg_image_url?: string;
};

export class LandingService {
    /**
     * Obtener todas las landings del usuario
     */
    async getUserLandings(): Promise<Landing[]> {
        const response = await apiClient.get<Landing[]>('/landings');
        return response.data || [];
    }

    /**
     * Obtener una landing específica por ID
     */
    async getLanding(id: number): Promise<Landing> {
        const response = await apiClient.get<Landing>(`/landings/${id}`);
        if (!response.data) {
            throw new Error('Landing no encontrada');
        }
        return response.data;
    }

    /**
     * Obtener landing pública por slug (sin autenticación)
     */
    async getPublicLanding(slug: string): Promise<Landing> {
        const response = await apiClient.get<Landing>(`/public/landing/${slug}`);
        if (!response.data) {
            throw new Error('Landing no encontrada');
        }
        return response.data;
    }

    /**
     * Crear nueva landing
     */
    async createLanding(data: CreateLandingData): Promise<Landing> {
        const response = await apiClient.post<Landing>('/landings', data);
        if (!response.data) {
            throw new Error('Error creando la landing');
        }
        return response.data;
    }

    /**
     * Actualizar landing existente
     */
    async updateLanding(id: number, data: UpdateLandingData): Promise<Landing> {
        const response = await apiClient.put<Landing>(`/landings/${id}`, data);
        if (!response.data) {
            throw new Error('Error actualizando la landing');
        }
        return response.data;
    }

    /**
     * Eliminar landing
     */
    async deleteLanding(id: number): Promise<void> {
        await apiClient.delete(`/landings/${id}`);
    }

    /**
     * Vincular media a landing
     */
    async attachMedia(landingId: number, mediaId: number, sortOrder?: number): Promise<void> {
        await apiClient.post(`/landings/${landingId}/media`, {
            media_id: mediaId,
            sort_order: sortOrder,
        });
    }

    /**
     * Desvincular media de landing
     */
    async detachMedia(landingId: number, mediaId: number): Promise<void> {
        await apiClient.delete(`/landings/${landingId}/media/${mediaId}`);
    }

    /**
     * Reordenar media en landing
     */
    async reorderMedia(landingId: number, mediaOrder: { media_id: number; sort_order: number }[]): Promise<void> {
        await apiClient.put(`/landings/${landingId}/media/reorder`, {
            media_order: mediaOrder,
        });
    }
}

// Instancia singleton del servicio
export const landingService = new LandingService();