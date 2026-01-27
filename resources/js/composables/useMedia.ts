import { ref, computed, type ComputedRef, type Ref } from 'vue';
import type { Media } from '@/types/auth';
import { mediaService } from '@/services';

export type UseMediaReturn = {
    // Estado reactivo
    media: Ref<Media[]>;
    isLoading: Ref<boolean>;
    isUploading: Ref<boolean>;
    error: Ref<string | null>;
    uploadProgress: Ref<number>;
    
    // Computed
    images: ComputedRef<Media[]>;
    totalSize: ComputedRef<number>;
    totalSizeFormatted: ComputedRef<string>;
    
    // Métodos
    loadMedia: () => Promise<void>;
    uploadMedia: (file: File) => Promise<Media>;
    uploadMultipleMedia: (files: FileList | File[]) => Promise<Media[]>;
    deleteMedia: (id: number) => Promise<void>;
    validateFile: (file: File) => { valid: boolean; error?: string };
    formatFileSize: (bytes: number) => string;
    clearError: () => void;
};

// Estado global compartido
const media = ref<Media[]>([]);
const isLoading = ref<boolean>(false);
const isUploading = ref<boolean>(false);
const error = ref<string | null>(null);
const uploadProgress = ref<number>(0);

// Computed properties
const images = computed(() => {
    return media.value.filter(m => m.mime_type.startsWith('image/'));
});

const totalSize = computed(() => {
    return media.value.reduce((total, m) => total + m.size_bytes, 0);
});

const totalSizeFormatted = computed(() => {
    return mediaService.formatFileSize(totalSize.value);
});

export function useMedia(): UseMediaReturn {
    
    /**
     * Cargar toda la media del usuario
     */
    const loadMedia = async (): Promise<void> => {
        try {
            isLoading.value = true;
            error.value = null;
            
            media.value = await mediaService.getUserMedia();
            
        } catch (err: any) {
            error.value = err.message || 'Error cargando archivos';
            console.error('Load media error:', err);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Subir un archivo
     */
    const uploadMedia = async (file: File): Promise<Media> => {
        try {
            isUploading.value = true;
            uploadProgress.value = 0;
            error.value = null;
            
            const newMedia = await mediaService.uploadMedia(file);
            
            // Añadir a la lista local
            media.value.unshift(newMedia);
            
            uploadProgress.value = 100;
            
            return newMedia;
            
        } catch (err: any) {
            error.value = err.message || 'Error subiendo archivo';
            throw err;
        } finally {
            isUploading.value = false;
            uploadProgress.value = 0;
        }
    };

    /**
     * Subir múltiples archivos
     */
    const uploadMultipleMedia = async (files: FileList | File[]): Promise<Media[]> => {
        const fileArray = Array.from(files);
        const results: Media[] = [];
        
        try {
            isUploading.value = true;
            error.value = null;
            
            for (let i = 0; i < fileArray.length; i++) {
                const file = fileArray[i];
                uploadProgress.value = Math.round((i / fileArray.length) * 100);
                
                try {
                    const newMedia = await mediaService.uploadMedia(file);
                    media.value.unshift(newMedia);
                    results.push(newMedia);
                } catch (err: any) {
                    console.error(`Error subiendo ${file.name}:`, err);
                    // Continuar con los demás archivos
                }
            }
            
            uploadProgress.value = 100;
            
            if (results.length < fileArray.length) {
                error.value = `Se subieron ${results.length} de ${fileArray.length} archivos`;
            }
            
            return results;
            
        } catch (err: any) {
            error.value = err.message || 'Error subiendo archivos';
            throw err;
        } finally {
            isUploading.value = false;
            uploadProgress.value = 0;
        }
    };

    /**
     * Eliminar media
     */
    const deleteMedia = async (id: number): Promise<void> => {
        try {
            isLoading.value = true;
            error.value = null;
            
            await mediaService.deleteMedia(id);
            
            // Remover de la lista local
            media.value = media.value.filter(m => m.id !== id);
            
        } catch (err: any) {
            error.value = err.message || 'Error eliminando archivo';
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Validar archivo
     */
    const validateFile = (file: File): { valid: boolean; error?: string } => {
        return mediaService.validateFile(file);
    };



    /**
     * Formatear tamaño de archivo
     */
    const formatFileSize = (bytes: number): string => {
        return mediaService.formatFileSize(bytes);
    };

    /**
     * Limpiar errores
     */
    const clearError = (): void => {
        error.value = null;
    };

    return {
        // Estado reactivo
        media,
        isLoading,
        isUploading,
        error,
        uploadProgress,
        
        // Computed
        images,
        totalSize,
        totalSizeFormatted,
        
        // Métodos
        loadMedia,
        uploadMedia,
        uploadMultipleMedia,
        deleteMedia,
        validateFile,
        formatFileSize,
        clearError,
    };
}