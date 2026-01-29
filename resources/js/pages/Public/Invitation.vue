<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { useInvitations } from '@/composables/useInvitations';

const props = defineProps<{
    slug: string;
}>();

const { invitation, loadPublicInvitation, isLoading, error } = useInvitations();

const currentImageIndex = ref(0);
const hasSaidYes = ref(false);
const noCount = ref(0);
const noInteractionText = ref('');

onMounted(async () => {
    await loadPublicInvitation(props.slug);
});

// Theme customization with robust defaults
const themeStyles = computed(() => {
    const defaultTheme = {
        primary: '#e11d48',   // Rose 600
        secondary: '#fafaf9', // Stone 50
        bg: '#fafaf9',        // Stone 50
        text: '#1c1917'       // Stone 900
    };

    const theme = invitation.value?.theme;
    
    return {
        '--primary-color': theme?.primary_color || defaultTheme.primary,
        '--secondary-color': theme?.secondary_color || defaultTheme.secondary, 
        '--bg-color': theme?.background_color || defaultTheme.bg,
        '--text-color': defaultTheme.text,
    };
});

// Background image from theme
const bgImage = computed(() => {
    return invitation.value?.theme?.bg_image_url || null;
});

const currentImage = computed(() => {
    if (!invitation.value || !invitation.value.media || invitation.value.media.length === 0) {
        return null; // TODO: Add default placeholder
    }
    const media = invitation.value.media[currentImageIndex.value] as any;
    return media.url || null;
});

const handleNo = () => {
    if (!invitation.value) return;

    noCount.value++;

    // Cycle through no_messages if available
    if (invitation.value.no_messages && invitation.value.no_messages.length > 0) {
        const index = (noCount.value - 1) % invitation.value.no_messages.length;
        noInteractionText.value = invitation.value.no_messages[index];
    } else {
        noInteractionText.value = '¡Oh no! ¿Seguro?';
    }

    // Rotate image
    if (invitation.value.media && invitation.value.media.length > 1) {
        currentImageIndex.value = (currentImageIndex.value + 1) % invitation.value.media.length;
    }
};

const handleYes = () => {
    hasSaidYes.value = true;
};

const yesButtonScale = computed(() => {
    return Math.min(1 + (noCount.value * 0.2), 2.5); // Cap scale at 2.5x
});
</script>

