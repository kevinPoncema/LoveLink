<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

import { useLandings } from '@/composables/useLandings';
import { useThemes } from '@/composables/useThemes';
import { landingService } from '@/services/landing/LandingService';
import { mediaService } from '@/services/media/MediaService';

const props = defineProps<{
    id?: number; // Optional ID for edit mode
}>();

const { landing, createLanding, updateLanding, isLoading, error: landingError, loadLanding } = useLandings();
const { loadThemes, systemThemes } = useThemes();

// Form State
const form = ref({
    couple_names: '',
    slug: '',
    anniversary_date: '',
    bio_text: '',
    theme_id: null as number | null,
    // Note: Media handling in LandingService is separate (attach/detach), 
    // but typically we want to submit basic info first.
});

// For media management
const currentLandingMedia = ref<any[]>([]); 

const isEditing = computed(() => !!props.id);

onMounted(async () => {
    // Load themes
    await loadThemes();

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
            };
            if (landing.value.media) {
                currentLandingMedia.value = landing.value.media;
            }
        }
    } else {
         // Default theme selection
        if (systemThemes.value.length > 0) {
            form.value.theme_id = systemThemes.value[0].id;
        }
    }
});

const handleSubmit = async () => {
    try {
        if (isEditing.value && props.id) {
            await updateLanding(props.id, form.value);
            // Media updates are handled separately via MediaUpload component events usually, 
            // or we might need to handle it here if it's just selection.
            // But since MediaUpload usually uploads immediately to Media library, 
            // the linking to Landing happens either via separate calls or as part of update if backend supports syncing.
            // The LandingService has attachMedia/detachMedia methods.
            // For MVP, we'll assume the form saves the data, and media management is done in a separate section or subsequent step,
            // OR we redirect to index.
            router.visit('/landings');
        } else {
            const newLanding = await createLanding(form.value);
            // Redirect to edit to add media, or index
            router.visit(`/landings/${newLanding.id}/edit`);
        }
    } catch (e) {
        console.error(e);
    }
};

// Handle Media events
// Since LandingService has specific attach/detach logic which might differ from generic usage,
// we'll need to implement methods that call landingService.attachMedia
// But we need the landing ID first. So media management is only enabled in Edit mode or after creation.

const handleAttachMedia = async (mediaId: number) => {
    if (!props.id) return;
    try {
        // Calculate next sort order? Backend usually handles it or we send it.
        // Frontend simple: just attach.
        await landingService.attachMedia(props.id, mediaId);
        // Reload landing to get updated media list with pivot data
        await loadLanding(props.id);
        if (landing.value?.media) {
            currentLandingMedia.value = landing.value.media;
        }
    } catch (e) {
        console.error("Error attaching media", e);
    }
};

const handleDetachMedia = async (mediaId: number) => {
    if (!props.id) return;
    try {
        await landingService.detachMedia(props.id, mediaId);
        // Reload
         await loadLanding(props.id);
        if (landing.value?.media) {
            currentLandingMedia.value = landing.value.media;
        }
    } catch (e) {
        console.error("Error detaching media", e);
    }
};

/* Unused but kept for future DnD implementation
const handleReorder = async (mediaOrder: { media_id: number; sort_order: number }[]) => {
     if (!props.id) return;
    try {
        await landingService.reorderMedia(props.id, mediaOrder);
         await loadLanding(props.id);
        if (landing.value?.media) {
            currentLandingMedia.value = landing.value.media;
        }
    } catch (e) {
        console.error("Error reordering", e);
    }
};
*/

// Helper for UI
/* Unused helper
const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    return new Date(dateStr).toISOString().split('T')[0];
};
*/

</script>

