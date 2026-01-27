<script setup lang="ts">
import { ref, computed } from 'vue';
import { useMedia } from '@/composables/useMedia';
import type { Media } from '@/types/auth';

interface Props {
    multiple?: boolean;
    accept?: string;
    maxSize?: number; // en MB
    disabled?: boolean;
    showPreview?: boolean;
    compact?: boolean;
}

interface Emits {
    (e: 'uploaded', media: Media | Media[]): void;
    (e: 'error', error: string): void;
}

const props = withDefaults(defineProps<Props>(), {
    multiple: false,
    accept: 'image/*',
    maxSize: 10,
    disabled: false,
    showPreview: true,
    compact: false
});

const emit = defineEmits<Emits>();

// Composables
const { isUploading, uploadProgress, uploadMedia, uploadMultipleMedia, validateFile, getPreviewUrl, revokePreviewUrl, formatFileSize } = useMedia();

// Estado local
const isDragOver = ref(false);
const fileInput = ref<HTMLInputElement>();
const previews = ref<Array<{file: File; url: string; valid: boolean}>>([]);

// Computed
const maxSizeBytes = computed(() => props.maxSize * 1024 * 1024);

const acceptedTypes = computed(() => {
    return props.accept.split(',').map(type => type.trim());
});

// Métodos
const validateFileSize = (file: File): boolean => {
    return file.size <= maxSizeBytes.value;
};

const validateFileType = (file: File): boolean => {
    if (props.accept === '*' || props.accept.includes('*/*')) return true;
    
    return acceptedTypes.value.some(type => {
        if (type.endsWith('/*')) {
            return file.type.startsWith(type.substring(0, type.length - 1));
        }
        return file.type === type;
    });
};

const validateFiles = (files: FileList | File[]): File[] => {
    const fileArray = Array.from(files);
    const validFiles: File[] = [];
    const errors: string[] = [];

    fileArray.forEach(file => {
        if (!validateFileType(file)) {
            errors.push(`${file.name}: Tipo de archivo no permitido`);
            return;
        }

        if (!validateFileSize(file)) {
            errors.push(`${file.name}: Archivo muy grande (máximo ${props.maxSize}MB)`);
            return;
        }

        if (!validateFile(file)) {
            errors.push(`${file.name}: Archivo inválido`);
            return;
        }

        validFiles.push(file);
    });

    if (errors.length > 0) {
        emit('error', errors.join(', '));
    }

    return validFiles;
};

const createPreviews = (files: File[]) => {
    // Limpiar previews anteriores
    previews.value.forEach(preview => revokePreviewUrl(preview.url));
    previews.value = [];

    if (props.showPreview && files.length > 0) {
        previews.value = files.map(file => ({
            file,
            url: getPreviewUrl(file),
            valid: validateFileType(file) && validateFileSize(file) && validateFile(file)
        }));
    }
};

const handleFiles = async (files: FileList | File[]) => {
    if (props.disabled) return;

    const validFiles = validateFiles(files);
    if (validFiles.length === 0) return;

    createPreviews(validFiles);

    try {
        if (props.multiple) {
            const results = await uploadMultipleMedia(validFiles);
            emit('uploaded', results);
        } else {
            const result = await uploadMedia(validFiles[0]);
            emit('uploaded', result);
        }
        
        // Limpiar previews después del upload exitoso
        clearPreviews();
        
    } catch (error: any) {
        emit('error', error.message || 'Error subiendo archivo');
    }
};

const clearPreviews = () => {
    previews.value.forEach(preview => revokePreviewUrl(preview.url));
    previews.value = [];
};

const removePreview = (index: number) => {
    const preview = previews.value[index];
    revokePreviewUrl(preview.url);
    previews.value.splice(index, 1);
};

// Event handlers
const handleClick = () => {
    if (!props.disabled) {
        fileInput.value?.click();
    }
};

const handleFileInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        handleFiles(target.files);
    }
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    if (!props.disabled) {
        isDragOver.value = true;
    }
};

const handleDragLeave = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = false;
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = false;
    
    if (props.disabled) return;
    
    const files = event.dataTransfer?.files;
    if (files && files.length > 0) {
        handleFiles(files);
    }
};
</script>

<template>
    <div class="media-upload-container">
        <!-- Input file oculto -->
        <input
            ref="fileInput"
            type="file"
            :accept="accept"
            :multiple="multiple"
            :disabled="disabled"
            @change="handleFileInput"
            class="hidden"
        />

        <!-- Área de drop/upload -->
        <div
            @click="handleClick"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDrop"
            class="border-2 border-dashed rounded-xl transition-all duration-200 cursor-pointer"
            :class="[
                compact ? 'p-4' : 'p-8',
                isDragOver 
                    ? 'border-rose-500 bg-rose-50 dark:bg-rose-900/20' 
                    : 'border-stone-300 dark:border-stone-600 hover:border-stone-400 dark:hover:border-stone-500',
                disabled ? 'opacity-50 cursor-not-allowed' : '',
                isUploading ? 'pointer-events-none' : ''
            ]"
        >
            <div class="text-center">
                <!-- Estado de carga -->
                <div v-if="isUploading" class="space-y-3">
                    <div class="animate-spin w-8 h-8 mx-auto">
                        📤
                    </div>
                    <p class="text-sm text-stone-600 dark:text-stone-400">
                        Subiendo... {{ uploadProgress }}%
                    </p>
                    <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2">
                        <div 
                            class="bg-rose-500 h-2 rounded-full transition-all duration-300"
                            :style="{ width: uploadProgress + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Estado normal -->
                <div v-else class="space-y-3">
                    <div class="text-4xl">
                        📁
                    </div>
                    
                    <div>
                        <p class="text-sm font-medium text-stone-700 dark:text-stone-300">
                            <span v-if="compact">Click para subir</span>
                            <span v-else>Click para subir o arrastra {{ multiple ? 'archivos' : 'archivo' }} aquí</span>
                        </p>
                        <p v-if="!compact" class="text-xs text-stone-500 dark:text-stone-400 mt-1">
                            Máximo {{ maxSize }}MB{{ multiple ? ' por archivo' : '' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista previa de archivos seleccionados -->
        <div v-if="previews.length > 0 && showPreview" class="mt-4 space-y-3">
            <h4 class="text-sm font-medium text-stone-700 dark:text-stone-300">
                Archivos seleccionados:
            </h4>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <div
                    v-for="(preview, index) in previews"
                    :key="index"
                    class="relative group"
                >
                    <div class="aspect-square bg-stone-100 dark:bg-stone-800 rounded-lg overflow-hidden">
                        <img
                            v-if="preview.file.type.startsWith('image/')"
                            :src="preview.url"
                            :alt="preview.file.name"
                            class="w-full h-full object-cover"
                            :class="!preview.valid ? 'opacity-50 grayscale' : ''"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-2xl">
                            📄
                        </div>
                        
                        <!-- Botón eliminar -->
                        <button
                            @click.stop="removePreview(index)"
                            class="absolute top-1 right-1 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                            ×
                        </button>
                        
                        <!-- Indicador de error -->
                        <div v-if="!preview.valid" class="absolute inset-0 bg-red-500/20 flex items-center justify-center">
                            <div class="bg-red-500 text-white text-xs px-2 py-1 rounded">
                                ❌
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-xs text-stone-600 dark:text-stone-400 mt-1 truncate" :title="preview.file.name">
                        {{ preview.file.name }}
                    </p>
                    <p class="text-xs text-stone-500 dark:text-stone-500">
                        {{ formatFileSize(preview.file.size) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>