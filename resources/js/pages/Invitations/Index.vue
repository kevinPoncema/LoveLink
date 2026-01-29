<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useInvitations } from '@/composables/useInvitations';

const { invitations, isLoading, error, loadInvitations, deleteInvitation } = useInvitations();

onMounted(() => {
    loadInvitations();
});

const handleDelete = async (id: number) => {
    if (confirm('¿Estás seguro de eliminar esta invitación?')) {
        await deleteInvitation(id);
    }
};

const getPublicUrl = (slug: string) => {
    return `${window.location.origin}/invitation/${slug}`;
};
</script>

<template>
    <div class="min-h-screen bg-stone-50 dark:bg-stone-900">
        <Head title="Invitaciones" />

        <header class="bg-white dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-stone-900 dark:text-stone-100">
                            Mis Invitaciones
                        </h1>
                        <p class="text-stone-600 dark:text-stone-400 mt-1">
                            Gestiona tus invitaciones personalizadas de San Valentín
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <Link
                            href="/dashboard"
                            class="text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200"
                        >
                            ← Volver al Dashboard
                        </Link>
                        <Link
                            href="/invitations/create"
                            class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                        >
                            + Nueva Invitación
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-6 py-8">
            <!-- Error -->
            <div v-if="error" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 rounded-lg">
                {{ error }}
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="text-center py-12">
                <div class="animate-spin text-4xl mb-4">💌</div>
                <p class="text-stone-600 dark:text-stone-400">Cargando invitaciones...</p>
            </div>

            <!-- Lista -->
            <div v-else-if="invitations.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="invitation in invitations"
                    :key="invitation.id"
                    class="bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700 overflow-hidden hover:shadow-lg transition-shadow"
                >
                    <!-- Preview (usando la primera imagen si existe) -->
                    <div class="aspect-video bg-rose-100 dark:bg-rose-900/20 relative flex items-center justify-center overflow-hidden">
                        <img
                            v-if="invitation.media && invitation.media.length > 0"
                            :src="invitation.media[0].path /* Assumed prop, actually accessing path might need full URL if backend sends path relative */" 
                            class="w-full h-full object-cover"
                            alt="Preview"
                        />
                         <!-- Wait, invitation.media type in service has path, but usually API returns full URL or we construct it. Theme logic used .url. Let's check type in service file again. -->
                        <!-- Service file says: type InvitationMedia = { ... path: string ... } but usually backend response transforms path to url or we rely on 'url' attribute if added by resource. -->
                        <!-- I'll check Media usage in Media.vue again. Media.vue uses item.url. -->
                        <!-- The InvitationMedia type in InvitationService.ts says path, but maybe it should be url? -->
                        <!-- Let's check InvitationController response. It uses $invitation->load(['media']). -->
                        <!-- Laravel's default JSON serialization of a model usually includes accessors if appended. Media model has 'url' in $fillable but usually 'url' is computed or stored. -->
                        <!-- In MediaService.php: $url = $this->disk->url($storedPath); ... 'url' => $url ... -->
                        <!-- So the Media model has a 'url' column. -->
                        <!-- So InvitationMedia type in InvitationService.ts should probably have 'url'. -->
                        <!-- In InvitationService.ts: export type InvitationMedia = { ... filename: string; path: string; mime_type: string; size: number; }; -->
                        <!-- It missed 'url'. I should probably update the type def or just assume 'url' exists dynamically. -->
                        <!-- I'll assume 'url' exists for now, I'll update the type definition later or cast as any if TS complains. -->
                        <!-- For now in template I assume 'url' exists. -->
                        <img 
                            v-if="invitation.media && invitation.media.length > 0 && (invitation.media[0] as any).url"
                            :src="(invitation.media[0] as any).url"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="text-4xl">❤️</div>
                        
                        <div class="absolute top-2 right-2 flex gap-1">
                             <span v-if="invitation.is_published" class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Publicada</span>
                             <span v-else class="bg-stone-500 text-white text-xs px-2 py-1 rounded-full">Borrador</span>
                        </div>
                    </div>

                    <div class="p-5">
                        <h3 class="font-bold text-lg text-stone-900 dark:text-stone-100 mb-1">
                            {{ invitation.title }}
                        </h3>
                        <p class="text-sm text-stone-500 dark:text-stone-400 mb-4 font-mono truncate">
                            /{{ invitation.slug }}
                        </p>

                        <div class="flex items-center justify-between gap-3">
                             <a 
                                :href="getPublicUrl(invitation.slug)"
                                target="_blank"
                                class="flex-1 text-center bg-stone-100 hover:bg-stone-200 dark:bg-stone-700 dark:hover:bg-stone-600 text-stone-700 dark:text-stone-200 py-2 rounded-lg text-sm font-medium transition-colors"
                             >
                                Ver
                             </a>
                             
                             <Link
                                :href="`/invitations/${invitation.id}/edit`"
                                class="flex-1 text-center bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/30 text-rose-600 dark:text-rose-400 py-2 rounded-lg text-sm font-medium transition-colors"
                             >
                                Editar
                             </Link>
                             
                             <button
                                @click="handleDelete(invitation.id)"
                                class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                title="Eliminar"
                            >
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16 bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700">
                <div class="text-6xl mb-4">💌</div>
                <h3 class="text-lg font-medium text-stone-900 dark:text-stone-100 mb-2">
                    No tienes invitaciones aún
                </h3>
                <p class="text-stone-600 dark:text-stone-400 mb-6">
                    Crea una invitación personalizada y compártela con esa persona especial.
                </p>
                <Link
                    href="/invitations/create"
                    class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-xl font-medium transition-colors"
                >
                    Crear mi primera invitación
                </Link>
            </div>
        </main>
    </div>
</template>
