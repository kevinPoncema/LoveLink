<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useAuth } from '@/composables/useAuth';
import { useDashboard } from '@/composables/useDashboard';

// Composables
const { user, logout } = useAuth();
const { stats, isLoading, error, loadStats, clearError } = useDashboard();

// Computed
const userName = computed(() => {
    if (!user.value) return 'Usuario';
    return user.value.name || user.value.email.split('@')[0];
});

const userInitials = computed(() => {
    if (!user.value) return 'U';
    const name = user.value.name || user.value.email;
    return name.charAt(0).toUpperCase();
});

// Configuración de secciones
const sections = [
    { 
        name: 'Landing Pages', 
        count: 'landings' as keyof typeof stats.value, 
        icon: '❤️', 
        desc: 'Tus páginas conmemorativas', 
        color: 'bg-rose-50',
        route: '/landings'
    },
    { 
        name: 'Invitaciones', 
        count: 'invitations' as keyof typeof stats.value, 
        icon: '💌', 
        desc: 'Cartas de San Valentín', 
        color: 'bg-amber-50',
        route: '/invitations'
    },
    { 
        name: 'Multimedia', 
        count: 'media' as keyof typeof stats.value, 
        icon: '📸', 
        desc: 'Fotos y recuerdos subidos', 
        color: 'bg-blue-50',
        route: '/media'
    },
];

// Métodos
onMounted(async () => {
    await loadStats();
});

const handleLogout = async () => {
    await logout();
};

const handleSectionClick = (route: string) => {
    // TODO: Navegar a la sección específica cuando las rutas estén implementadas
    console.log(`Navigating to: ${route}`);
};

const handleCreateLanding = () => {
    // TODO: Navegar a creación de landing
    console.log('Creating new landing...');
};

const handleUploadMedia = () => {
    // TODO: Navegar a upload de media
    console.log('Uploading media...');
};

const handleRetryLoad = async () => {
    clearError();
    await loadStats();
};
</script>

<template>
    <div class="min-h-screen bg-stone-50 p-6">
        <Head title="Dashboard" />
        
        <!-- Header -->
        <header class="max-w-6xl mx-auto flex justify-between items-center mb-12">
            <div>
                <Link href="/" class="text-2xl font-serif text-stone-800 hover:text-rose-600 transition-colors">
                    UsPage<span class="text-rose-500">.love</span>
                </Link>
                <h2 class="text-lg text-stone-600 mt-1">Panel de Control</h2>
            </div>
            
            <div class="flex items-center gap-4">
                <span class="text-sm text-stone-500">
                    Hola, {{ userName }}
                </span>
                
                <!-- Menú de usuario -->
                <div class="relative group">
                    <button class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center text-rose-600 font-bold hover:bg-rose-200 transition-colors">
                        {{ userInitials }}
                    </button>
                    
                    <!-- Dropdown del usuario -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-stone-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                        <div class="p-3 border-b border-stone-100">
                            <p class="font-medium text-stone-900">{{ userName }}</p>
                            <p class="text-sm text-stone-500">{{ user?.email }}</p>
                        </div>
                        <div class="p-2">
                            <button 
                                @click="handleLogout"
                                class="w-full text-left px-3 py-2 text-sm text-stone-700 hover:bg-stone-50 rounded-lg transition-colors"
                            >
                                Cerrar sesión
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Estadísticas principales -->
        <main class="max-w-6xl mx-auto">
            <!-- Error state -->
            <div v-if="error && !isLoading" class="mb-8 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-red-800 font-medium">Error cargando datos</h3>
                        <p class="text-red-600 text-sm mt-1">{{ error }}</p>
                    </div>
                    <button 
                        @click="handleRetryLoad"
                        class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors"
                    >
                        Reintentar
                    </button>
                </div>
            </div>

            <!-- Loading state -->
            <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div v-for="i in 3" :key="i" class="bg-white p-8 rounded-3xl border border-stone-100 animate-pulse">
                    <div class="w-14 h-14 bg-stone-200 rounded-2xl mb-6"></div>
                    <div class="h-6 bg-stone-200 rounded mb-2"></div>
                    <div class="h-4 bg-stone-200 rounded mb-6"></div>
                    <div class="flex justify-between">
                        <div class="h-10 w-16 bg-stone-200 rounded"></div>
                        <div class="h-6 w-20 bg-stone-200 rounded"></div>
                    </div>
                </div>
            </div>

            <!-- Cards de estadísticas -->
            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <button
                    v-for="section in sections" 
                    :key="section.name" 
                    @click="handleSectionClick(section.route)"
                    class="group bg-white p-8 rounded-3xl border border-stone-100 shadow-sm hover:shadow-md transition-all cursor-pointer text-left"
                >
                    <div :class="[section.color, 'w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform']">
                        {{ section.icon }}
                    </div>
                    <h3 class="text-xl font-bold text-stone-800 mb-2">{{ section.name }}</h3>
                    <p class="text-stone-500 text-sm mb-6">{{ section.desc }}</p>
                    <div class="flex items-end justify-between">
                        <span class="text-4xl font-serif text-stone-900">{{ stats[section.count] }}</span>
                        <span class="text-rose-600 font-medium text-sm group-hover:underline">Gestionar →</span>
                    </div>
                </button>
            </div>

            <!-- Acciones rápidas -->
            <div class="pt-8 border-t border-stone-200">
                <h4 class="text-stone-400 text-xs uppercase tracking-widest font-bold mb-6">Acciones Rápidas</h4>
                <div class="flex flex-wrap gap-4">
                    <button 
                        @click="handleCreateLanding"
                        class="bg-stone-900 text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-stone-800 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-900"
                    >
                        + Crear Nueva Landing
                    </button>
                    <button 
                        @click="handleUploadMedia"
                        class="border border-stone-200 bg-white text-stone-700 px-6 py-3 rounded-xl text-sm font-medium hover:bg-stone-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-200"
                    >
                        📸 Subir Fotos
                    </button>
                    <Link
                        href="/themes"
                        class="border border-stone-200 bg-white text-stone-700 px-6 py-3 rounded-xl text-sm font-medium hover:bg-stone-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-200"
                    >
                        🎨 Gestionar Temas
                    </Link>
                </div>
            </div>
        </main>
    </div>
