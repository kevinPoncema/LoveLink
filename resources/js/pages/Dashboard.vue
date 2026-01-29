<script setup lang="ts">
import { Link, Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '@/composables/useAuth';
import { useDashboard } from '@/composables/useDashboard';

// Composables
const { user } = useAuth();
const { isLoading, error } = useDashboard();

// Theme toggle functionality
const isDark = ref(false);

// Computeds
const userName = computed(() => {
    return user.value?.name || 'Usuario';
});

const userInitials = computed(() => {
    if (!user.value?.name) return 'U';
    
    const names = user.value.name.split(' ');
    return names.length >= 2 
        ? names[0][0] + names[1][0]
        : names[0][0];
});

// Secciones del dashboard
const sections = computed(() => [
    {
        name: 'Landings',
        desc: 'Páginas de tu historia de amor',
        icon: '💕',
        color: 'bg-rose-100 dark:bg-rose-900/30',
        count: 'landings',
        route: '/landings'
    },
    {
        name: 'Invitaciones',
        desc: 'Invita a familiares y amigos',
        icon: '✉️',
        color: 'bg-purple-100 dark:bg-purple-900/30',
        count: 'invitations',
        route: '/invitations'
    },
    {
        name: 'Fotos',
        desc: 'Galería de momentos especiales',
        icon: '📸',
        color: 'bg-blue-100 dark:bg-blue-900/30',
        count: 'media',
        route: '/media'
    }
]);

// Métodos
const handleSectionClick = (route: string) => {
    // Navegar a la ruta correspondiente
    router.visit(route);
};

const handleCreateLanding = () => {
    router.visit('/landings/create');
};

const handleUploadMedia = () => {
    router.visit('/media');
};

const handleLogout = async () => {
    router.post('/logout');
};

const toggleTheme = () => {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};

// Initialize theme on mount
onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    isDark.value = savedTheme === 'dark' || (savedTheme === null && systemPrefersDark);
    document.documentElement.classList.toggle('dark', isDark.value);
});
</script>

<template>
    <div class="min-h-screen bg-stone-50 dark:bg-stone-900 p-6">
        <Head title="Dashboard" />
        
        <!-- Header -->
        <header class="max-w-6xl mx-auto flex justify-between items-center mb-12">
            <div>
                <Link href="/" class="text-2xl font-serif text-stone-800 dark:text-stone-200 hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                    LoveLink
                </Link>
                <h2 class="text-lg text-stone-600 dark:text-stone-400 mt-1">Panel de Control</h2>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Theme toggle button -->
                <button 
                    @click="toggleTheme"
                    class="p-2 rounded-xl bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:bg-stone-200 dark:hover:bg-stone-700 transition-colors"
                >
                    <span class="text-lg">{{ isDark ? '☀️' : '🌙' }}</span>
                </button>

                <span class="text-sm font-medium text-stone-700 dark:text-stone-200">
                    {{ userName }}
                </span>
                
                <!-- Menú de usuario -->
                <div class="relative group">
                    <button class="w-10 h-10 bg-rose-100 dark:bg-rose-900/30 rounded-full flex items-center justify-center text-rose-600 dark:text-rose-400 font-bold hover:bg-rose-200 dark:hover:bg-rose-900/50 transition-colors">
                        {{ userInitials }}
                    </button>
                    
                    <!-- Dropdown del usuario -->
                    <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-stone-800 rounded-xl shadow-lg border border-stone-100 dark:border-stone-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                        <div class="p-3 border-b border-stone-100 dark:border-stone-700">
                            <p class="font-medium text-stone-900 dark:text-stone-100">{{ userName }}</p>
                            <p class="text-sm text-stone-500 dark:text-stone-400">{{ user?.email }}</p>
                        </div>
                        <div class="p-2">
                            <button 
                                @click="handleLogout"
                                class="w-full text-left px-3 py-2 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 rounded-lg transition-colors"
                            >
                                Cerrar sesión
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Error state -->
        <div v-if="error" class="max-w-6xl mx-auto mb-8">
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg">
                Error cargando dashboard: {{ error }}
            </div>
        </div>

        <!-- Estadísticas principales -->
        <main class="max-w-6xl mx-auto">
            <!-- Loading state -->
            <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div v-for="i in 3" :key="i" class="bg-white dark:bg-stone-800 p-8 rounded-3xl border border-stone-100 dark:border-stone-700 animate-pulse">
                    <div class="w-14 h-14 bg-stone-200 dark:bg-stone-700 rounded-2xl mb-6"></div>
                    <div class="h-6 bg-stone-200 dark:bg-stone-700 rounded mb-2"></div>
                    <div class="h-4 bg-stone-200 dark:bg-stone-700 rounded mb-6"></div>
                    <div class="flex justify-between">
                        <div class="h-10 w-16 bg-stone-200 dark:bg-stone-700 rounded"></div>
                        <div class="h-6 w-20 bg-stone-200 dark:bg-stone-700 rounded"></div>
                    </div>
                </div>
            </div>

            <!-- Cards de estadísticas -->
            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <button
                    v-for="section in sections" 
                    :key="section.name" 
                    @click="handleSectionClick(section.route)"
                    class="group bg-white dark:bg-stone-800 p-8 rounded-3xl border border-stone-100 dark:border-stone-700 shadow-sm hover:shadow-md dark:hover:shadow-lg transition-all cursor-pointer text-left"
                >
                    <div :class="[section.color, 'w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform']">
                        {{ section.icon }}
                    </div>
                    <h3 class="text-xl font-bold text-stone-800 dark:text-stone-200 mb-2">{{ section.name }}</h3>
                    <p class="text-stone-500 dark:text-stone-400 text-sm mb-6">{{ section.desc }}</p>
                    <div class="flex items-end justify-end">
                        <span class="text-rose-600 dark:text-rose-400 font-medium text-sm group-hover:underline">Gestionar &rarr;</span>
                    </div>
                </button>
            </div>

            <!-- Acciones rápidas -->
            <div class="pt-8 border-t border-stone-200 dark:border-stone-700">
                <h4 class="text-stone-400 dark:text-stone-500 text-xs uppercase tracking-widest font-bold mb-6">Acciones Rápidas</h4>
                <div class="flex flex-wrap gap-4">
                    <button 
                        @click="handleCreateLanding"
                        class="bg-stone-900 dark:bg-stone-700 text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-stone-800 dark:hover:bg-stone-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-900 dark:focus:ring-stone-600"
                    >
                        + Crear Nueva Landing
                    </button>
                    <button 
                        @click="handleUploadMedia"
                        class="border border-stone-200 dark:border-stone-600 bg-white dark:bg-stone-800 text-stone-700 dark:text-stone-300 px-6 py-3 rounded-xl text-sm font-medium hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-200 dark:focus:ring-stone-600"
                    >
                        📸 Subir Fotos
                    </button>
                    <Link
                        href="/themes"
                        class="border border-stone-200 dark:border-stone-600 bg-white dark:bg-stone-800 text-stone-700 dark:text-stone-300 px-6 py-3 rounded-xl text-sm font-medium hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-200 dark:focus:ring-stone-600"
                    >
                        🎨 Gestionar Temas
                    </Link>
                </div>
            </div>
        </main>
    </div>
</template>