import { ref, computed, type ComputedRef, type Ref } from 'vue';
import type { DashboardStats } from '@/types/auth';
import { landingService, invitationService, mediaService } from '@/services';

export type UseDashboardReturn = {
    // Estado reactivo
    stats: Ref<DashboardStats>;
    isLoading: Ref<boolean>;
    error: Ref<string | null>;
    
    // Computed
    hasData: ComputedRef<boolean>;
    totalItems: ComputedRef<number>;
    
    // Métodos
    loadStats: () => Promise<void>;
    refreshStats: () => Promise<void>;
    clearError: () => void;
};

export function useDashboard(): UseDashboardReturn {
    // Estado reactivo
    const stats = ref<DashboardStats>({
        landings: 0,
        invitations: 0,
        media: 0,
    });
    
    const isLoading = ref<boolean>(false);
    const error = ref<string | null>(null);

    // Computed properties
    const hasData = computed(() => {
        return stats.value.landings > 0 || stats.value.invitations > 0 || stats.value.media > 0;
    });

    const totalItems = computed(() => {
        return stats.value.landings + stats.value.invitations + stats.value.media;
    });

    // Métodos
    const loadStats = async (): Promise<void> => {
        try {
            isLoading.value = true;
            error.value = null;

            // Ejecutar todas las llamadas en paralelo
            const [landings, invitations, media] = await Promise.allSettled([
                landingService.getUserLandings(),
                invitationService.getUserInvitations(),
                mediaService.getUserMedia(),
            ]);

            // Procesar resultados
            stats.value = {
                landings: landings.status === 'fulfilled' ? landings.value.length : 0,
                invitations: invitations.status === 'fulfilled' ? invitations.value.length : 0,
                media: media.status === 'fulfilled' ? media.value.length : 0,
            };

            // Log de errores individuales sin romper la carga
            if (landings.status === 'rejected') {
                console.warn('Error loading landings:', landings.reason);
            }
            if (invitations.status === 'rejected') {
                console.warn('Error loading invitations:', invitations.reason);
            }
            if (media.status === 'rejected') {
                console.warn('Error loading media:', media.reason);
            }

        } catch (err: any) {
            error.value = err.message || 'Error cargando las estadísticas del dashboard';
            console.error('Dashboard stats error:', err);
            
            // Mantener stats en 0 si hay error
            stats.value = {
                landings: 0,
                invitations: 0,
                media: 0,
            };
        } finally {
            isLoading.value = false;
        }
    };

    const refreshStats = async (): Promise<void> => {
        await loadStats();
    };

    const clearError = (): void => {
        error.value = null;
    };

    return {
        // Estado reactivo
        stats,
        isLoading,
        error,
        
        // Computed
        hasData,
        totalItems,
        
        // Métodos
        loadStats,
        refreshStats,
        clearError,
    };
}