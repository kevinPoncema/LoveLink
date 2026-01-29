import { ref } from 'vue';
import type { Landing, CreateLandingData, UpdateLandingData } from '@/services/landing/LandingService';
import { landingService } from '@/services/landing/LandingService';

export function useLandings() {
    const landings = ref<Landing[]>([]);
    const landing = ref<Landing | null>(null);
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    // Cargar todas las landings
    const loadLandings = async () => {
        isLoading.value = true;
        error.value = null;
        try {
            landings.value = await landingService.getUserLandings();
        } catch (e: any) {
            error.value = e.message || 'Error al cargar las landings';
        } finally {
            isLoading.value = false;
        }
    };

    // Cargar una landing por ID
    const loadLanding = async (id: number) => {
        isLoading.value = true;
        error.value = null;
        try {
            landing.value = await landingService.getLanding(id);
        } catch (e: any) {
            error.value = e.message || 'Error al cargar la landing';
        } finally {
            isLoading.value = false;
        }
    };

    // Cargar una landing pública por slug
    const loadPublicLanding = async (slug: string) => {
        isLoading.value = true;
        error.value = null;
        try {
            landing.value = await landingService.getPublicLanding(slug);
        } catch (e: any) {
            error.value = e.message || 'Error al cargar la landing pública';
        } finally {
            isLoading.value = false;
        }
    };

    // Crear nueva landing
    const createLanding = async (data: CreateLandingData) => {
        isLoading.value = true;
        error.value = null;
        try {
            const newLanding = await landingService.createLanding(data);
            landings.value.push(newLanding);
            return newLanding;
        } catch (e: any) {
            error.value = e.message || 'Error al crear la landing';
            throw e;
        } finally {
            isLoading.value = false;
        }
    };

    // Actualizar landing
    const updateLanding = async (id: number, data: UpdateLandingData) => {
        isLoading.value = true;
        error.value = null;
        try {
            const updated = await landingService.updateLanding(id, data);
            const index = landings.value.findIndex(i => i.id === id);
            if (index !== -1) {
                landings.value[index] = updated;
            }
            if (landing.value?.id === id) {
                landing.value = updated;
            }
            return updated;
        } catch (e: any) {
            error.value = e.message || 'Error al actualizar la landing';
            throw e;
        } finally {
            isLoading.value = false;
        }
    };

    // Eliminar landing
    const deleteLanding = async (id: number) => {
        isLoading.value = true;
        error.value = null;
        try {
            await landingService.deleteLanding(id);
            landings.value = landings.value.filter(i => i.id !== id);
        } catch (e: any) {
            error.value = e.message || 'Error al eliminar la landing';
            throw e;
        } finally {
            isLoading.value = false;
        }
    };

    return {
        landings,
        landing,
        isLoading,
        error,
        loadLandings,
        loadLanding,
        loadPublicLanding,
        createLanding,
        updateLanding,
        deleteLanding
    };
}
