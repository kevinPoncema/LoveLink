import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { apiClient } from '@/services/ApiClient';

const isInitialized = ref(false);

export function useApiAuth() {
    const page = usePage();
    
    const user = computed(() => page.props.auth?.user || null);
    const isAuthenticated = computed(() => !!user.value);

    // Inicializar token de API automáticamente si el usuario está autenticado
    const initializeApiAuth = async () => {
        if (isInitialized.value) return;
        
        if (isAuthenticated.value && !apiClient.getToken()) {
            try {
                console.log('[DEBUG] Creando token de API para usuario autenticado');
                await apiClient.createApiToken();
                console.log('[DEBUG] Token de API creado exitosamente');
            } catch (error) {
                console.error('[DEBUG] Error al crear token de API:', error);
            }
        }
        
        isInitialized.value = true;
    };

    // Limpiar token cuando el usuario se desloguea
    const clearApiAuth = () => {
        apiClient.clearToken();
        isInitialized.value = false;
    };

    return {
        user,
        isAuthenticated,
        initializeApiAuth,
        clearApiAuth,
        getToken: () => apiClient.getToken()
    };
}