<template>
    <div class="min-h-screen bg-stone-50 dark:bg-stone-900 font-sans">
        <Head :title="isEditing ? 'Editar Landing' : 'Nueva Landing'" />

        <header class="bg-white dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700 sticky top-0 z-30">
            <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link href="/landings" class="text-stone-500 hover:text-stone-800 dark:text-stone-400 dark:hover:text-stone-200 transition-colors">
                        ← Cancelar
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
                        class="bg-stone-900 dark:bg-stone-100 hover:bg-stone-800 dark:hover:bg-white text-white dark:text-stone-900 px-6 py-2 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
                            class="w-full rounded-xl border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-900 text-stone-900 dark:text-stone-100 focus:ring-rose-500 focus:border-rose-500"
                        />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-stone-700 dark:text-stone-300">Fecha de Aniversario</label>
                        <input
                            v-model="form.anniversary_date"
                            type="date"
                            class="w-full rounded-xl border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-900 text-stone-900 dark:text-stone-100 focus:ring-rose-500 focus:border-rose-500"
                        />
                    </div>
                     <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-stone-700 dark:text-stone-300">Slug (URL personalizada)</label>
                        <div class="flex items-center">
                            <span class="bg-stone-100 dark:bg-stone-800 border border-r-0 border-stone-200 dark:border-stone-700 rounded-l-xl px-3 py-2 text-stone-500 dark:text-stone-400 text-sm">
                                uspage.love/p/
                            </span>
                            <input
                                v-model="form.slug"
                                type="text"
                                placeholder="juan-y-maria"
                                class="flex-1 rounded-r-xl border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-900 text-stone-900 dark:text-stone-100 focus:ring-rose-500 focus:border-rose-500 min-w-0"
                            />
                        </div>
                    </div>
                     <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-stone-700 dark:text-stone-300">Historia (Bio)</label>
                        <textarea
                            v-model="form.bio_text"
                            rows="4"
                            placeholder="Cuéntanos un poco de su historia..."
                            class="w-full rounded-xl border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-900 text-stone-900 dark:text-stone-100 focus:ring-rose-500 focus:border-rose-500"
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

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div 
                        v-for="theme in systemThemes" 
                        :key="theme.id"
                        @click="form.theme_id = theme.id"
                        class="cursor-pointer group relative rounded-xl overflow-hidden border-2 transition-all aspect-3/4"
                        :class="form.theme_id === theme.id ? 'border-rose-500 ring-2 ring-rose-500/20 scale-105' : 'border-transparent hover:border-stone-300 dark:hover:border-stone-600'"
                    >
                        <div class="absolute inset-0" :style="{ backgroundColor: theme.bg_color }"></div>
                        <!-- Preview Abstracto -->
                        <div class="absolute inset-x-4 top-4 h-2 rounded-full opacity-50" :style="{ backgroundColor: theme.primary_color }"></div>
                        <div class="absolute inset-x-4 top-8 h-2 w-2/3 rounded-full opacity-30" :style="{ backgroundColor: theme.secondary_color }"></div>
                        
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/10">
                            <span class="text-xs font-bold bg-white/90 px-2 py-1 rounded-md text-stone-900 shadow-sm">
                                {{ theme.name }}
                            </span>
                        </div>
                        
                        <div v-if="form.theme_id === theme.id" class="absolute top-2 right-2 bg-rose-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-md">
                            ✓
                        </div>
                    </div>
                     <!-- Link to create theme -->
                     <Link href="/themes" class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-stone-200 dark:border-stone-700 hover:border-rose-500 hover:text-rose-500 text-stone-400 transition-colors aspect-3/4">
                        <span class="text-2xl mb-2">+</span>
                        <span class="text-xs font-medium">Crear Tema</span>
                     </Link>
                </div>
            </section>

             <!-- 3. Galería Multimedia -->
             <section v-if="isEditing" class="bg-white dark:bg-stone-800 rounded-2xl p-8 border border-stone-200 dark:border-stone-700 shadow-sm">
                <h2 class="text-lg font-bold text-stone-900 dark:text-stone-100 mb-6 flex items-center gap-2">
                    <span class="bg-rose-100 dark:bg-rose-900 text-rose-600 dark:text-rose-400 w-8 h-8 rounded-full flex items-center justify-center text-sm">3</span>
                    Galería de Fotos
                </h2>
                <div class="mb-4 text-sm text-stone-500 dark:text-stone-400">
                    <p>Sube y selecciona las fotos. <strong>La primera foto será la portada.</strong> Utiliza la biblioteca multimedia.</p>
                </div>

                 <!-- We can try to reuse MediaUpload or implement a specific selector -->
                 <!-- Here we'll implement a simple list of attached media and a button to open Media Picker -->
                 <!-- Since MediaUpload in this project seems to handle upload AND selection/emit -->
                 <!-- Let's check MediaUpload component structure if possible, but I can't check everything. -->
                 <!-- I'll use a simple approach: Grid of attached images with 'Remove' button. And a specialized 'Add Media' area. -->
                 <!-- Reusing logic: The user asked to reuse MediaService and UI. -->
                 <!-- I'll use a file input that uploads via MediaService and then attaches. -->
                 
                 <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
                    <div v-for="(media, index) in currentLandingMedia" :key="media.id" class="group relative aspect-square rounded-xl overflow-hidden border border-stone-200 dark:border-stone-700 bg-stone-100 dark:bg-stone-900">
                        <img :src="media.url || media.path" class="w-full h-full object-cover" />
                        
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                             <button @click="handleDetachMedia(media.id)" class="text-white hover:text-red-400 p-1" title="Quitar">
                                🗑️
                            </button>
                        </div>

                         <div class="absolute top-1 left-1 bg-black/50 text-white text-xs px-1.5 py-0.5 rounded">
                            {{ index === 0 ? 'Portada' : index }}
                        </div>
                    </div>
                    
                    <!-- Add Button placeholder (Ideally opens a modal with Media Library) -->
                    <!-- Since I don't have the full Media Library modal code, I'll instruct user or use a simple file upload that auto-attaches -->
                    <label class="flex flex-col items-center justify-center aspect-square rounded-xl border-2 border-dashed border-stone-200 dark:border-stone-700 hover:border-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/10 cursor-pointer transition-colors text-stone-400 hover:text-rose-500">
                        <span class="text-3xl mb-2">+</span>
                        <span class="text-xs text-center px-2">Subir/Añadir Foto</span>
                        <input type="file" multiple class="hidden" accept="image/*" @change="async (e) => {
                             // Quick implementation of upload & attach
                             const files = (e.target as HTMLInputElement).files;
                             if (!files || !files.length) return;
                             
                             for(let i=0; i<files.length; i++) {
                                 // Upload
                                 const uploaded = await mediaService.uploadMedia(files[i]);
                                 // Attach
                                 await handleAttachMedia(uploaded.id);
                             }
                        }" />
                    </label>
                 </div>
                 
                 <p class="text-xs text-stone-400 italic">* El orden es automático por ahora. Elimina y vuelve a añadir para cambiar el orden.</p>

            </section>
             <div v-else class="text-center p-8 bg-stone-50 dark:bg-stone-800/50 rounded-xl border-2 border-dashed border-stone-200 dark:border-stone-700 text-stone-500">
                Guarda la landing primero para añadir fotos.
            </div>

        </main>
    </div>
</template>
