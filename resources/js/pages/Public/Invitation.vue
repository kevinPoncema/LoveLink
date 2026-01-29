<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
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

const currentImage = computed(() => {
    if (!invitation.value || !invitation.value.media || invitation.value.media.length === 0) {
        return null;
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
    <div class="min-h-screen bg-stone-50 text-stone-900 flex flex-col items-center justify-center p-4 overflow-hidden relative">
        <Head :title="invitation?.title || 'Invitación'" />

        <!-- Error State -->
        <div v-if="error" class="text-center p-8 bg-white rounded-2xl shadow-xl max-w-md">
            <div class="text-4xl mb-4">💔</div>
            <h1 class="text-xl font-bold mb-2">No pudimos encontrar esta invitación</h1>
            <p class="text-stone-600 mb-4">{{ error }}</p>
            <a href="/" class="text-rose-600 hover:underline">Ir al inicio</a>
        </div>

        <!-- Loading State -->
        <div v-else-if="isLoading || !invitation" class="flex flex-col items-center">
            <div class="animate-bounce text-6xl mb-4">💌</div>
            <p class="text-stone-500 animate-pulse">Cargando sorpresa...</p>
        </div>

        <!-- Content -->
        <div v-else class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border-4 border-white transform transition-all hover:scale-[1.01]">

            <!-- Image Area -->
            <div class="relative aspect-square bg-stone-200 overflow-hidden">
                <transition
                    enter-active-class="transition-opacity duration-500 ease-in-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-opacity duration-300 ease-in-out absolute inset-0"
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
                        <span class="text-8xl">❤️</span>
                    </div>
                </transition>

                <!-- Floating Hearts Animation Background (CSS only) -->
                <div v-if="hasSaidYes" class="absolute inset-0 pointer-events-none z-10 flex items-center justify-center bg-white/30 backdrop-blur-sm">
                    <div class="text-center animate-bounce">
                        <div class="text-9xl transform drop-shadow-lg">💖</div>
                    </div>
                </div>
            </div>

            <!-- Text Area -->
            <div class="p-8 text-center relative z-20 bg-white">
                <template v-if="!hasSaidYes">
                    <h1 class="text-2xl font-bold mb-2 text-rose-600">{{ invitation.title }}</h1>

                    <!-- Not showing content because it's not in DB -->

                    <div v-if="noInteractionText" class="mb-4 text-red-500 font-medium animate-pulse min-h-[1.5em]">
                        {{ noInteractionText }}
                    </div>
                    <div v-else class="mb-8 min-h-[1.5em]"></div> <!-- Spacer -->

                    <div class="flex flex-col gap-4 items-center">
                        <button
                            @click="handleYes"
                            :style="{ transform: `scale(${yesButtonScale})` }"
                            class="bg-rose-600 text-white px-8 py-3 rounded-full font-bold text-lg shadow-lg hover:bg-rose-700 hover:shadow-rose-500/30 transition-all active:scale-95 w-full max-w-[200px]"
                        >
                            ¡SÍ! 😍
                        </button>

                        <button
                            @click="handleNo"
                            class="text-stone-400 font-medium text-sm hover:text-stone-600 transition-colors px-4 py-2 hover:bg-stone-100 rounded-lg"
                        >
                            No, gracias
                        </button>
                    </div>
                </template>

                <template v-else>
                    <div class="py-8">
                        <h2 class="text-3xl font-bold text-rose-600 mb-4 animate-bounce">
                            ¡SIIIIIII! 🎉
                        </h2>
                        <p class="text-xl text-stone-700">
                            {{ invitation.yes_message || '¡Sabía que dirías que sí!' }}
                        </p>
                        <div class="mt-8 text-6xl animate-pulse">
                            💑
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer -->
        <footer class="fixed bottom-4 text-center w-full text-stone-400 text-xs pointer-events-none">
            Hecho con ❤️ en LoveLink
        </footer>
    </div>
</template>

<style scoped>
/* Simple confetti-like floaters could be added here if needed */
</style>
