import { ref, computed, type ComputedRef, type Ref } from 'vue';
import type { Theme } from '@/types/auth';
import { themeService } from '@/services';

export type UseThemesReturn = {
    // Estado reactivo
    themes: Ref<Theme[]>;
    currentTheme: Ref<Theme | null>;
    isLoading: Ref<boolean>;
    error: Ref<string | null>;
    
    // Computed
    systemThemes: ComputedRef<Theme[]>;
    userThemes: ComputedRef<Theme[]>;
    canCreateMore: ComputedRef<boolean>;
    
    // Métodos
    loadThemes: () => Promise<void>;
    getTheme: (id: number) => Promise<Theme>;
    createTheme: (data: any) => Promise<Theme>;
    updateTheme: (id: number, data: any) => Promise<Theme>;
    deleteTheme: (id: number) => Promise<void>;
    setCurrentTheme: (theme: Theme) => void;
    clearError: () => void;
};

// Estado global compartido
const themes = ref<Theme[]>([]);
const currentTheme = ref<Theme | null>(null);
const isLoading = ref<boolean>(false);
const error = ref<string | null>(null);

// Computed properties
const systemThemes = computed(() => {
    return themes.value.filter(theme => theme.is_system);
});

const userThemes = computed(() => {
    return themes.value.filter(theme => !theme.is_system);
});

const canCreateMore = computed(() => {
    // Límite de 10 temas personalizados por usuario
    return userThemes.value.length < 10;
});

export function useThemes(): UseThemesReturn {
    
    /**
     * Cargar todos los temas disponibles
     */
    const loadThemes = async (): Promise<void> => {
        try {
            isLoading.value = true;
            error.value = null;
            
            themes.value = await themeService.getAvailableThemes();
            
        } catch (err: any) {
            error.value = err.message || 'Error cargando temas';
            console.error('Load themes error:', err);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Obtener un tema específico
     */
    const getTheme = async (id: number): Promise<Theme> => {
        try {
            isLoading.value = true;
            error.value = null;
            
            const theme = await themeService.getTheme(id);
            
            // Actualizar en la lista local si existe
            const index = themes.value.findIndex(t => t.id === id);
            if (index !== -1) {
                themes.value[index] = theme;
            }
            
            return theme;
            
        } catch (err: any) {
            error.value = err.message || 'Error cargando tema';
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Crear nuevo tema personalizado
     */
    const createTheme = async (data: any): Promise<Theme> => {
        try {
            isLoading.value = true;
            error.value = null;
            
            const newTheme = await themeService.createTheme(data);
            
            // Añadir a la lista local
            themes.value.push(newTheme);
            
            return newTheme;
            
        } catch (err: any) {
            error.value = err.message || 'Error creando tema';
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Actualizar tema existente
     */
    const updateTheme = async (id: number, data: any): Promise<Theme> => {
        try {
            isLoading.value = true;
            error.value = null;
            
            const updatedTheme = await themeService.updateTheme(id, data);
            
            // Actualizar en la lista local
            const index = themes.value.findIndex(t => t.id === id);
            if (index !== -1) {
                themes.value[index] = updatedTheme;
            }
            
            // Actualizar tema actual si es el mismo
            if (currentTheme.value?.id === id) {
                currentTheme.value = updatedTheme;
            }
            
            return updatedTheme;
            
        } catch (err: any) {
            error.value = err.message || 'Error actualizando tema';
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Eliminar tema
     */
    const deleteTheme = async (id: number): Promise<void> => {
        try {
            isLoading.value = true;
            error.value = null;
            
            await themeService.deleteTheme(id);
            
            // Remover de la lista local
            themes.value = themes.value.filter(t => t.id !== id);
            
            // Limpiar tema actual si era el eliminado
            if (currentTheme.value?.id === id) {
                currentTheme.value = null;
            }
            
        } catch (err: any) {
            error.value = err.message || 'Error eliminando tema';
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Establecer tema actual
     */
    const setCurrentTheme = (theme: Theme): void => {
        currentTheme.value = theme;
    };

    /**
     * Limpiar errores
     */
    const clearError = (): void => {
        error.value = null;
    };

    return {
        // Estado reactivo
        themes,
        currentTheme,
        isLoading,
        error,
        
        // Computed
        systemThemes,
        userThemes,
        canCreateMore,
        
        // Métodos
        loadThemes,
        getTheme,
        createTheme,
        updateTheme,
        deleteTheme,
        setCurrentTheme,
        clearError,
    };
}