<template>
    <div 
        class="min-h-screen flex flex-col items-center justify-center p-4 overflow-hidden relative transition-colors duration-500"
        :style="{ 
            backgroundColor: themeStyles['--bg-color'], 
            color: themeStyles['--text-color'] 
        }"
    >
        <Head :title="invitation?.title || 'Invitación'" />

        <!-- Background Image -->
        <div v-if="bgImage" class="absolute inset-0 z-0">
            <img :src="bgImage" alt="Background" class="w-full h-full object-cover opacity-20 blur-sm" />
            <div class="absolute inset-0 bg-black/10"></div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="text-center p-8 bg-white/90 backdrop-blur rounded-2xl shadow-xl max-w-md relative z-10">
            <div class="text-4xl mb-4">💔</div>
            <h1 class="text-xl font-bold mb-2">No pudimos encontrar esta invitación</h1>
            <p class="text-stone-600 mb-4">{{ error }}</p>
            <a href="/" class="text-rose-600 hover:underline">Ir al inicio</a>
        </div>

        <!-- Loading State -->
        <div v-else-if="isLoading || !invitation" class="flex flex-col items-center relative z-10">
            <div class="animate-bounce text-6xl mb-4">💌</div>
            <p class="text-stone-500 animate-pulse">Cargando sorpresa...</p>
        </div>

        <!-- Content -->
        <div v-else class="w-full max-w-7xl h-[90vh] md:h-[85vh] bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden border-4 border-white/50 transform transition-all flex flex-col relative z-20">

            <div class="flex flex-col md:flex-row h-full">
                <!-- Image Area -->
                <div class="relative w-full md:w-3/5 h-[45%] md:h-full bg-stone-200 overflow-hidden">
                    <transition
                        enter-active-class="transition-opacity duration-1000 ease-in-out"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition-opacity duration-500 ease-in-out absolute inset-0"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <img
                            v-if="currentImage"
                            :key="currentImageIndex"
                            :src="currentImage"
                            class="w-full h-full object-cover"
                            alt="Invitación"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center bg-rose-100 text-rose-300">
                            <span class="text-8xl animate-pulse">✨</span>
                        </div>
                    </transition>

                    <!-- Floating Confetti Animation Background (CSS only) -->
                    <div v-if="hasSaidYes" class="absolute inset-0 pointer-events-none z-10 flex items-center justify-center bg-white/30 backdrop-blur-sm">
                        <div class="text-center animate-bounce">
                            <div class="text-9xl transform drop-shadow-lg">🎊</div>
                        </div>
                    </div>
                </div>

                <!-- Text Area -->
                <div class="relative w-full md:w-2/5 p-6 md:p-12 flex flex-col justify-center items-center text-center bg-white/80 backdrop-blur-md overflow-y-auto">
                    <template v-if="!hasSaidYes">
                        <h1 
                            class="text-3xl md:text-5xl font-extrabold mb-6 transition-colors duration-300 drop-shadow-sm leading-tight"
                            :style="{ color: themeStyles['--primary-color'] }"
                        >
                            {{ invitation.title }}
                        </h1>

                        <div v-if="noInteractionText" class="mb-8 min-h-[3em] flex items-center justify-center">
                            <span class="text-xl md:text-2xl font-bold animate-elastic text-red-500 bg-red-50 px-4 py-2 rounded-xl shadow-inner inline-block transform rotate-2">
                                {{ noInteractionText }} 🥺
                            </span>
                        </div>
                        <div v-else class="mb-8 min-h-[3em]"></div>

                        <div class="flex flex-col gap-6 items-center w-full max-w-sm">
                            <button
                                @click="handleYes"
                                :style="[
                                    { transform: `scale(${yesButtonScale})` },
                                    { backgroundColor: themeStyles['--primary-color'] }
                                ]"
                                class="text-white px-10 py-5 rounded-full font-black text-2xl shadow-xl hover:brightness-110 hover:shadow-2xl transition-all active:scale-95 w-full uppercase tracking-widest ring-4 ring-transparent hover:ring-rose-200"
                            >
                                ¡SÍ! <span class="inline-block animate-bounce">🥳</span>
                            </button>

                            <button
                                @click="handleNo"
                                class="text-stone-400 font-semibold text-base hover:text-stone-600 transition-colors px-6 py-3 hover:bg-stone-100 rounded-full cursor-pointer select-none"
                            >
                                {{ noCount === 0 ? 'No, gracias' : 'Mmm... no sé' }}
                            </button>
                        </div>
                    </template>

                    <template v-else>
                        <div class="py-12 animate-fade-in-up">
                            <h2 
                                class="text-4xl md:text-6xl font-black mb-8 animate-bounce leading-tight"
                                :style="{ color: themeStyles['--primary-color'] }"
                            >
                                ¡SIIIIIII! 🎉
                            </h2>
                            <p class="text-2xl md:text-3xl text-stone-700 font-medium leading-relaxed">
                                {{ invitation.yes_message || '¡Sabía que dirías que sí!' }}
                            </p>
                            <div class="mt-12 text-7xl animate-pulse flex justify-center gap-4">
                                <span>✨</span><span>🎉</span><span>🎈</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            
        </div>

        <!-- Footer -->
        <footer class="mt-8 mb-4 text-center w-full relative z-30">
            <p class="text-stone-400 text-xs">Hecho con ✨ en <a href="/" class="hover:text-rose-500 transition-colors">UsPage</a></p>
            <div class="mt-2 text-[10px] opacity-60">
                <span class="text-stone-400">Desarrollado por</span>
                <a href="https://kevinponcedev.xyz/" target="_blank" class="font-bold text-stone-500 hover:text-rose-600 ml-1 transition-colors">
                    Kevin Ponce
                </a>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes elastic {
    0%, 100% { transform: scale(1) rotate(2deg); }
    50% { transform: scale(1.1) rotate(-2deg); }
}
.animate-elastic {
    animation: elastic 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
    opacity: 0;
    transform: translateY(20px);
}
@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
