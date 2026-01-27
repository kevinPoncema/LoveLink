import { apiClient } from '@/services/ApiClient';
import type { Media } from '@/types/auth';

// Tipos para respuestas del API
type MediaUploadResponse = {
    media: Media;
    message?: string;
};

/**
 * Servicio para gestión de archivos multimedia
 */
class MediaService {
    /**
     * Obtener todos los archivos media del usuario
     */
    async getUserMedia(): Promise<Media[]> {
        const response = await apiClient.get<Media[]>('/api/media');
        return response.data || [];
    }

    /**
     * Subir un archivo multimedia
     */
    async uploadMedia(file: File): Promise<Media> {
        // Validar archivo antes de subirlo
        this.validateFileInternal(file);

        const formData = new FormData();
        formData.append('file', file);

        const response = await apiClient.postFormData<MediaUploadResponse>('/api/media', formData);
        
        if (!response.data?.media) {
            throw new Error('Error subiendo el archivo');
        }
        
        return response.data.media;
    }

    /**
     * Subir múltiples archivos media
     */
    async uploadMultipleMedia(files: File[]): Promise<Media[]> {
        const uploadPromises = files.map(file => this.uploadMedia(file));
        return Promise.all(uploadPromises);
    }

    /**
     * Eliminar media si no está en uso
     */
    async deleteMedia(mediaId: number): Promise<void> {
        await apiClient.delete(`/api/media/${mediaId}`);
    }

    /**
     * Validar archivo antes de subir (interno)
     */
    private validateFileInternal(file: File): void {
        // Tipos permitidos
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            throw new Error('Tipo de archivo no permitido. Use JPG, PNG, WebP o GIF.');
        }

        // Tamaño máximo 10MB
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            throw new Error('El archivo es demasiado grande. Máximo 10MB.');
        }
    }

    /**
     * Validar archivo antes de subirlo (versión pública para componentes)
     */
    validateFile(file: File): { valid: boolean; error?: string } {
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];

        if (!allowedTypes.includes(file.type)) {
            return {
                valid: false,
                error: 'Tipo de archivo no permitido. Solo se aceptan: JPG, PNG, WebP, GIF'
            };
        }

        if (file.size > maxSize) {
            return {
                valid: false,
                error: 'El archivo es demasiado grande. Máximo 10MB'
            };
        }

        return { valid: true };
    }

    /**
     * Validar múltiples archivos
     */
    validateFiles(files: File[]): { valid: boolean; errors: string[] } {
        const errors: string[] = [];
        
        for (let i = 0; i < files.length; i++) {
            const validation = this.validateFile(files[i]);
            if (!validation.valid && validation.error) {
                errors.push(`${files[i].name}: ${validation.error}`);
            }
        }

        return {
            valid: errors.length === 0,
            errors
        };
    }

    /**
     * Verificar si un archivo es una imagen válida
     */
    isValidImageFile(file: File): boolean {
        try {
            this.validateFileInternal(file);
            return true;
        } catch (error) {
            return false;
        }
    }

    /**
     * Formatear tamaño de archivo en bytes a formato legible
     */
    formatFileSize(bytes: number): string {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Obtener URL pública del media
     */
    getPublicUrl(media: Media): string {
        return media.public_url || `/storage/${media.path}`;
    }

    /**
     * Verificar si un archivo es imagen
     */
    isImage(mimeType: string): boolean {
        return mimeType.startsWith('image/');
    }

    /**
     * Generar thumbnail URL (si el backend lo soporta)
     */
    getThumbnailUrl(media: Media, size: 'small' | 'medium' | 'large' = 'medium'): string {
        // TODO: Implementar cuando el backend soporte thumbnails
        return this.getPublicUrl(media);
    }
}

// Instancia singleton del servicio
export const mediaService = new MediaService();