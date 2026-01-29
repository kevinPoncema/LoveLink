import { ref, computed, type Ref } from 'vue';
import type { Invitation, CreateInvitationData, UpdateInvitationData, InvitationMedia } from '@/services/invitation/InvitationService';
import { invitationService } from '@/services/invitation/InvitationService';

export function useInvitations() {
    const invitations = ref<Invitation[]>([]);
    const invitation = ref<Invitation | null>(null);
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    // Cargar todas las invitaciones
    const loadInvitations = async () => {
        isLoading.value = true;
        error.value = null;
        try {
            invitations.value = await invitationService.getUserInvitations();
        } catch (e: any) {
            error.value = e.message || 'Error al cargar las invitaciones';
        } finally {
            isLoading.value = false;
        }
    };

    // Cargar una invitación por ID
    const loadInvitation = async (id: number) => {
        isLoading.value = true;
        error.value = null;
        try {
            invitation.value = await invitationService.getInvitation(id);
        } catch (e: any) {
            error.value = e.message || 'Error al cargar la invitación';
        } finally {
            isLoading.value = false;
        }
    };

    // Cargar una invitación pública por slug
    const loadPublicInvitation = async (slug: string) => {
        isLoading.value = true;
        error.value = null;
        try {
            invitation.value = await invitationService.getPublicInvitation(slug);
        } catch (e: any) {
            error.value = e.message || 'Error al cargar la invitación pública';
        } finally {
            isLoading.value = false;
        }
    };

    // Crear nueva invitación
    const createInvitation = async (data: CreateInvitationData) => {
        isLoading.value = true;
        error.value = null;
        try {
            const newInvitation = await invitationService.createInvitation(data);
            invitations.value.push(newInvitation);
            return newInvitation;
        } catch (e: any) {
            error.value = e.message || 'Error al crear la invitación';
            throw e;
        } finally {
            isLoading.value = false;
        }
    };

    // Actualizar invitación
    const updateInvitation = async (id: number, data: UpdateInvitationData) => {
        isLoading.value = true;
        error.value = null;
        try {
            const updated = await invitationService.updateInvitation(id, data);
            const index = invitations.value.findIndex(i => i.id === id);
            if (index !== -1) {
                invitations.value[index] = updated;
            }
            if (invitation.value?.id === id) {
                invitation.value = updated;
            }
            return updated;
        } catch (e: any) {
            error.value = e.message || 'Error al actualizar la invitación';
            throw e;
        } finally {
            isLoading.value = false;
        }
    };

    // Eliminar invitación
    const deleteInvitation = async (id: number) => {
        isLoading.value = true;
        error.value = null;
        try {
            await invitationService.deleteInvitation(id);
            invitations.value = invitations.value.filter(i => i.id !== id);
        } catch (e: any) {
            error.value = e.message || 'Error al eliminar la invitación';
            throw e;
        } finally {
            isLoading.value = false;
        }
    };

    // Vincular media
    const attachMedia = async (invitationId: number, mediaId: number) => {
        isLoading.value = true;
        error.value = null;
        try {
            await invitationService.attachMedia(invitationId, mediaId);
            // Recargar invitación para ver el cambio
            if (invitation.value && invitation.value.id === invitationId) {
                await loadInvitation(invitationId);
            }
        } catch (e: any) {
            error.value = e.message || 'Error al vincular media';
            throw e;
        } finally {
            isLoading.value = false;
        }
    };

    // Desvincular media
    const detachMedia = async (invitationId: number, mediaId: number) => {
        isLoading.value = true;
        error.value = null;
        try {
            await invitationService.detachMedia(invitationId, mediaId);
            // Recargar invitación para ver el cambio
            if (invitation.value && invitation.value.id === invitationId) {
                await loadInvitation(invitationId);
            }
        } catch (e: any) {
            error.value = e.message || 'Error al desvincular media';
            throw e;
        } finally {
            isLoading.value = false;
        }
    };

    const clearError = () => {
        error.value = null;
    };

    return {
        invitations,
        invitation,
        isLoading,
        error,
        loadInvitations,
        loadInvitation,
        loadPublicInvitation,
        createInvitation,
        updateInvitation,
        deleteInvitation,
        attachMedia,
        detachMedia,
        clearError
    };
}
