import type { ApiResponse } from '@/types/auth';
import { apiClient } from '../ApiClient';

// Tipos específicos para Media
export type Media = {
    id: number;
    filename: string;
    original_filename: string;
    path: string;
    public_url: string;
    mime_type: string;
    size: number;
    user_id: number;
    created_at: string;
    updated_at: string;
};

export type MediaUploadResponse = {
    media: Media;
    message: string;
};

export class MediaService {
    /**
     * Obtener todos los archivos media accesibles por el usuario
     */
    async getUserMedia(): Promise<Media[]> {
        const response = await apiClient.get<Media[]>('/media');
        return response.data || [];
    }

    /**
     * Subir nuevo archivo multimedia
     */
    async uploadMedia(file: File): Promise<Media> {
        // Crear FormData para el archivo
        const formData = new FormData();
        formData.append('file', file);

        const response = await apiClient.postFormData<MediaUploadResponse>('/media', formData);
        
        if (!response.data?.media) {
            throw new Error('Error subiendo el archivo');
        }
        
        return response.data.media;
    }

    /**
     * Subir múltiples archivos
     */
    async uploadMultipleMedia(files: File[]): Promise<Media[]> {
        const uploadPromises = files.map(file => this.uploadMedia(file));
        return Promise.all(uploadPromises);
    }

    /**
     * Eliminar archivo media
     */
    async deleteMedia(id: number): Promise<void> {
        await apiClient.delete(`/media/${id}`);
    }

    /**
     * Validar archivo antes de subirlo
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
     * Obtener URL pública del media
     */
    getPublicUrl(media: Media): string {
        return media.public_url || `/storage/${media.path}`;
    }

    /**
     * Formatear tamaño de archivo en formato legible
     */
    formatFileSize(bytes: number): string {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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