</template>
</script>

<template>
    <div class="min-h-screen bg-stone-50 p-6">
        <Head title="Dashboard" />
        
        <!-- Header -->
        <header class="max-w-6xl mx-auto flex justify-between items-center mb-12">
            <div>
                <Link href="/" class="text-2xl font-serif text-stone-800 hover:text-rose-600 transition-colors">
                    UsPage<span class="text-rose-500">.love</span>
                </Link>
                <h2 class="text-lg text-stone-600 mt-1">Panel de Control</h2>
            </div>
            
            <div class="flex items-center gap-4">
                <span class="text-sm text-stone-500">
                    Hola, {{ userName }}
                </span>
                
                <!-- Menú de usuario -->
                <div class="relative group">
                    <button class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center text-rose-600 font-bold hover:bg-rose-200 transition-colors">
                        {{ userInitials }}
                    </button>
                    
                    <!-- Dropdown del usuario -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-stone-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                        <div class="p-3 border-b border-stone-100">
                            <p class="font-medium text-stone-900">{{ userName }}</p>
                            <p class="text-sm text-stone-500">{{ user?.email }}</p>
                        </div>
                        <div class="p-2">
                            <button 
                                @click="handleLogout"
                                class="w-full text-left px-3 py-2 text-sm text-stone-700 hover:bg-stone-50 rounded-lg transition-colors"
                            >
                                Cerrar sesión
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Estadísticas principales -->
        <main class="max-w-6xl mx-auto">
            <!-- Loading state -->
            <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div v-for="i in 3" :key="i" class="bg-white p-8 rounded-3xl border border-stone-100 animate-pulse">
                    <div class="w-14 h-14 bg-stone-200 rounded-2xl mb-6"></div>
                    <div class="h-6 bg-stone-200 rounded mb-2"></div>
                    <div class="h-4 bg-stone-200 rounded mb-6"></div>
                    <div class="flex justify-between">
                        <div class="h-10 w-16 bg-stone-200 rounded"></div>
                        <div class="h-6 w-20 bg-stone-200 rounded"></div>
                    </div>
                </div>
            </div>

            <!-- Cards de estadísticas -->
            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <button
                    v-for="section in sections" 
                    :key="section.name" 
                    @click="handleSectionClick(section.route)"
                    class="group bg-white p-8 rounded-3xl border border-stone-100 shadow-sm hover:shadow-md transition-all cursor-pointer text-left"
                >
                    <div :class="[section.color, 'w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform']">
                        {{ section.icon }}
                    </div>
                    <h3 class="text-xl font-bold text-stone-800 mb-2">{{ section.name }}</h3>
                    <p class="text-stone-500 text-sm mb-6">{{ section.desc }}</p>
                    <div class="flex items-end justify-between">
                        <span class="text-4xl font-serif text-stone-900">{{ stats[section.count] }}</span>
                        <span class="text-rose-600 font-medium text-sm group-hover:underline">Gestionar →</span>
                    </div>
                </button>
            </div>

            <!-- Acciones rápidas -->
            <div class="pt-8 border-t border-stone-200">
                <h4 class="text-stone-400 text-xs uppercase tracking-widest font-bold mb-6">Acciones Rápidas</h4>
                <div class="flex flex-wrap gap-4">
                    <button 
                        @click="handleCreateLanding"
                        class="bg-stone-900 text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-stone-800 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-900"
                    >
                        + Crear Nueva Landing
                    </button>
                    <button 
                        @click="handleUploadMedia"
                        class="border border-stone-200 bg-white text-stone-700 px-6 py-3 rounded-xl text-sm font-medium hover:bg-stone-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-200"
                    >
                        📸 Subir Fotos
                    </button>
                    <Link
                        href="/themes"
                        class="border border-stone-200 bg-white text-stone-700 px-6 py-3 rounded-xl text-sm font-medium hover:bg-stone-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-200"
                    >
                        🎨 Gestionar Temas
                    </Link>
                </div>
            </div>
        </main>
    </div>
</template>
