<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useMedia } from '@/composables/useMedia';
import type { Media } from '@/types/auth';
import MediaUpload from '@/components/ui/MediaUpload.vue';

// Composables
const {
    media,
    uploadProgress,
    isUploading,
    error,
    loadMedia,
    deleteMedia,
    clearError
} = useMedia();

// Estado local
const selectedItems = ref<Set<number>>(new Set());
const viewMode = ref<'grid' | 'list'>('grid');
const filterType = ref<'all' | 'image' | 'video' | 'document'>('all');
const searchQuery = ref('');
const showDeleteModal = ref(false);
const itemsToDelete = ref<Media[]>([]);

// Computed
const filteredMedia = computed(() => {
    let result = media.value;

    // Filtrar por tipo
    if (filterType.value !== 'all') {
        result = result.filter(item => {
            switch (filterType.value) {
                case 'image':
                    return item.mime_type.startsWith('image/');
                case 'video':
                    return item.mime_type.startsWith('video/');
                case 'document':
                    return !item.mime_type.startsWith('image/') && !item.mime_type.startsWith('video/');
                default:
                    return true;
            }
        });
    }

    // Filtrar por búsqueda
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(item =>
            item.filename.toLowerCase().includes(query) ||
            (item.description && item.description.toLowerCase().includes(query))
        );
    }

    return result.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
});

const isAllSelected = computed(() => {
    return filteredMedia.value.length > 0 && selectedItems.value.size === filteredMedia.value.length;
});

const selectedCount = computed(() => selectedItems.value.size);

const totalSize = computed(() => {
    return media.value.reduce((total, item) => total + item.size, 0);
});

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getFileIcon = (mimeType: string): string => {
    if (mimeType.startsWith('image/')) return '🖼️';
    if (mimeType.startsWith('video/')) return '🎥';
    if (mimeType.includes('pdf')) return '📄';
    if (mimeType.includes('word') || mimeType.includes('document')) return '📝';
    if (mimeType.includes('spreadsheet') || mimeType.includes('excel')) return '📊';
    return '📎';
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Métodos de selección
const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedItems.value.clear();
    } else {
        filteredMedia.value.forEach(item => {
            selectedItems.value.add(item.id);
        });
    }
};

const toggleSelect = (itemId: number) => {
    if (selectedItems.value.has(itemId)) {
        selectedItems.value.delete(itemId);
    } else {
        selectedItems.value.add(itemId);
    }
};

// Métodos de eliminación
const confirmDelete = (items?: Media[]) => {
    if (items) {
        itemsToDelete.value = items;
    } else {
        itemsToDelete.value = filteredMedia.value.filter(item =>
            selectedItems.value.has(item.id)
        );
    }

    if (itemsToDelete.value.length === 0) return;

    showDeleteModal.value = true;
};

