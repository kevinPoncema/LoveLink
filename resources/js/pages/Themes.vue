l <script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useThemes } from '@/composables/useThemes';
import { useMedia } from '@/composables/useMedia';
import type { Theme, Media } from '@/types/auth';
import ColorPicker from '@/components/ui/ColorPicker.vue';
import MediaUpload from '@/components/ui/MediaUpload.vue';

// Composables
const {
    themes,
    systemThemes,
    userThemes,
    isLoading,
    error,
    canCreateMore,
    loadThemes,
    createTheme,
    updateTheme,
    deleteTheme,
    clearError
} = useThemes();

const { media, loadMedia } = useMedia();

// Estado local
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingTheme = ref<Theme | null>(null);
const selectedBackgroundMedia = ref<Media | null>(null);
const showMediaSelector = ref(false);

// Form data
const form = ref({
    name: '',
    description: '',
    primary_color: '#E11D48',
    secondary_color: '#F472B6',
    bg_color: '#FDF2F8',
    css_class: '',
    bg_image_file: null as File | null,
});

// Computed
const backgroundImages = computed(() => {
    return media.value.filter(m => m.mime_type.startsWith('image/'));
});

const formattedCssClass = computed(() => {
    return form.value.name
        .toLowerCase()
        .replace(/[^a-z0-9\s]/g, '')
        .replace(/\s+/g, '-')
        .replace(/^-+|-+$/g, '');
});

// Watchers para auto-generar CSS class
const updateCssClass = () => {
    if (form.value.name && !showEditModal.value) {
        form.value.css_class = formattedCssClass.value;
    }
};

// Métodos
const openCreateModal = () => {
    if (!canCreateMore.value) {
        alert('Has alcanzado el límite de temas personalizados (10)');
        return;
    }

    form.value = {
        name: '',
        description: '',
        primary_color: '#E11D48',
        secondary_color: '#F472B6',
        bg_color: '#FDF2F8',
        css_class: '',
        bg_image_file: null,
    };
    selectedBackgroundMedia.value = null;
    showCreateModal.value = true;
};

const openEditModal = (theme: Theme) => {
    if (theme.is_system) {
        alert('Los temas del sistema no se pueden editar');
        return;
    }

    editingTheme.value = theme;
    form.value = {
        name: theme.name,
        description: theme.description || '',
        primary_color: theme.primary_color,
        secondary_color: theme.secondary_color,
        bg_color: theme.bg_color,
        css_class: theme.css_class,
        bg_image_file: null,
    };
    // Cargar la imagen de fondo desde la relación bg_image si existe
    selectedBackgroundMedia.value = theme.bg_image || null;
    showEditModal.value = true;
};

const closeModals = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingTheme.value = null;
    selectedBackgroundMedia.value = null;
    showMediaSelector.value = false;
    form.value.bg_image_file = null;
    clearError();
};

const handleSubmit = async () => {
    try {
        clearError();

        const formData = {
            name: form.value.name,
            description: form.value.description,
            primary_color: form.value.primary_color,
            secondary_color: form.value.secondary_color,
            bg_color: form.value.bg_color,
            css_class: form.value.css_class || formattedCssClass.value,
            // Enviar media_id si hay un media seleccionado, sino enviar el file
            bg_image_media_id: selectedBackgroundMedia.value?.id,
            bg_image_file: form.value.bg_image_file,
        };

        if (showEditModal.value && editingTheme.value) {
            await updateTheme(editingTheme.value.id, formData);
        } else {
            await createTheme(formData);
        }

        closeModals();

    } catch (err: any) {
        console.error('Error saving theme:', err);
    }
};

const handleDelete = async (theme: Theme) => {
    if (theme.is_system) {
        alert('Los temas del sistema no se pueden eliminar');
        return;
    }

    if (confirm(`¿Estás seguro de eliminar el tema "${theme.name}"?`)) {
        try {
            await deleteTheme(theme.id);
        } catch (err: any) {
            console.error('Error deleting theme:', err);
        }
    }
};

const handleBackgroundUpload = (uploadedMedia: Media | Media[]) => {
    const media = Array.isArray(uploadedMedia) ? uploadedMedia[0] : uploadedMedia;
    form.value.bg_image_file = null; // Limpiar file ya que se subió
    selectedBackgroundMedia.value = media;
    showMediaSelector.value = false;
};

const removeBackground = () => {
    form.value.bg_image_file = null;
    selectedBackgroundMedia.value = null;
    // Si estamos editando, asegurarnos de que se quite la referencia
    if (editingTheme.value) {
        editingTheme.value.bg_image = null;
        editingTheme.value.bg_image_media_id = undefined;
    }
};

