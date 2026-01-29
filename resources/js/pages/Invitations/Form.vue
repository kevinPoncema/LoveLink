<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

import MediaUpload from '@/components/ui/MediaUpload.vue';

import { useInvitations } from '@/composables/useInvitations';
import { useMedia } from '@/composables/useMedia';
import { useThemes } from '@/composables/useThemes'; // Removed unused Theme type import
import type { CreateInvitationData } from '@/services/invitation/InvitationService';
import type { Media } from '@/types/auth';

const props = defineProps<{
    id?: number; // Optional ID for edit mode
}>();

const { invitation, createInvitation, updateInvitation, isLoading, error: invError, loadInvitation, attachMedia, detachMedia } = useInvitations();
const { media: galleryMedia, loadMedia } = useMedia();
const { loadThemes, systemThemes, userThemes } = useThemes();

// Form State
const form = ref({
    title: '',
    slug: '',
    yes_message: '¡Sabía que dirías que sí! ❤️',
    no_messages: ['¡Oh no! ¿Seguro? 🥺', '¡Piénsalo bien!', '¡No te rindas!'],
    is_published: false,
    theme_id: null as number | null,
    selected_media: [] as number[], // IDs of selected media
});

const newMessage = ref(''); // For adding new "no" messages

// Interface State
const showMediaModal = ref(false);
const localSelectedMedia = ref<number[]>([]); // For the modal selection
const previewMedia = ref<Media[]>([]); // To show previews in the form

const isEditing = computed(() => !!props.id);

onMounted(async () => {
    if (isEditing.value && props.id) {
        await loadInvitation(props.id);
        if (invitation.value) {
            form.value = {
                title: invitation.value.title,
                slug: invitation.value.slug,
                yes_message: invitation.value.yes_message || '¡Sabía que dirías que sí! ❤️',
                no_messages: invitation.value.no_messages && invitation.value.no_messages.length > 0
                    ? [...invitation.value.no_messages]
                    : ['¡Oh no! ¿Seguro? 🥺', '¡Piénsalo bien!', '¡No te rindas!'],
                is_published: !!invitation.value.is_published,
                theme_id: invitation.value.theme_id || null,
                selected_media: invitation.value.media ? invitation.value.media.map((m: any) => m.id) : [],
            };

            // Populate preview media from invitation data
             previewMedia.value = (invitation.value.media || []) as unknown as Media[];
        }
    } else {
        // Defaults
    }

    // Load gallery for the modal
    loadMedia();
    loadThemes();
});

const addNoMessage = () => {
    if (newMessage.value.trim()) {
        form.value.no_messages.push(newMessage.value.trim());
        newMessage.value = '';
    }
};

const removeNoMessage = (index: number) => {
    form.value.no_messages.splice(index, 1);
};

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

    // Init previews based on selection
    previewMedia.value = galleryMedia.value.filter(m => form.value.selected_media.includes(m.id));

    showMediaModal.value = false;
};

const removeMedia = (mediaId: number) => {
    form.value.selected_media = form.value.selected_media.filter(id => id !== mediaId);
    previewMedia.value = previewMedia.value.filter(m => m.id !== mediaId);
};

const handleSubmit = async () => {
    const data: CreateInvitationData = {
        title: form.value.title,
        slug: form.value.slug,
        yes_message: form.value.yes_message,
        no_messages: form.value.no_messages,
        theme_id: form.value.theme_id,
    };

    try {
        let invitationId: number;

        if (isEditing.value && props.id) {
            await updateInvitation(props.id, {
                ...data,
                is_published: form.value.is_published,
            });
            invitationId = props.id;
        } else {
            const newInvitation = await createInvitation(data);
            invitationId = newInvitation.id;

            // If published state differs from default (false), update it?
            if (form.value.is_published) {
                await updateInvitation(invitationId, { is_published: true });
            }
        }

        // Handle Media Sync
        // Existing media ids
        const existingMediaIds = invitation.value?.media ? invitation.value.media.map((m: any) => m.id) : [];
        const newMediaIds = form.value.selected_media;

        const toAdd = newMediaIds.filter(id => !existingMediaIds.includes(id));
        const toRemove = existingMediaIds.filter(id => !newMediaIds.includes(id));

        for (const mid of toAdd) {
            await attachMedia(invitationId, mid);
        }
        for (const mid of toRemove) {
            await detachMedia(invitationId, mid);
        }

        if (!invError.value) {
            // Redirect using Inertia router
            router.visit('/invitations');
        }
    } catch (e) {
        console.error(e);
    }
};

