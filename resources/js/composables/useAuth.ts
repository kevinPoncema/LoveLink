import { ref, computed, type ComputedRef, type Ref } from 'vue';
import { router } from '@inertiajs/vue3';
import type { User, LoginData, RegisterData, AuthResponse } from '@/types/auth';
import { authService } from '@/services/auth/AuthService';

export type UseAuthReturn = {
    // Estado reactivo
    user: Ref<User | null>;
    isAuthenticated: ComputedRef<boolean>;
    isLoading: Ref<boolean>;
    error: Ref<string | null>;
    
    // Métodos
    login: (credentials: LoginData) => Promise<void>;
    register: (userData: RegisterData) => Promise<void>;
    logout: () => Promise<void>;
    checkAuth: () => Promise<void>;
    clearError: () => void;
};

// Estado global compartido
const user = ref<User | null>(null);
const isLoading = ref<boolean>(false);
const error = ref<string | null>(null);

// Computed properties
const isAuthenticated = computed(() => !!user.value);

export function useAuth(): UseAuthReturn {
    
    /**
     * Login del usuario
     */
    const login = async (credentials: LoginData): Promise<void> => {
        try {
            isLoading.value = true;
            error.value = null;
            
            const response: AuthResponse = await authService.login(credentials);
            user.value = response.user;
            
            // Fortify maneja la redirección automáticamente después del login exitoso
            // No necesitamos hacer redirección manual
            
        } catch (err: any) {
            error.value = err.response?.data?.message || err.message || 'Error en el login';
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Registro de nuevo usuario
     */
    const register = async (userData: RegisterData): Promise<void> => {
        try {
            isLoading.value = true;
            error.value = null;
            
            const response: AuthResponse = await authService.register(userData);
            user.value = response.user;
            
            // Fortify maneja la redirección automáticamente después del registro exitoso
            // No necesitamos hacer redirección manual
            
        } catch (err: any) {
            error.value = err.response?.data?.message || err.message || 'Error en el registro';
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Logout del usuario
     */
    const logout = async (): Promise<void> => {
        try {
            isLoading.value = true;
            error.value = null;
            
            await authService.logout();
            user.value = null;
            
            // Fortify maneja la redirección automáticamente después del logout
            // No necesitamos hacer redirección manual
            
        } catch (err: any) {
            // Incluso si falla la llamada al servidor, limpiamos el estado local
            user.value = null;
            console.error('Error en logout:', err);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Verificar autenticación y obtener usuario actual
     */
    const checkAuth = async (): Promise<void> => {
        try {
            // Primero verificar si hay token almacenado
            if (!authService.isAuthenticated()) {
                user.value = null;
                return;
            }

            // Si hay token, obtener usuario del servidor
            isLoading.value = true;
            const userData = await authService.getUser();
            user.value = userData;
            
        } catch (err: any) {
            // Si falla la verificación, limpiar estado
            user.value = null;
            console.error('Error verificando autenticación:', err);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Limpiar errores
     */
    const clearError = (): void => {
        error.value = null;
    };

    // Inicializar el estado del usuario si hay datos almacenados
    if (!user.value) {
        const storedUser = authService.getStoredUser();
        if (storedUser) {
            user.value = storedUser;
        }
    }

    return {
        // Estado reactivo
        user,
        isAuthenticated,
        isLoading,
        error,
        
        // Métodos
        login,
        register,
        logout,
        checkAuth,
        clearError,
    };
}