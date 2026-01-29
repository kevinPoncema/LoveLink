<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="LoveLink - Crea tu historia de amor" />
    
    <div class="min-h-screen bg-linear-to-br from-rose-50 via-pink-50 to-purple-50 dark:from-stone-900 dark:via-rose-950 dark:to-purple-950">
        <header class="w-full py-6 px-4 md:px-8">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <Heart class="size-8 text-rose-600 dark:text-rose-400 fill-rose-600 dark:fill-rose-400" />
                    <h1 class="text-2xl font-black text-stone-900 dark:text-stone-100">LoveLink</h1>
                </div>
                
                <nav class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="px-4 py-2 text-sm font-medium text-stone-700 dark:text-stone-300 hover:text-rose-600 dark:hover:text-rose-400 transition-colors"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            href="/login"
                            class="px-4 py-2 text-sm font-medium text-stone-700 dark:text-stone-300 hover:text-rose-600 dark:hover:text-rose-400 transition-colors"
                        >
                            Iniciar Sesión
                        </Link>
                        <Link
                            v-if="canRegister"
                            href="/register"
                            class="px-6 py-2 text-sm font-medium bg-rose-600 text-white rounded-xl hover:bg-rose-700 transition-colors shadow-lg hover:shadow-xl"
                        >
                            Registrarse
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 md:px-8 py-12 md:py-24">
            <div class="text-center space-y-8">
                <div class="space-y-4 animate-fade-in-down">
                    <h2 class="text-5xl md:text-7xl font-black text-stone-900 dark:text-stone-100 leading-tight">
                        Tu Historia de Amor
                        <span class="block text-transparent bg-clip-text bg-linear-to-r from-rose-600 to-pink-600 dark:from-rose-400 dark:to-pink-400">
                            Merece su Propio Espacio
                        </span>
                    </h2>
                    <p class="text-xl md:text-2xl text-stone-600 dark:text-stone-400 max-w-3xl mx-auto">
                        Crea landing pages personalizadas e invitaciones únicas para compartir los momentos más especiales con tu pareja
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-8">
                    <Link
                        href="/login"
                        class="group relative px-12 py-6 bg-linear-to-r from-rose-600 to-pink-600 text-white text-xl font-black rounded-2xl shadow-2xl hover:shadow-rose-500/50 transform hover:scale-105 transition-all duration-300 overflow-hidden"
                    >
                        <span class="relative z-10 flex items-center gap-3">
                            <Heart class="size-6 fill-white" />
                            Iniciar Sesión
                        </span>
                        <div class="absolute inset-0 bg-linear-to-r from-pink-600 to-rose-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </Link>
                    
                    <Link
                        v-if="canRegister"
                        href="/register"
                        class="px-12 py-6 border-2 border-rose-600 dark:border-rose-400 text-rose-600 dark:text-rose-400 text-xl font-bold rounded-2xl hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all duration-300"
                    >
                        Crear Cuenta Gratis
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-20">
                    <div class="p-8 bg-white/60 dark:bg-stone-800/60 backdrop-blur-sm rounded-3xl border border-rose-100 dark:border-rose-900 shadow-lg hover:shadow-xl transition-all hover:scale-105">
                        <div class="text-5xl mb-4">💕</div>
                        <h3 class="text-xl font-bold text-stone-900 dark:text-stone-100 mb-2">Landing Pages</h3>
                        <p class="text-stone-600 dark:text-stone-400">
                            Crea páginas únicas para contar tu historia de amor con fotos y contadores especiales
                        </p>
                    </div>

                    <div class="p-8 bg-white/60 dark:bg-stone-800/60 backdrop-blur-sm rounded-3xl border border-rose-100 dark:border-rose-900 shadow-lg hover:shadow-xl transition-all hover:scale-105">
                        <div class="text-5xl mb-4">✉️</div>
                        <h3 class="text-xl font-bold text-stone-900 dark:text-stone-100 mb-2">Invitaciones</h3>
                        <p class="text-stone-600 dark:text-stone-400">
                            Envía invitaciones interactivas a familiares y amigos para tus eventos especiales
                        </p>
                    </div>

                    <div class="p-8 bg-white/60 dark:bg-stone-800/60 backdrop-blur-sm rounded-3xl border border-rose-100 dark:border-rose-900 shadow-lg hover:shadow-xl transition-all hover:scale-105">
                        <div class="text-5xl mb-4">📸</div>
                        <h3 class="text-xl font-bold text-stone-900 dark:text-stone-100 mb-2">Galería de Fotos</h3>
                        <p class="text-stone-600 dark:text-stone-400">
                            Sube y organiza los mejores momentos de tu relación en galerías hermosas
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="w-full py-12 px-4 md:px-8 mt-20 border-t border-rose-100 dark:border-rose-900">
            <div class="max-w-7xl mx-auto text-center space-y-4">
                <p class="text-stone-600 dark:text-stone-400">
                    Hecho con ❤️ para compartir historias de amor
                </p>
                <div class="pt-4">
                    <p class="text-xs uppercase tracking-widest text-stone-400 dark:text-stone-500 mb-2">Desarrollado por</p>
                    <a 
                        href="https://kevinponcedev.xyz/" 
                        target="_blank" 
                        class="text-lg font-black text-stone-700 dark:text-stone-300 hover:text-rose-600 dark:hover:text-rose-400 transition-colors"
                    >
                        Kevin Ponce
                    </a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.animate-fade-in-down {
    animation: fadeInDown 1s ease-out;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>