const executeDelete = async () => {
    try {
        for (const item of itemsToDelete.value) {
            await deleteMedia(item.id);
            selectedItems.value.delete(item.id);
        }
        showDeleteModal.value = false;
        itemsToDelete.value = [];
    } catch (err: any) {
        console.error('Error deleting media:', err);
        showDeleteModal.value = false;
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    itemsToDelete.value = [];
};

// Métodos de copia
const copyUrl = async (url: string) => {
    try {
        await navigator.clipboard.writeText(url);
        // TODO: Mostrar toast de éxito
        console.log('URL copiada al portapapeles');
    } catch (err) {
        console.error('Error copying URL:', err);
    }
};

const handleUpload = () => {
    selectedItems.value.clear();
    // La lista se actualiza automáticamente mediante el composable
};

// Lifecycle
onMounted(async () => {
    await loadMedia();
});
</script>

<template>
    <div class="min-h-screen bg-stone-50 dark:bg-stone-900">
        <Head title="Galería de Media" />

        <!-- Header -->
        <header class="bg-white dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-stone-900 dark:text-stone-100">
                            Galería de Media
                        </h1>
                        <p class="text-stone-600 dark:text-stone-400 mt-1">
                            Gestiona tus archivos multimedia
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <Link
                            href="/dashboard"
                            class="text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200"
                        >
                            ← Volver al Dashboard
                        </Link>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="flex items-center gap-6 mt-4 text-sm text-stone-600 dark:text-stone-400">
                    <span>
                        <strong class="text-stone-900 dark:text-stone-100">{{ media.length }}</strong>
                        archivos
                    </span>
                    <span>
                        <strong class="text-stone-900 dark:text-stone-100">{{ formatFileSize(totalSize) }}</strong>
                        en total
                    </span>
                    <span v-if="selectedCount > 0" class="text-rose-600 dark:text-rose-400">
                        <strong>{{ selectedCount }}</strong> seleccionados
                    </span>
                </div>
            </div>
        </header>

        <!-- Barra de herramientas -->
        <div class="bg-white dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Búsqueda -->
                    <div class="flex-1">
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Buscar archivos..."
                                class="w-full pl-10 pr-4 py-2 border border-stone-200 dark:border-stone-600 rounded-xl bg-white dark:bg-stone-700 text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                            />
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-stone-400">
                                🔍
                            </div>
                        </div>
                    </div>

                    <!-- Filtros y vista -->
                    <div class="flex items-center gap-3">
                        <!-- Filtro por tipo -->
                        <select
                            v-model="filterType"
                            class="px-3 py-2 border border-stone-200 dark:border-stone-600 rounded-xl bg-white dark:bg-stone-700 text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-rose-500"
                        >
                            <option value="all">Todos los tipos</option>
                            <option value="image">Imágenes</option>
                            <option value="video">Videos</option>
                            <option value="document">Documentos</option>
                        </select>

                        <!-- Vista -->
                        <div class="flex border border-stone-200 dark:border-stone-600 rounded-lg">
                            <button
                                @click="viewMode = 'grid'"
                                :class="[
                                    'px-3 py-2 text-sm',
                                    viewMode === 'grid'
                                        ? 'bg-rose-600 text-white'
                                        : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-700'
                                ]"
                            >
                                ⊞
                            </button>
                            <button
                                @click="viewMode = 'list'"
                                :class="[
                                    'px-3 py-2 text-sm border-l border-stone-200 dark:border-stone-600',
                                    viewMode === 'list'
                                        ? 'bg-rose-600 text-white'
                                        : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-700'
                                ]"
                            >
                                ☰
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Acciones múltiples -->
                <div v-if="selectedCount > 0" class="flex items-center justify-between mt-4 p-3 bg-rose-50 dark:bg-rose-900/20 rounded-xl">
                    <span class="text-sm text-rose-700 dark:text-rose-400">
                        {{ selectedCount }} {{ selectedCount === 1 ? 'archivo seleccionado' : 'archivos seleccionados' }}
                    </span>
                    <button
                        @click="confirmDelete()"
                        class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium"
                    >
                        Eliminar seleccionados
                    </button>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <main class="max-w-7xl mx-auto px-6 py-8">
            <!-- Error global -->
            <div v-if="error" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 rounded-lg">
                {{ error }}
                <button @click="clearError" class="ml-2 text-red-800 dark:text-red-300 hover:underline">
                    Cerrar
                </button>
            </div>

            <!-- Upload area -->
            <div class="mb-8">
                <MediaUpload @uploaded="handleUpload" />
            </div>

            <!-- Progress indicator -->
            <div v-if="isUploading" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-blue-700 dark:text-blue-300 font-medium">Subiendo archivos...</span>
                    <span class="text-blue-600 dark:text-blue-400 text-sm">{{ uploadProgress }}%</span>
                </div>
                <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                    <div
                        class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        :style="{ width: `${uploadProgress}%` }"
                    ></div>
                </div>
            </div>

            <!-- Lista de archivos -->
            <div v-if="media.length === 0" class="text-center py-16">
                <div class="text-6xl mb-4">📁</div>
                <h3 class="text-lg font-medium text-stone-900 dark:text-stone-100 mb-2">
                    Tu galería está vacía
                </h3>
                <p class="text-stone-600 dark:text-stone-400">
                    Sube tus primeros archivos para empezar a construir tu galería
                </p>
            </div>

            <!-- Vista de cuadrícula -->
            <div v-else-if="viewMode === 'grid'" class="space-y-6">
                <!-- Selector todo/nada -->
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            :checked="isAllSelected"
                            @change="toggleSelectAll"
                            class="w-4 h-4 text-rose-600 border-stone-300 rounded focus:ring-rose-500"
                        />
                        <span class="text-sm text-stone-600 dark:text-stone-400">
                            Seleccionar todos los {{ filteredMedia.length }} archivos visibles
                        </span>
                    </label>
                </div>

                <!-- Grid de archivos -->
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                    <div
                        v-for="item in filteredMedia"
                        :key="item.id"
                        class="group relative bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700 overflow-hidden hover:shadow-md transition-all"
                        :class="{
                            'ring-2 ring-rose-500': selectedItems.has(item.id)
                        }"
                    >
                        <!-- Checkbox de selección -->
                        <div class="absolute top-2 left-2 z-10">
                            <input
                                type="checkbox"
                                :checked="selectedItems.has(item.id)"
                                @change="toggleSelect(item.id)"
                                class="w-4 h-4 text-rose-600 border-stone-300 rounded focus:ring-rose-500 bg-white/90"
                            />
                        </div>

                        <!-- Acciones -->
                        <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button
                                @click="copyUrl(item.url)"
                                class="w-8 h-8 bg-white/90 hover:bg-white text-stone-700 rounded-lg flex items-center justify-center text-xs mr-1"
                                title="Copiar URL"
                            >
                                📋
                            </button>
                            <button
                                @click="confirmDelete([item])"
                                class="w-8 h-8 bg-red-500/90 hover:bg-red-500 text-white rounded-lg flex items-center justify-center text-xs"
                                title="Eliminar"
                            >
                                🗑️
                            </button>
                        </div>

                        <!-- Contenido del archivo -->
                        <div class="aspect-square relative">
                            <!-- Preview de imagen -->
                            <img
                                v-if="item.mime_type.startsWith('image/')"
                                :src="item.url"
                                :alt="item.filename"
                                class="w-full h-full object-cover"
                                loading="lazy"
                            />

                            <!-- Preview de video -->
                            <video
                                v-else-if="item.mime_type.startsWith('video/')"
                                :src="item.url"
                                class="w-full h-full object-cover"
                                muted
                            ></video>

                            <!-- Icono para otros tipos -->
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center bg-stone-100 dark:bg-stone-700"
                            >
                                <div class="text-center">
                                    <div class="text-4xl mb-2">{{ getFileIcon(item.mime_type) }}</div>
                                    <div class="text-xs font-mono text-stone-500 dark:text-stone-400 uppercase">
                                        {{ item.mime_type.split('/')[1] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info del archivo -->
                        <div class="p-3">
                            <p class="text-xs font-medium text-stone-900 dark:text-stone-100 truncate" :title="item.filename">
                                {{ item.filename }}
                            </p>
                            <p class="text-xs text-stone-500 dark:text-stone-400">
                                {{ formatFileSize(item.size) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista de lista -->
            <div v-else class="space-y-4">
                <!-- Header de tabla -->
                <div class="flex items-center gap-3 p-3 bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700">
                    <input
                        type="checkbox"
                        :checked="isAllSelected"
                        @change="toggleSelectAll"
                        class="w-4 h-4 text-rose-600 border-stone-300 rounded focus:ring-rose-500"
                    />
                    <div class="flex-1 grid grid-cols-5 gap-4 font-medium text-stone-700 dark:text-stone-300 text-sm">
                        <div>Archivo</div>
                        <div>Tipo</div>
                        <div>Tamaño</div>
                        <div>Fecha</div>
                        <div class="text-right">Acciones</div>
                    </div>
                </div>

                <!-- Items de lista -->
                <div
                    v-for="item in filteredMedia"
                    :key="item.id"
                    class="flex items-center gap-3 p-3 bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700 hover:shadow-md transition-all"
                    :class="{
                        'ring-2 ring-rose-500': selectedItems.has(item.id)
                    }"
                >
                    <input
                        type="checkbox"
                        :checked="selectedItems.has(item.id)"
                        @change="toggleSelect(item.id)"
                        class="w-4 h-4 text-rose-600 border-stone-300 rounded focus:ring-rose-500"
                    />

                    <div class="flex-1 grid grid-cols-5 gap-4 items-center text-sm">
                        <!-- Archivo -->
                        <div class="flex items-center gap-3">
                            <!-- Thumbnail -->
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-stone-100 dark:bg-stone-700 flex-shrink-0">
                                <img
                                    v-if="item.mime_type.startsWith('image/')"
                                    :src="item.url"
                                    :alt="item.filename"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-lg">
                                    {{ getFileIcon(item.mime_type) }}
                                </div>
                            </div>

                            <div class="min-w-0">
                                <p class="font-medium text-stone-900 dark:text-stone-100 truncate" :title="item.filename">
                                    {{ item.filename }}
                                </p>
                                <p v-if="item.description" class="text-stone-500 dark:text-stone-400 text-xs truncate">
                                    {{ item.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Tipo -->
                        <div class="text-stone-600 dark:text-stone-400 font-mono text-xs">
                            {{ item.mime_type }}
                        </div>

                        <!-- Tamaño -->
                        <div class="text-stone-600 dark:text-stone-400">
                            {{ formatFileSize(item.size) }}
                        </div>

                        <!-- Fecha -->
                        <div class="text-stone-600 dark:text-stone-400">
                            {{ formatDate(item.created_at) }}
                        </div>

                        <!-- Acciones -->
                        <div class="flex items-center justify-end gap-1">
                            <button
                                @click="copyUrl(item.url)"
                                class="p-2 text-stone-500 hover:text-stone-700 dark:text-stone-400 dark:hover:text-stone-200"
                                title="Copiar URL"
                            >
                                📋
                            </button>
                            <button
                                @click="confirmDelete([item])"
                                class="p-2 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                title="Eliminar"
                            >
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado vacío filtrado -->
            <div v-if="media.length > 0 && filteredMedia.length === 0" class="text-center py-16">
                <div class="text-4xl mb-4">🔍</div>
                <h3 class="text-lg font-medium text-stone-900 dark:text-stone-100 mb-2">
                    No se encontraron archivos
                </h3>
                <p class="text-stone-600 dark:text-stone-400">
                    Intenta cambiar tus filtros o términos de búsqueda
                </p>
                <button
                    @click="searchQuery = ''; filterType = 'all'"
                    class="mt-4 text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 font-medium"
                >
                    Limpiar filtros
                </button>
            </div>
        </main>

        <!-- Modal: Confirmar eliminación -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50"
            @click.self="cancelDelete"
        >
            <div class="bg-white dark:bg-stone-800 rounded-2xl max-w-md w-full">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-stone-900 dark:text-stone-100 mb-4">
                        Confirmar eliminación
                    </h2>

                    <p class="text-stone-600 dark:text-stone-400 mb-6">
                        ¿Estás seguro de eliminar
                        <strong class="text-stone-900 dark:text-stone-100">
                            {{ itemsToDelete.length === 1 ? itemsToDelete[0].filename : `${itemsToDelete.length} archivos` }}
                        </strong>?
                        Esta acción no se puede deshacer.
                    </p>

                    <div class="flex justify-end gap-3">
                        <button
                            @click="cancelDelete"
                            class="px-4 py-2 text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="executeDelete"
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-xl font-medium"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
