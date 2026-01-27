<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import { useAuth } from '@/composables/useAuth';
import type { LoginData } from '@/types/auth';

defineProps<{
    status?: string;
    canResetPassword?: boolean;
    canRegister?: boolean;
}>();

// Composables
const { login, isLoading, error, clearError } = useAuth();

// Form data reactivo
const form = ref<LoginData>({
    email: '',
    password: '',
});

// Estados locales
const showPassword = ref(false);

// Computed
const isFormValid = computed(() => {
    return form.value.email.length > 0 && form.value.password.length > 0;
});

const buttonText = computed(() => {
    return isLoading.value ? 'Iniciando sesión...' : 'Entrar a mi cuenta';
});

// Métodos
const handleLogin = async () => {
    if (!isFormValid.value || isLoading.value) return;
    
    clearError();
    
    try {
        await login(form.value);
    } catch (err) {
        // El error ya se maneja en el composable
        console.error('Login failed:', err);
    }
};

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <div class="min-h-screen bg-stone-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <Head title="Iniciar Sesión" />
        
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <h1 class="text-4xl font-serif text-stone-900 mb-2">
                UsPage<span class="text-rose-500">.love</span>
            </h1>
            <p class="text-stone-500 font-sans">Tu historia, en un solo link.</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-sm border border-stone-100 rounded-2xl sm:px-10">
                <!-- Mensaje de estado (success) -->
                <div v-if="status" class="mb-4 p-3 bg-green-50 border border-green-200 text-green-600 text-sm rounded-lg text-center">
                    {{ status }}
                </div>

                <!-- Mensaje de error -->
                <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg">
                    {{ error }}
                </div>

                <form @submit.prevent="handleLogin" class="space-y-6">
                    <!-- Campo Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-stone-700">
                            Email
                        </label>
                        <input 
                            id="email"
                            v-model="form.email" 
                            type="email" 
                            autocomplete="email"
                            required 
                            :disabled="isLoading"
                            @input="clearError"
                            placeholder="tu@email.com"
                            class="mt-1 block w-full px-3 py-2 border border-stone-200 rounded-xl focus:ring-rose-500 focus:border-rose-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors" 
                        />
                    </div>

                    <!-- Campo Contraseña -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-sm font-medium text-stone-700">
                                Contraseña
                            </label>
                            <Link 
                                v-if="canResetPassword"
                                href="/forgot-password" 
                                class="text-xs text-stone-400 hover:text-rose-500 transition-colors duration-200"
                            >
                                ¿Olvidaste tu contraseña?
                            </Link>
                        </div>
                        <div class="relative">
                            <input 
                                id="password"
                                v-model="form.password" 
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password" 
                                required 
                                :disabled="isLoading"
                                @input="clearError"
                                placeholder="Tu contraseña"
                                class="block w-full px-3 py-2 pr-10 border border-stone-200 rounded-xl focus:ring-rose-500 focus:border-rose-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors" 
                            />
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                :disabled="isLoading"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 hover:text-stone-600 disabled:cursor-not-allowed"
                            >
                                <span class="text-sm">
                                    {{ showPassword ? '🙈' : '👁️' }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Botón de login -->
                    <button 
                        type="submit" 
                        :disabled="!isFormValid || isLoading"
                        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                    >
                        <svg 
                            v-if="isLoading" 
                            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" 
                            xmlns="http://www.w3.org/2000/svg" 
                            fill="none" 
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ buttonText }}
                    </button>
                </form>

                <!-- Link a registro -->
                <div class="mt-6 text-center">
                    <Link 
                        v-if="canRegister"
                        href="/register" 
                        class="text-xs text-stone-400 hover:text-rose-500 transition-colors duration-200"
                    >
                        ¿No tienes cuenta? Regístrate
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
