<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import LandingCounter from '@/components/LandingCounter.vue';
import type { Landing } from '@/services/landing/LandingService';

const props = defineProps<{
    landing: Landing;
}>();

// Estilo dinámico basado en las variables del tema
const themeStyles = computed(() => {
    const theme = props.landing.theme;
    if (!theme) return {};

    return {
        '--primary': theme.primary_color || '#e11d48', // Default rose-600
        '--secondary': theme.secondary_color || '#44403c', // Default stone-700
        '--bg-color': theme.bg_color || '#fafaf9', // Default stone-50
        '--bg-image': theme.bg_image_url ? `url('${theme.bg_image_url}')` : 'none',
        '--font-display': 'serif', // Could be dynamic too
    };
});

// Separar media: Portada (index 0) vs Galería
const heroImage = computed(() => {
    if (!props.landing.media || props.landing.media.length === 0) return null;
    return props.landing.media[0];
});

const gallery = computed(() => {
    if (!props.landing.media || props.landing.media.length <= 1) return [];
    return props.landing.media.slice(1);
});

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};
</script>

<template>
    <div class="landing-page-root min-h-screen font-sans" :style="themeStyles">
        <Head>
            <title>{{ landing.couple_names }} - UsPage</title>
            <meta name="description" :content="landing.bio_text || `La historia de ${landing.couple_names}`" />
            <!-- Open Graph Tags (Dynamic meta provided by Inertia/SSR if enabled, otherwise helpful for some clients) -->
            <meta property="og:title" :content="`${landing.couple_names} - Nuestra Historia`" />
            <meta property="og:description" :content="landing.bio_text" />
            <meta v-if="heroImage" property="og:image" :content="heroImage.url || heroImage.path" />
        </Head>

        <!-- Dynamic Background Layer -->
        <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat opacity-10 pointer-events-none" style="background-image: var(--bg-image);"></div>
        
        <div class="relative z-10 w-full min-h-screen flex flex-col items-center">
            
            <!-- Header -->
            <header class="text-center py-12 px-6 animate-fade-in-down">
                <h1 class="text-5xl md:text-7xl font-serif text-secondary mb-4 drop-shadow-sm">
                    {{ landing.couple_names }}
                </h1>
                <p class="text-stone-500 tracking-[0.2em] uppercase text-xs md:text-sm font-bold bg-white/50 inline-block px-4 py-1 rounded-full backdrop-blur-sm">
                    Desde el <span class="font-black text-stone-900 dark:text-stone-100">{{ formatDate(landing.anniversary_date) }}</span>
                </p>
            </header>

            <!-- Counter Section -->
            <section class="max-w-4xl mx-auto mb-10 text-primary">
                <LandingCounter :date="landing.anniversary_date" />
            </section>

            <!-- Hero Section -->
            <section class="w-full max-w-4xl px-4 md:px-0 mb-16">
                <div class="relative aspect-3/4 md:aspect-video rounded-[2.5rem] overflow-hidden shadow-2xl border-4 border-white/50 mx-auto group">
                     <!-- Imagen de Portada -->
                    <img 
                        v-if="heroImage"  
                        :src="heroImage.url || heroImage.path" 
                        class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105" 
                    />
                    <div v-else class="w-full h-full bg-stone-200 flex items-center justify-center text-stone-400">
                        Sin imagen de portada
                    </div>

                    <!-- Bio Overlay -->
                    <div v-if="landing.bio_text" class="absolute inset-x-0 bottom-0 top-1/3 bg-linear-to-t from-black/80 via-black/40 to-transparent flex items-end p-8 md:p-12">
                        <p class="text-white text-lg md:text-2xl italic leading-relaxed font-serif drop-shadow-md max-w-2xl">
                            "{{ landing.bio_text }}"
                        </p>
                    </div>
                </div>
            </section>

            <!-- Gallery Grid (Masonry-ish) -->
            <section v-if="gallery.length > 0" class="w-full max-w-6xl px-4 md:px-6 mb-24">
                <h2 class="text-center text-2xl font-serif text-secondary mb-10 opacity-80">Nuestros Momentos</h2>
                <div class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
                    <div 
                        v-for="img in gallery" 
                        :key="img.id" 
                        class="break-inside-avoid rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 hover:scale-[1.02] cursor-pointer"
                    >
                        <img 
                            :src="img.url || img.path" 
                            class="w-full h-auto object-cover" 
                            loading="lazy"
                        />
                    </div>
                </div>
            </section>
            
            <!-- Footer -->
             <footer class="pb-10 pt-10 text-center text-stone-400 text-sm">
                <p>Hecho con ❤️ en <a href="/" class="hover:text-primary transition-colors">UsPage</a></p>
                <div class="mt-4 pt-4 border-t border-stone-200/50 max-w-xs mx-auto">
                    <p class="text-[10px] uppercase tracking-widest opacity-60 mb-1">Desarrollado por</p>
                    <a href="https://kevinponcedev.xyz/" target="_blank" class="font-bold text-stone-600 dark:text-stone-300 hover:text-primary transition-all">
                        Kevin Ponce
                    </a>
                    <p class="text-[9px] mt-1 opacity-40">kevinponcedev.xyz</p>
                </div>
            </footer>

        </div>
    </div>
</template>

<style scoped>
.landing-page-root {
  background-color: var(--bg-color);
  color: var(--secondary);
}

.text-primary { color: var(--primary); }
.text-secondary { color: var(--secondary); }
.bg-primary { background-color: var(--primary); }

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
