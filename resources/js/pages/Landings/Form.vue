<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

import MediaUpload from '@/components/ui/MediaUpload.vue';

import { useLandings } from '@/composables/useLandings';
import { useMedia } from '@/composables/useMedia';
import { useThemes } from '@/composables/useThemes';
import { landingService } from '@/services/landing/LandingService';
import type { Media } from '@/types/auth';

const props = defineProps<{
    id?: number; // Optional ID for edit mode
}>();

const { landing, createLanding, updateLanding, isLoading, error: landingError, loadLanding } = useLandings();
const { loadThemes, systemThemes, userThemes } = useThemes();
const { media: galleryMedia, loadMedia } = useMedia();

// Form State
const form = ref({
    couple_names: '',
    slug: '',
    anniversary_date: '',
    bio_text: '',
    theme_id: null as number | null,
    selected_media: [] as number[], // IDs for sync
});

// Interface State
const showMediaModal = ref(false);
const localSelectedMedia = ref<number[]>([]);
const previewMedia = ref<Media[]>([]);
const draggedItemIndex = ref<number | null>(null);

const isEditing = computed(() => !!props.id);

onMounted(async () => {
    // Load themes and gallery
    await Promise.all([
        loadThemes(),
        loadMedia(),
    ]);

    if (isEditing.value && props.id) {
        await loadLanding(props.id);
        if (landing.value) {
            form.value = {
                couple_names: landing.value.couple_names,
                slug: landing.value.slug,
                anniversary_date: landing.value.anniversary_date
                    ? (landing.value.anniversary_date.includes('T') ? landing.value.anniversary_date.split('T')[0] : landing.value.anniversary_date)
                    : '',
                bio_text: landing.value.bio_text || '',
                theme_id: landing.value.theme_id,
                selected_media: landing.value.media ? landing.value.media.map((m: any) => m.id) : [],
            };

            // Populate previews
            previewMedia.value = (landing.value.media || []) as unknown as Media[];
        }
    } else {
        // Default theme selection
        if (systemThemes.value.length > 0) {
            form.value.theme_id = systemThemes.value[0].id;
        }
    }
});

const openMediaPicker = () => {
    localSelectedMedia.value = [...form.value.selected_media];
    showMediaModal.value = true;
};

const toggleMediaSelection = (mediaId: number) => {
    if (localSelectedMedia.value.includes(mediaId)) {
        localSelectedMedia.value = localSelectedMedia.value.filter(id => id !== mediaId);
    } else {
        localSelectedMedia.value.push(mediaId);
    }
};

const confirmMediaSelection = () => {
    form.value.selected_media = [...localSelectedMedia.value];
    // Sync previews from the gallery media we have loaded
    previewMedia.value = galleryMedia.value.filter(m => form.value.selected_media.includes(m.id))
        .sort((a, b) => {
            const indexA = form.value.selected_media.indexOf(a.id);
            const indexB = form.value.selected_media.indexOf(b.id);
            return indexA - indexB;
        });
    showMediaModal.value = false;
};

const removeMedia = (mediaId: number) => {
    form.value.selected_media = form.value.selected_media.filter(id => id !== mediaId);
    previewMedia.value = previewMedia.value.filter(m => m.id !== mediaId);
};

// Drag and Drop Logic
const onDragStart = (index: number) => {
    draggedItemIndex.value = index;
};

const onDragOver = (e: DragEvent) => {
    e.preventDefault();
};

const onDrop = (index: number) => {
    if (draggedItemIndex.value === null || draggedItemIndex.value === index) return;

    const items = [...previewMedia.value];
    const draggedItem = items.splice(draggedItemIndex.value, 1)[0];
    items.splice(index, 0, draggedItem);

    previewMedia.value = items;
    form.value.selected_media = items.map(item => item.id);
    draggedItemIndex.value = null;
};

const handleSubmit = async () => {
    try {
        let landingId: number;

        if (isEditing.value && props.id) {
            await updateLanding(props.id, form.value);
            landingId = props.id;
        } else {
            const newLanding = await createLanding(form.value);
            landingId = newLanding.id;
        }

        // Handle Media Sync
        const existingMediaIds = landing.value?.media ? landing.value.media.map((m: any) => m.id) : [];
        const newMediaIds = form.value.selected_media;

        const toAdd = newMediaIds.filter(id => !existingMediaIds.includes(id));
        const toRemove = existingMediaIds.filter(id => !newMediaIds.includes(id));

        for (const mid of toAdd) {
            await landingService.attachMedia(landingId, mid);
        }
        for (const mid of toRemove) {
            await landingService.detachMedia(landingId, mid);
        }

        // Handle Reorder
        const mediaOrder = form.value.selected_media.map((id, index) => ({
            media_id: id,
            sort_order: index,
        }));
        await landingService.reorderMedia(landingId, mediaOrder);

        if (!landingError.value) {
            router.visit('/landings');
        }
    } catch (e) {
        console.error(e);
    }
};