// Auto-generate slug from title if not manually edited and creating
const generateSlug = () => {
    if (!isEditing.value && !form.value.slug && form.value.title) {
        form.value.slug = form.value.title
            .toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-');
    }
};
</script>

<template>
    <div class="min-h-screen bg-stone-50 dark:bg-stone-900 pb-20">
        <Head :title="isEditing ? 'Editar Invitación' : 'Nueva Invitación'" />

        <header class="bg-white dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700 sticky top-0 z-30">
            <div class="max-w-4xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link
                            href="/invitations"
                            class="p-2 hover:bg-stone-100 dark:hover:bg-stone-700 rounded-full transition-colors"
                        >
                            ←
                        </Link>
                        <div>
                            <h1 class="text-xl font-bold text-stone-900 dark:text-stone-100">
                                {{ isEditing ? 'Editar Invitación' : 'Crear Nueva Invitación' }}
                            </h1>
                        </div>
                    </div>

                    <button
                        @click="handleSubmit"
                        :disabled="isLoading || !form.title"
                        class="bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-2 rounded-xl text-sm font-medium transition-all shadow-md hover:shadow-lg"
                    >
                        {{ isLoading ? 'Guardando...' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-6 py-8">
            <div v-if="invError" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg border border-red-200 dark:border-red-800">
                {{ invError }}
            </div>

            <div class="grid gap-8">
                <!-- Información Básica -->
                <section class="bg-white dark:bg-stone-800 rounded-2xl shadow-sm border border-stone-200 dark:border-stone-700 p-6">
                    <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 dark:text-stone-100">
                        <span>📝</span> Detalles Principales
                    </h2>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Título de la Invitación *</label>
                            <input
                                v-model="form.title"
                                @blur="generateSlug"
                                type="text"
                                class="w-full px-4 py-3 rounded-xl border border-stone-200 dark:border-stone-600 bg-stone-50 dark:bg-stone-900/50 text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition-all"
                                placeholder="Ej: San Valentín 2024"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">URL Personalizada (Slug)</label>
                            <div class="flex items-center">
                                <span class="bg-stone-100 dark:bg-stone-700 text-stone-500 dark:text-stone-400 px-4 py-3 rounded-l-xl border-y border-l border-stone-200 dark:border-stone-600 text-sm">
                                    /invitation/
                                </span>
                                <input
                                    v-model="form.slug"
                                    type="text"
                                    class="flex-1 px-4 py-3 rounded-r-xl border border-stone-200 dark:border-stone-600 bg-stone-50 dark:bg-stone-900/50 text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition-all"
                                    placeholder="mi-invitacion-especial"
                                />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Multimedia -->
                <section class="bg-white dark:bg-stone-800 rounded-2xl shadow-sm border border-stone-200 dark:border-stone-700 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold flex items-center gap-2 dark:text-stone-100">
                            <span>📸</span> Fotos y Recuerdos
                        </h2>
                        <button
                            @click="openMediaPicker"
                            class="text-sm bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400 dark:hover:bg-rose-900/50 px-4 py-2 rounded-lg font-medium transition-colors"
                        >
                            Gestionar Galería
                        </button>
                    </div>

                    <div v-if="previewMedia.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                         <div
                            v-for="item in previewMedia"
                            :key="item.id"
                            class="group relative aspect-square bg-stone-100 dark:bg-stone-900 rounded-xl overflow-hidden border border-stone-200 dark:border-stone-700"
                        >
                            <img
                                :src="item.url"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button
                                    @click="removeMedia(item.id)"
                                    class="bg-red-500 text-white p-2 rounded-full transform scale-0 group-hover:scale-100 transition-transform hover:bg-red-600"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-12 border-2 border-dashed border-stone-200 dark:border-stone-700 rounded-xl">
                        <p class="text-stone-500 dark:text-stone-400">No has seleccionado ninguna foto aún</p>
                        <button
                            @click="openMediaPicker"
                            class="mt-2 text-rose-600 font-medium hover:underline"
                        >
                            Abrir Galería
                        </button>
                    </div>

                    <p class="mt-4 text-xs text-stone-500 dark:text-stone-400 text-center">
                        Estas fotos rotarán automáticamente si la persona intenta rechazar la invitación 😉
                    </p>
                </section>

                <!-- Selección de Tema -->
                <section class="bg-white dark:bg-stone-800 rounded-2xl shadow-sm border border-stone-200 dark:border-stone-700 p-6">
                    <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 dark:text-stone-100">
                        <span>🎨</span> Tema Visual
                    </h2>

                    <div class="space-y-6">
                        <!-- System Themes -->
                        <div v-if="systemThemes.length > 0">
                            <h3 class="text-xs font-bold text-stone-500 dark:text-stone-400 mb-3 uppercase tracking-wider">Temas del Sistema</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                <div 
                                    v-for="theme in systemThemes" 
                                    :key="theme.id"
                                    @click="form.theme_id = theme.id"
                                    class="cursor-pointer group"
                                >
                                    <div 
                                        class="aspect-3/4 rounded-xl border-2 transition-all relative overflow-hidden flex flex-col items-center justify-center p-2"
                                        :class="form.theme_id === theme.id ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-stone-200 dark:border-stone-700 hover:border-rose-300'"
                                        :style="{ backgroundColor: theme.background_color || '#ffffff' }"
                                    >
                                        <!-- Background Image Preview -->
                                        <img 
                                            v-if="theme.bg_image_url" 
                                            :src="theme.bg_image_url" 
                                            class="absolute inset-0 w-full h-full object-cover opacity-50" 
                                        />

                                        <!-- Content Preview -->
                                        <div class="relative z-10 w-full text-center">
                                            <div 
                                                class="font-bold text-lg mb-1"
                                                :style="{ color: theme.primary_color || '#e11d48' }"
                                            >
                                                Título
                                            </div>
                                            <div 
                                                class="px-3 py-1 rounded-full text-xs font-bold text-white mb-2 inline-block shadow-sm"
                                                :style="{ backgroundColor: theme.primary_color || '#e11d48' }"
                                            >
                                                ¡SÍ!
                                            </div>
                                        </div>

                                        <!-- Selection Check -->
                                        <div 
                                            class="absolute top-2 right-2 w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs shadow-md transition-transform duration-200"
                                            :class="form.theme_id === theme.id ? 'scale-100' : 'scale-0'"
                                        >
                                            ✓
                                        </div>
                                    </div>
                                    <p class="text-xs text-center mt-2 font-medium text-stone-600 dark:text-stone-400 truncate">{{ theme.name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- User Themes -->
                        <div v-if="userThemes.length > 0">
                            <h3 class="text-xs font-bold text-stone-500 dark:text-stone-400 mb-3 uppercase tracking-wider">Mis Temas</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                <div 
                                    v-for="theme in userThemes" 
                                    :key="theme.id"
                                    @click="form.theme_id = theme.id"
                                    class="cursor-pointer group"
                                >
                                     <div 
                                        class="aspect-3/4 rounded-xl border-2 transition-all relative overflow-hidden flex flex-col items-center justify-center p-2"
                                        :class="form.theme_id === theme.id ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-stone-200 dark:border-stone-700 hover:border-rose-300'"
                                        :style="{ backgroundColor: theme.background_color || '#ffffff' }"
                                    >
                                        <img 
                                            v-if="theme.bg_image_url" 
                                            :src="theme.bg_image_url" 
                                            class="absolute inset-0 w-full h-full object-cover opacity-50" 
                                        />
                                        <div class="relative z-10 w-full text-center">
                                            <div 
                                                class="font-bold text-lg mb-1"
                                                :style="{ color: theme.primary_color || '#e11d48' }"
                                            >
                                                Título
                                            </div>
                                            <div 
                                                class="px-3 py-1 rounded-full text-xs font-bold text-white mb-2 inline-block shadow-sm"
                                                :style="{ backgroundColor: theme.primary_color || '#e11d48' }"
                                            >
                                                ¡SÍ!
                                            </div>
                                        </div>
                                        <div 
                                            class="absolute top-2 right-2 w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs shadow-md transition-transform duration-200"
                                            :class="form.theme_id === theme.id ? 'scale-100' : 'scale-0'"
                                        >
                                            ✓
                                        </div>
                                    </div>
                                    <p class="text-xs text-center mt-2 font-medium text-stone-600 dark:text-stone-400 truncate">{{ theme.name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Default Option -->
                        <div 
                            @click="form.theme_id = null"
                            class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer hover:bg-stone-50 dark:hover:bg-stone-700/50 transition-colors"
                            :class="form.theme_id === null ? 'border-rose-500 bg-rose-50/50 dark:bg-rose-900/10' : 'border-stone-200 dark:border-stone-700'"
                        >
                            <div class="w-10 h-10 rounded-full bg-white dark:bg-stone-700 border border-stone-200 dark:border-stone-600 flex items-center justify-center text-stone-400">
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

                <!-- Personalización de Botones -->
                <section class="bg-white dark:bg-stone-800 rounded-2xl shadow-sm border border-stone-200 dark:border-stone-700 p-6">
                    <h2 class="text-lg font-semibold mb-6 flex items-center gap-2 dark:text-stone-100">
                        <span>💬</span> Mensajes de Reacción
                    </h2>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Mensaje al decir "SÍ" 💚</label>
                            <input
                                v-model="form.yes_message"
                                type="text"
                                class="w-full px-4 py-3 rounded-xl border border-stone-200 dark:border-stone-600 bg-stone-50 dark:bg-stone-900/50 text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none transition-all"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Mensajes de insistencia (Botón "NO") 💔</label>
                            <div class="space-y-3">
                                <div v-for="(msg, index) in form.no_messages" :key="index" class="flex items-center gap-2">
                                     <span class="text-stone-400 text-sm font-mono">{{ index + 1 }}.</span>
                                     <input
                                        v-model="form.no_messages[index]"
                                        class="flex-1 px-3 py-2 rounded-lg border border-stone-200 dark:border-stone-600 bg-stone-50 dark:bg-stone-900/50 text-sm"
                                     />
                                     <button
                                        @click="removeNoMessage(index)"
                                        class="text-red-500 hover:bg-red-50 p-2 rounded-lg"
                                        title="Eliminar mensaje"
                                     >
                                        🗑️
                                     </button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="newMessage"
                                        @keyup.enter="addNoMessage"
                                        placeholder="Añadir otro mensaje de persuasión..."
                                        class="flex-1 px-3 py-2 rounded-lg border border-stone-200 dark:border-stone-600 bg-stone-50 dark:bg-stone-900/50 text-sm"
                                    />
                                    <button
                                        @click="addNoMessage"
                                        class="text-rose-600 hover:bg-rose-50 px-3 py-2 rounded-lg font-medium text-sm"
                                    >
                                        + Añadir
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-stone-500 mt-2">Estos mensajes se mostrarán en orden cada vez que intenten hacer clic en "No".</p>
                        </div>
                    </div>
                </section>

                <!-- Opciones -->
                <section class="bg-white dark:bg-stone-800 rounded-2xl shadow-sm border border-stone-200 dark:border-stone-700 p-6">
                    <div class="flex items-center gap-3">
                        <input
                            id="published"
                            v-model="form.is_published"
                            type="checkbox"
                            class="w-5 h-5 rounded text-rose-600 focus:ring-rose-500 border-gray-300 dark:border-gray-600 dark:bg-stone-700"
                        />
                        <label for="published" class="select-none cursor-pointer">
                            <span class="block font-medium text-stone-900 dark:text-stone-100">Publicar Invitación</span>
                            <span class="block text-sm text-stone-500 dark:text-stone-400">Si está desactivado, solo tú podrás verla.</span>
                        </label>
                    </div>
                </section>
            </div>
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
                                    localSelectedMedia.includes(item.id)
                                        ? 'border-rose-500 ring-4 ring-rose-500/20'
                                        : 'border-transparent hover:border-stone-300 dark:hover:border-stone-600',
                                    !item.mime_type.startsWith('image/') ? 'opacity-50 grayscale cursor-not-allowed' : ''
                                ]"
                            >
                                <img
                                    v-if="item.mime_type.startsWith('image/')"
                                    :src="item.url"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center bg-stone-200 dark:bg-stone-800 text-stone-500">
                                    📄
                                </div>

                                <!-- Selection Indicator -->
                                <div v-if="localSelectedMedia.includes(item.id)" class="absolute top-2 right-2 w-6 h-6 bg-rose-500 rounded-full flex items-center justify-center text-white text-xs shadow-sm">
                                    ✓
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-stone-500">
                            Cargando galería o vacía...
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