const selectBackgroundFromMedia = (selectedMedia: Media) => {
    selectedBackgroundMedia.value = selectedMedia;
    showMediaSelector.value = false;
};

// Abrir selector de medios
const openMediaSelector = async () => {
    // Cargar medios solo cuando se abre el selector
    if (media.value.length === 0) {
        await loadMedia();
    }
    showMediaSelector.value = true;
};

// Helper para crear URL de blob
const createObjectURL = (file: File): string => {
    return window.URL.createObjectURL(file);
};

// Lifecycle
onMounted(async () => {
    // Solo cargar temas al inicio, no medios
    await loadThemes();
});
</script>

<template>
    <div class="min-h-screen bg-stone-50 dark:bg-stone-900">
        <Head title="Gestionar Temas" />

        <!-- Header -->
        <header class="bg-white dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
            <div class="max-w-6xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-stone-900 dark:text-stone-100">
                            Gestionar Temas
                        </h1>
                        <p class="text-stone-600 dark:text-stone-400 mt-1">
                            Personaliza la apariencia de tus landing pages
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <Link
                            href="/dashboard"
                            class="text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200"
                        >
                            ← Volver al Dashboard
                        </Link>

                        <button
                            @click="openCreateModal"
                            :disabled="!canCreateMore || isLoading"
                            class="bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                        >
                            + Nuevo Tema
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenido principal -->
        <main class="max-w-6xl mx-auto px-6 py-8">
            <!-- Error global -->
            <div v-if="error" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 rounded-lg">
                {{ error }}
                <button @click="clearError" class="ml-2 text-red-800 dark:text-red-300 hover:underline">
                    Cerrar
                </button>
            </div>

            <!-- Loading state -->
            <div v-if="isLoading" class="text-center py-12">
                <div class="animate-spin text-4xl mb-4">🎨</div>
                <p class="text-stone-600 dark:text-stone-400">Cargando temas...</p>
            </div>

            <!-- Contenido -->
            <div v-else class="space-y-8">
                <!-- Temas del sistema -->
                <section>
                    <h2 class="text-lg font-semibold text-stone-900 dark:text-stone-100 mb-4">
                        Temas del Sistema
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <div
                            v-for="theme in systemThemes"
                            :key="theme.id"
                            class="bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700 overflow-hidden hover:shadow-md transition-shadow"
                        >
                            <!-- Preview del tema -->
                            <div
                                class="aspect-video p-4"
                                :style="{
                                    backgroundColor: theme.bg_color,
                                    backgroundImage: theme.bg_image?.url || theme.bg_image_url ? `url(${theme.bg_image?.url || theme.bg_image_url})` : 'none',
                                    backgroundSize: 'cover',
                                    backgroundPosition: 'center'
                                }"
                            >
                                <div class="space-y-2">
                                    <div
                                        class="w-full h-2 rounded"
                                        :style="{ backgroundColor: theme.primary_color }"
                                    ></div>
                                    <div
                                        class="w-3/4 h-2 rounded"
                                        :style="{ backgroundColor: theme.secondary_color }"
                                    ></div>
                                    <div
                                        class="w-1/2 h-2 rounded"
                                        :style="{ backgroundColor: theme.secondary_color, opacity: 0.7 }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Info del tema -->
                            <div class="p-4">
                                <h3 class="font-medium text-stone-900 dark:text-stone-100">
                                    {{ theme.name }}
                                </h3>
                                <p v-if="theme.description" class="text-sm text-stone-600 dark:text-stone-400 mt-1">
                                    {{ theme.description }}
                                </p>
                                <div class="flex items-center justify-between mt-3">
                                    <span class="inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 text-xs rounded">
                                        Sistema
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Temas del usuario -->
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-stone-900 dark:text-stone-100">
                            Mis Temas Personalizados
                        </h2>
                        <span class="text-sm text-stone-500 dark:text-stone-400">
                            {{ userThemes.length }}/10 temas
                        </span>
                    </div>

                    <!-- Lista de temas del usuario -->
                    <div v-if="userThemes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <div
                            v-for="theme in userThemes"
                            :key="theme.id"
                            class="bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700 overflow-hidden hover:shadow-md transition-shadow group"
                        >
                            <!-- Preview del tema -->
                            <div
                                class="aspect-video p-4 relative"
                                :style="{
                                    backgroundColor: theme.bg_color,
                                    backgroundImage: theme.bg_image?.url || theme.bg_image_url ? `url(${theme.bg_image?.url || theme.bg_image_url})` : 'none',
                                    backgroundSize: 'cover',
                                    backgroundPosition: 'center'
                                }"
                            >
                                <div class="space-y-2">
                                    <div
                                        class="w-full h-2 rounded"
                                        :style="{ backgroundColor: theme.primary_color }"
                                    ></div>
                                    <div
                                        class="w-3/4 h-2 rounded"
                                        :style="{ backgroundColor: theme.secondary_color }"
                                    ></div>
                                    <div
                                        class="w-1/2 h-2 rounded"
                                        :style="{ backgroundColor: theme.secondary_color, opacity: 0.7 }"
                                    ></div>
                                </div>

                                <!-- Botones de acción -->
                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity space-x-1">
                                    <button
                                        @click="openEditModal(theme)"
                                        class="w-8 h-8 bg-white/90 hover:bg-white text-stone-700 rounded-lg flex items-center justify-center text-sm"
                                        title="Editar tema"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        @click="handleDelete(theme)"
                                        class="w-8 h-8 bg-red-500/90 hover:bg-red-500 text-white rounded-lg flex items-center justify-center text-sm"
                                        title="Eliminar tema"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </div>

                            <!-- Info del tema -->
                            <div class="p-4">
                                <h3 class="font-medium text-stone-900 dark:text-stone-100">
                                    {{ theme.name }}
                                </h3>
                                <p v-if="theme.description" class="text-sm text-stone-600 dark:text-stone-400 mt-1 line-clamp-2">
                                    {{ theme.description }}
                                </p>

                                <!-- Paleta de colores -->
                                <div class="flex items-center gap-1 mt-3">
                                    <div
                                        class="w-4 h-4 rounded-full border border-stone-200 dark:border-stone-600"
                                        :style="{ backgroundColor: theme.primary_color }"
                                        :title="'Primario: ' + theme.primary_color"
                                    ></div>
                                    <div
                                        class="w-4 h-4 rounded-full border border-stone-200 dark:border-stone-600"
                                        :style="{ backgroundColor: theme.secondary_color }"
                                        :title="'Secundario: ' + theme.secondary_color"
                                    ></div>
                                    <div
                                        class="w-4 h-4 rounded-full border border-stone-200 dark:border-stone-600"
                                        :style="{ backgroundColor: theme.bg_color }"
                                        :title="'Fondo: ' + theme.bg_color"
                                    ></div>

                                    <span class="ml-auto inline-flex items-center px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 text-xs rounded">
                                        Personalizado
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="text-center py-12 bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700">
                        <div class="text-4xl mb-4">🎨</div>
                        <h3 class="text-lg font-medium text-stone-900 dark:text-stone-100 mb-2">
                            No tienes temas personalizados
                        </h3>
                        <p class="text-stone-600 dark:text-stone-400 mb-4">
                            Crea tu primer tema personalizado para darle un toque único a tus landing pages
                        </p>
                        <button
                            @click="openCreateModal"
                            class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-xl font-medium"
                        >
                            Crear Mi Primer Tema
                        </button>
                    </div>
                </section>
            </div>
        </main>

        <!-- Modal: Crear/Editar Tema -->
        <div
            v-if="showCreateModal || showEditModal"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50"
            @click.self="closeModals"
        >
            <div class="bg-white dark:bg-stone-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-stone-200 dark:border-stone-700">
                    <h2 class="text-xl font-bold text-stone-900 dark:text-stone-100">
                        {{ showEditModal ? 'Editar Tema' : 'Crear Nuevo Tema' }}
                    </h2>
                </div>

                <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
                    <!-- Nombre y descripción -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                                Nombre del tema *
                            </label>
                            <input
                                v-model="form.name"
                                @input="updateCssClass"
                                type="text"
                                required
                                placeholder="Mi tema personalizado"
                                class="w-full px-3 py-2 border border-stone-200 dark:border-stone-600 rounded-xl bg-white dark:bg-stone-700 text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                                Clase CSS
                            </label>
                            <input
                                v-model="form.css_class"
                                type="text"
                                :placeholder="formattedCssClass"
                                class="w-full px-3 py-2 border border-stone-200 dark:border-stone-600 rounded-xl bg-white dark:bg-stone-700 text-stone-900 dark:text-stone-100 font-mono text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Descripción
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            placeholder="Describe tu tema..."
                            class="w-full px-3 py-2 border border-stone-200 dark:border-stone-600 rounded-xl bg-white dark:bg-stone-700 text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                        ></textarea>
                    </div>

                    <!-- Colores -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <ColorPicker
                            v-model="form.primary_color"
                            label="Color Primario"
                        />

                        <ColorPicker
                            v-model="form.secondary_color"
                            label="Color Secundario"
                        />

                        <ColorPicker
                            v-model="form.bg_color"
                            label="Color de Fondo"
                        />
                    </div>

                    <!-- Background Image -->
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-3">
                            Imagen de Fondo
                        </label>

                        <!-- Imagen actual/seleccionada -->
                        <div v-if="selectedBackgroundMedia || form.bg_image_file" class="mb-4">
                            <div class="flex items-start gap-4 p-4 bg-stone-50 dark:bg-stone-900 rounded-xl">
                                <img
                                    :src="selectedBackgroundMedia?.public_url || selectedBackgroundMedia?.url || (form.bg_image_file ? createObjectURL(form.bg_image_file) : '')"
                                    :alt="selectedBackgroundMedia?.original_filename || selectedBackgroundMedia?.filename || form.bg_image_file?.name"
                                    class="w-24 h-24 object-cover rounded-lg"
                                />
                                <div class="flex-1">
                                    <p class="font-medium text-stone-900 dark:text-stone-100">
                                        {{ selectedBackgroundMedia?.original_filename || selectedBackgroundMedia?.filename || form.bg_image_file?.name }}
                                    </p>
                                    <p class="text-sm text-stone-500 dark:text-stone-400">
                                        {{ selectedBackgroundMedia ? 'De tu galería' : 'Archivo nuevo' }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="removeBackground"
                                    class="text-red-500 hover:text-red-700 p-1"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>

                        <!-- Opciones para seleccionar imagen -->
                        <div class="space-y-3">
                            <!-- Upload nueva -->
                            <MediaUpload
                                @uploaded="handleBackgroundUpload"
                                accept="image/*"
                                :compact="true"
                                :show-preview="false"
                            />

                            <!-- Seleccionar de galería -->
                            <button
                                type="button"
                                @click="openMediaSelector"
                                class="w-full px-4 py-2 border border-stone-200 dark:border-stone-600 rounded-xl text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors"
                            >
                                Seleccionar de mi galería
                            </button>
                        </div>
                    </div>

                    <!-- Preview del tema -->
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-3">
                            Vista previa
                        </label>
                        <div
                            class="aspect-video rounded-xl p-6"
                            :style="{
                                backgroundColor: form.bg_color,
                                backgroundImage: form.bg_image_file
                                    ? `url(${createObjectURL(form.bg_image_file)})`
                                    : (selectedBackgroundMedia?.url || selectedBackgroundMedia?.public_url
                                        ? `url(${selectedBackgroundMedia.url || selectedBackgroundMedia.public_url})`
                                        : 'none'),
                                backgroundSize: 'cover',
                                backgroundPosition: 'center'
                            }"
                        >
                            <div class="space-y-3">
                                <div
                                    class="w-full h-4 rounded"
                                    :style="{ backgroundColor: form.primary_color }"
                                ></div>
                                <div
                                    class="w-3/4 h-3 rounded"
                                    :style="{ backgroundColor: form.secondary_color }"
                                ></div>
                                <div
                                    class="w-1/2 h-3 rounded"
                                    :style="{ backgroundColor: form.secondary_color, opacity: 0.7 }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeModals"
                            class="px-4 py-2 text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="isLoading || !form.name"
                            class="bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white px-6 py-2 rounded-xl font-medium"
                        >
                            {{ showEditModal ? 'Actualizar' : 'Crear' }} Tema
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Selector de media -->
        <div
            v-if="showMediaSelector"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50"
            @click.self="showMediaSelector = false"
        >
            <div class="bg-white dark:bg-stone-800 rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-stone-200 dark:border-stone-700">
                    <h2 class="text-xl font-bold text-stone-900 dark:text-stone-100">
                        Seleccionar Imagen de Fondo
                    </h2>
                </div>

                <div class="p-6">
                    <div v-if="backgroundImages.length === 0" class="text-center py-12">
                        <div class="text-4xl mb-4">📸</div>
                        <p class="text-stone-600 dark:text-stone-400">
                            No tienes imágenes en tu galería. Sube una imagen primero.
                        </p>
                    </div>

                    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <button
                            v-for="mediaItem in backgroundImages"
                            :key="mediaItem.id"
                            @click="selectBackgroundFromMedia(mediaItem)"
                            class="group aspect-square rounded-lg overflow-hidden hover:ring-2 hover:ring-rose-500 transition-all"
                        >
                            <img
                                :src="mediaItem.public_url || mediaItem.url"
                                :alt="mediaItem.original_filename || mediaItem.filename"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
