import type { ApiResponse } from '@/types/auth';
import { apiClient } from '../ApiClient';

// Tipos específicos para Invitation
export type Invitation = {
    id: number;
    title: string;
    slug: string;
    yes_message: string;
    no_messages: string[];
    is_published: boolean;
    user_id: number;
    created_at: string;
    updated_at: string;
    deleted_at?: string;
    media?: InvitationMedia[];
};

export type InvitationMedia = {
    id: number;
    filename: string;
    path: string;
    mime_type: string;
    size: number;
};

export type CreateInvitationData = {
    title: string;
    slug?: string;
    yes_message?: string;
    no_messages?: string[];
};

export type UpdateInvitationData = Partial<CreateInvitationData> & {
    is_published?: boolean;
};

export class InvitationService {
    /**
     * Obtener todas las invitations del usuario
     */
    async getUserInvitations(): Promise<Invitation[]> {
        const response = await apiClient.get<Invitation[]>('/api/invitations');
        return response.data || [];
    }

    /**
     * Obtener una invitation específica por ID
     */
    async getInvitation(id: number): Promise<Invitation> {
        const response = await apiClient.get<Invitation>(`/api/invitations/${id}`);
        if (!response.data) {
            throw new Error('Invitation no encontrada');
        }
        return response.data;
    }

    /**
     * Obtener invitation pública por slug (sin autenticación)
     */
    async getPublicInvitation(slug: string): Promise<Invitation> {
        const response = await apiClient.get<Invitation>(`/api/public/invitation/${slug}`);
        if (!response.data) {
            throw new Error('Invitation no encontrada');
        }
        return response.data;
    }

    /**
     * Crear nueva invitation
     */
    async createInvitation(data: CreateInvitationData): Promise<Invitation> {
        const response = await apiClient.post<Invitation>('/api/invitations', data);
        if (!response.data) {
            throw new Error('Error creando la invitation');
        }
        return response.data;
    }

    /**
     * Actualizar invitation existente
     */
    async updateInvitation(id: number, data: UpdateInvitationData): Promise<Invitation> {
        const response = await apiClient.put<Invitation>(`/api/invitations/${id}`, data);
        if (!response.data) {
            throw new Error('Error actualizando la invitation');
        }
        return response.data;
    }

    /**
     * Eliminar invitation (soft delete)
     */
    async deleteInvitation(id: number): Promise<void> {
        await apiClient.delete(`/api/invitations/${id}`);
    }

    /**
     * Vincular media a invitation
     */
    async attachMedia(invitationId: number, mediaId: number): Promise<void> {
        await apiClient.post(`/api/invitations/${invitationId}/media`, {
            media_id: mediaId,
        });
    }

    /**
     * Desvincular media de invitation
     */
    async detachMedia(invitationId: number, mediaId: number): Promise<void> {
        await apiClient.delete(`/api/invitations/${invitationId}/media/${mediaId}`);
    }

    /**
     * Publicar/despublicar invitation
     */
    async togglePublish(id: number, isPublished: boolean): Promise<Invitation> {
        return this.updateInvitation(id, { is_published: isPublished });
    }
}

// Instancia singleton del servicio
export const invitationService = new InvitationService();
