<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { useLandings } from '@/composables/useLandings';
import AppLayout from '@/layouts/app/AppLayout.vue';

const { landings, isLoading, error, loadLandings, deleteLanding } = useLandings();

onMounted(() => {
    loadLandings();
});

const handleDelete = async (id: number) => {
    if (confirm('¿Estás seguro de eliminar esta landing?')) {
        await deleteLanding(id);
    }
};

const getPublicUrl = (slug: string) => {
    return `${window.location.origin}/p/${slug}`;
};
</script>

<template>
    <AppLayout>
        <Head title="Mis Landings" />

        <div class="bg-white dark:bg-stone-900 min-h-screen">
            <header class="bg-white dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                <div class="max-w-7xl mx-auto px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-stone-900 dark:text-stone-100">
                                Mis Landings
                            </h1>
                            <p class="text-stone-600 dark:text-stone-400 mt-1">
                                Gestiona tus páginas de pareja personalizadas
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
                                href="/landings/create"
                                class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                            >
                                + Nueva Landing
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
                    <div class="animate-spin text-4xl mb-4">❤️</div>
                    <p class="text-stone-600 dark:text-stone-400">Cargando landings...</p>
                </div>

                <!-- Lista -->
                <div v-else-if="landings.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="landing in landings"
                        :key="landing.id"
                        class="bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700 overflow-hidden hover:shadow-lg transition-shadow"
                    >
                        <!-- Preview (usando la primera imagen si existe) -->
                        <div class="aspect-video bg-rose-100 dark:bg-rose-900/20 relative flex items-center justify-center overflow-hidden">
                            <img
                                v-if="landing.media && landing.media.length > 0 && (landing.media[0] as any).url"
                                :src="(landing.media[0] as any).url"
                                class="w-full h-full object-cover"
                                alt="Preview"
                            />
                            <div v-else class="text-rose-300 dark:text-rose-700 text-4xl">
                                ❤️
                            </div>
                            
                            <!-- Overlay acciones -->
                            <div class="absolute inset-x-0 bottom-0 bg-linear-to-t from-black/60 to-transparent p-4 flex justify-end gap-2">
                                <Link
                                    :href="`/landings/${landing.id}/edit`"
                                    class="text-white hover:text-rose-200 bg-black/30 hover:bg-black/50 p-2 rounded-lg backdrop-blur-sm transition-colors"
                                    title="Editar"
                                >
                                    ✏️
                                </Link>
                                <a
                                    :href="getPublicUrl(landing.slug)"
                                    target="_blank"
                                    class="text-white hover:text-rose-200 bg-black/30 hover:bg-black/50 p-2 rounded-lg backdrop-blur-sm transition-colors"
                                    title="Ver pública"
                                >
                                    👁️
                                </a>
                            </div>
                        </div>

                        <div class="p-5">
                            <h3 class="text-lg font-bold text-stone-900 dark:text-stone-100 mb-1">
                                {{ landing.couple_names }}
                            </h3>
                            <p class="text-sm text-stone-500 dark:text-stone-400 mb-4">
                                {{ landing.slug }}
                            </p>
                            
                            <div class="flex items-center justify-between pt-4 border-t border-stone-100 dark:border-stone-700">
                                <span class="text-xs text-stone-400">
                                    {{ new Date(landing.created_at).toLocaleDateString() }}
                                </span>
                                <button
                                    @click="handleDelete(landing.id)"
                                    class="text-red-500 hover:text-red-700 text-sm font-medium"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20 bg-stone-50 dark:bg-stone-800/50 rounded-2xl border-2 border-dashed border-stone-200 dark:border-stone-700">
                    <div class="text-5xl mb-4">💑</div>
                    <h3 class="text-xl font-medium text-stone-900 dark:text-stone-100 mb-2">
                        No has creado ninguna Landing
                    </h3>
                    <p class="text-stone-500 dark:text-stone-400 mb-6 max-w-sm mx-auto">
                        Crea tu primera página de pareja para compartir tu historia de amor
                    </p>
                    <Link
                        href="/landings/create"
                        class="inline-block bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-xl font-medium transition-colors"
                    >
                        Comenzar ahora
                    </Link>
                </div>
            </main>
        </div>
    </AppLayout>
</template>