</script>

<template>
    <div class="min-h-screen bg-stone-50 dark:bg-stone-900 pb-20">
        <Head :title="isEditing ? 'Editar Landing' : 'Nueva Landing'" />

        <header class="bg-white dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700 sticky top-0 z-30">
            <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link href="/landings" class="p-2 hover:bg-stone-100 dark:hover:bg-stone-700 rounded-full transition-colors">
                        ←
                    </Link>
                    <h1 class="text-xl font-bold text-stone-900 dark:text-stone-100">
                        {{ isEditing ? 'Editar Landing' : 'Crear Nueva Landing' }}
                    </h1>
                </div>
                <div class="flex items-center gap-3">
                     <a v-if="isEditing && landing" 
                        :href="`/p/${landing.slug}`" 
                        target="_blank"
                        class="text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        Ver Pública ↗
                    </a>
                    <button
                        @click="handleSubmit"
                        :disabled="isLoading"
                        class="bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-2 rounded-xl text-sm font-medium transition-all shadow-md hover:shadow-lg"
                    >
                        {{ isLoading ? 'Guardando...' : 'Guardar Cambios' }}
                    </button>
                </div>
            </div>
        </header>

        <main class="max-w-5xl mx-auto px-6 py-10 space-y-10">
            
            <!-- Error Alert -->
            <div v-if="landingError" class="p-4 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl border border-red-200 dark:border-red-800">
                {{ landingError }}
            </div>

            <!-- 1. Detalles Básicos -->
            <section class="bg-white dark:bg-stone-800 rounded-2xl p-8 border border-stone-200 dark:border-stone-700 shadow-sm">
                <h2 class="text-lg font-bold text-stone-900 dark:text-stone-100 mb-6 flex items-center gap-2">
                    <span class="bg-rose-100 dark:bg-rose-900 text-rose-600 dark:text-rose-400 w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span>
                    Detalles de la Pareja
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-stone-700 dark:text-stone-300">Nombres de la Pareja</label>
                        <input
                            v-model="form.couple_names"
                            type="text"
                            placeholder="Ej: Juan & María"
                            class="w-full rounded-xl border-stone-200 dark:border-stone-600 bg-stone-50 dark:bg-stone-900/50 text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition-all"
                        />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-stone-700 dark:text-stone-300">Fecha de Aniversario</label>
                        <div class="relative">
                            <input
                                v-model="form.anniversary_date"
                                type="date"
                                class="w-full px-4 py-3 rounded-xl border border-stone-200 dark:border-stone-600 bg-stone-50 dark:bg-stone-900/50 text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition-all appearance-none"
                            />
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-stone-400">
                                📅
                            </div>
                        </div>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Selecciona la fecha para mostrar el contador de tiempo.</p>
                    </div>

                     <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-stone-700 dark:text-stone-300">Slug (URL personalizada)</label>
                        <div class="flex items-center">
                            <span class="bg-stone-100 dark:bg-stone-800 border border-r-0 border-stone-200 dark:border-stone-600 rounded-l-xl px-3 py-3 text-stone-500 dark:text-stone-400 text-sm">
                                lovelink.love/p/
                            </span>
                            <input
                                v-model="form.slug"
                                type="text"
                                placeholder="juan-y-maria"
                                class="flex-1 px-4 py-3 rounded-r-xl border border-stone-200 dark:border-stone-600 bg-stone-50 dark:bg-stone-900/50 text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition-all min-w-0"
                            />
                        </div>
                    </div>
                     <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-stone-700 dark:text-stone-300">Historia (Bio)</label>
                        <textarea
                            v-model="form.bio_text"
                            rows="4"
                            placeholder="Cuéntanos un poco de su historia..."
                            class="w-full px-4 py-3 rounded-xl border border-stone-200 dark:border-stone-600 bg-stone-50 dark:bg-stone-900/50 text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition-all"
                        ></textarea>
                    </div>
                </div>
            </section>

             <!-- 2. Tema Visual -->
            <section class="bg-white dark:bg-stone-800 rounded-2xl p-8 border border-stone-200 dark:border-stone-700 shadow-sm">
                <h2 class="text-lg font-bold text-stone-900 dark:text-stone-100 mb-6 flex items-center gap-2">
                    <span class="bg-rose-100 dark:bg-rose-900 text-rose-600 dark:text-rose-400 w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span>
                    Estilo Visual
                </h2>

                <div class="space-y-6">
                    <div v-if="systemThemes.length > 0">
                        <h3 class="text-xs font-bold text-stone-400 uppercase tracking-widest mb-4">Temas del Sistema</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <div 
                                v-for="theme in systemThemes" 
                                :key="theme.id"
                                @click="form.theme_id = theme.id"
                                class="cursor-pointer group relative rounded-xl overflow-hidden border-2 transition-all aspect-3/4"
                                :class="form.theme_id === theme.id ? 'border-rose-500 ring-2 ring-rose-500/20 scale-105' : 'border-transparent hover:border-stone-300 dark:hover:border-stone-600'"
                            >
                                <div class="absolute inset-0" :style="{ backgroundColor: theme.bg_color }"></div>
                                <div class="absolute inset-x-4 top-4 h-2 rounded-full opacity-50" :style="{ backgroundColor: theme.primary_color }"></div>
                                <div class="absolute inset-x-4 top-8 h-2 w-2/3 rounded-full opacity-30" :style="{ backgroundColor: theme.secondary_color }"></div>
                                <div class="absolute inset-0 flex items-center justify-center bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="text-[10px] font-bold bg-white/90 px-2 py-1 rounded text-stone-900 shadow-sm">
                                        {{ theme.name }}
                                    </span>
                                </div>
                                <div v-if="form.theme_id === theme.id" class="absolute top-2 right-2 bg-rose-500 text-white w-5 h-5 rounded-full flex items-center justify-center shadow-md text-[10px]">
                                    ✓
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="userThemes.length > 0">
                        <h3 class="text-xs font-bold text-stone-400 uppercase tracking-widest mb-4">Mis Temas</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <div 
                                v-for="theme in userThemes" 
                                :key="theme.id"
                                @click="form.theme_id = theme.id"
                                class="cursor-pointer group relative rounded-xl overflow-hidden border-2 transition-all aspect-3/4"
                                :class="form.theme_id === theme.id ? 'border-rose-500 ring-2 ring-rose-500/20 scale-105' : 'border-transparent hover:border-stone-300 dark:hover:border-stone-600'"
                            >
                                <div class="absolute inset-0" :style="{ backgroundColor: theme.bg_color }"></div>
                                <div class="absolute inset-x-4 top-4 h-2 rounded-full opacity-50" :style="{ backgroundColor: theme.primary_color }"></div>
                                <div class="absolute inset-x-4 top-8 h-2 w-2/3 rounded-full opacity-30" :style="{ backgroundColor: theme.secondary_color }"></div>
                                <div v-if="form.theme_id === theme.id" class="absolute top-2 right-2 bg-rose-500 text-white w-5 h-5 rounded-full flex items-center justify-center shadow-md text-[10px]">
                                    ✓
                                </div>
                                <p class="absolute bottom-2 inset-x-2 text-[10px] font-medium text-center truncate bg-white/80 dark:bg-stone-900/80 rounded px-1">{{ theme.name }}</p>
                            </div>
                        </div>
                    </div>

                    <div 
                        @click="form.theme_id = null"
                        class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer hover:bg-stone-50 dark:hover:bg-stone-700/50 transition-colors"
                        :class="form.theme_id === null ? 'border-rose-500 bg-rose-50/50 dark:bg-rose-900/10' : 'border-stone-200 dark:border-stone-700'"
                    >
                        <div class="w-10 h-10 rounded-full bg-white dark:bg-stone-700 border border-stone-200 dark:border-stone-600 flex items-center justify-center text-stone-400 px-2 text-xl">
                            🚫
                        </div>
                        <div>
                            <div class="font-medium text-sm text-stone-900 dark:text-stone-100">Sin Tema Específico</div>
                            <div class="text-xs text-stone-500 dark:text-stone-400">Usar diseño por defecto</div>
                        </div>
                        <div v-if="form.theme_id === null" class="ml-auto text-rose-600">✓</div>
                    </div>
                </div>
            </section>

             <!-- 3. Galería Multimedia -->
             <section class="bg-white dark:bg-stone-800 rounded-2xl p-8 border border-stone-200 dark:border-stone-700 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-stone-900 dark:text-stone-100 flex items-center gap-2">
                        <span class="bg-rose-100 dark:bg-rose-900 text-rose-600 dark:text-rose-400 w-8 h-8 rounded-full flex items-center justify-center text-sm">3</span>
                        Galería de Fotos
                    </h2>
                    <button
                        @click="openMediaPicker"
                        class="text-sm bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400 dark:hover:bg-rose-900/50 px-4 py-2 rounded-lg font-medium transition-colors"
                    >
                        Gestionar Galería
                    </button>
                </div>

                <div class="mb-6 text-sm text-stone-500 dark:text-stone-400 bg-stone-50 dark:bg-stone-900/50 p-4 rounded-xl border border-stone-100 dark:border-stone-700">
                    <p>📸 <strong>Personaliza tu historia.</strong> La primera foto será la portada. Puedes seleccionar fotos existentes o subir nuevas.</p>
                </div>
                 
                 <div v-if="previewMedia.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
                    <div 
                        v-for="(media, index) in previewMedia" 
                        :key="media.id" 
                        draggable="true"
                        @dragstart="onDragStart(index)"
                        @dragover="onDragOver"
                        @drop="onDrop(index)"
                        class="group relative aspect-square rounded-xl overflow-hidden border border-stone-200 dark:border-stone-700 bg-stone-100 dark:bg-stone-900 shadow-sm cursor-move active:scale-95 transition-transform"
                    >
                        <img :src="media.url || media.path" class="w-full h-full object-cover transition-transform group-hover:scale-110 duration-500 pointer-events-none" />
                        
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                             <button @click="removeMedia(media.id)" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow-lg transition-all transform hover:scale-110" title="Quitar">
                                ✕
                            </button>
                        </div>

                         <div v-if="index === 0" class="absolute top-2 left-2 bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-md uppercase tracking-wide">
                            Portada
                        </div>
                    </div>
                 </div>

                 <div v-else class="text-center py-12 border-2 border-dashed border-stone-200 dark:border-stone-700 rounded-2xl">
                    <p class="text-stone-500 dark:text-stone-400">No has seleccionado ninguna foto aún</p>
                    <button
                        @click="openMediaPicker"
                        class="mt-2 text-rose-600 font-medium hover:underline"
                    >
                        Abrir Galería
                    </button>
                </div>
                 
                 <p class="text-[11px] text-stone-400 italic text-center mt-6">* Las fotos se guardarán definitivamente al pulsar "Guardar Cambios".</p>

            </section>

        </main>

        <!-- Gallery Modal -->
        <div v-if="showMediaModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-white dark:bg-stone-800 w-full max-w-5xl h-[85vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden">
                <div class="p-4 border-b border-stone-200 dark:border-stone-700 flex justify-between items-center bg-white dark:bg-stone-800">
                    <h3 class="font-bold text-lg dark:text-stone-100">Seleccionar Fotos</h3>
                    <button @click="showMediaModal = false" class="text-stone-500 hover:text-stone-800 dark:hover:text-stone-200 text-2xl leading-none">&times;</button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 bg-stone-50 dark:bg-stone-900">
                    <!-- Upload Section -->
                    <div class="mb-8">
                        <h4 class="text-sm font-bold text-stone-400 uppercase tracking-wider mb-3">Subir Nuevas</h4>
                        <MediaUpload @uploaded="loadMedia" />
                    </div>

                    <!-- Gallery Grid -->
                    <div>
                        <h4 class="text-sm font-bold text-stone-400 uppercase tracking-wider mb-3">Galería Existente</h4>
                        <div v-if="galleryMedia.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <div
                                v-for="item in galleryMedia"
                                :key="item.id"
                                @click="item.mime_type.startsWith('image/') ? toggleMediaSelection(item.id) : null"
                                :class="[
                                    'relative aspect-square rounded-lg overflow-hidden border-2 cursor-pointer transition-all',
                                    localSelectedMedia.includes(item.id) ? 'border-rose-500 ring-2 ring-rose-500/20 shadow-lg' : 'border-transparent',
                                    !item.mime_type.startsWith('image/') ? 'opacity-50 grayscale cursor-not-allowed' : ''
                                ]"
                            >
                                <img
                                    :src="item.url || item.path"
                                    class="w-full h-full object-cover"
                                />
                                <div v-if="localSelectedMedia.includes(item.id)" class="absolute top-2 right-2 w-6 h-6 bg-rose-500 rounded-full flex items-center justify-center text-white text-xs shadow-md border-2 border-white">
                                    ✓
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-stone-500">
                            No hay fotos en tu galería. ¡Sube algunas arriba!
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 flex justify-end gap-3">
                    <button
                        @click="showMediaModal = false"
                        class="px-5 py-2 text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-700 rounded-xl transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="confirmMediaSelection"
                        class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-lg shadow-rose-600/20 transition-all font-medium"
                    >
                        Confirmar Selección ({{ localSelectedMedia.length }})
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
