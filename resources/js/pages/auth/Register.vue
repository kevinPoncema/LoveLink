<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
}>();

// Usar useForm de Inertia
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

// Estados locales
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Métodos
const submit = () => {
    form.post('/api/auth/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const togglePasswordConfirmationVisibility = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
};
</script>

<template>
    <div class="min-h-screen bg-stone-50 dark:bg-stone-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <Head title="Crear Cuenta" />

        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <h1 class="text-4xl font-serif text-stone-900 dark:text-stone-100 mb-2">
                LoveLink
            </h1>
            <p class="text-stone-500 dark:text-stone-400 font-sans">Únete a nuestra comunidad del amor.</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white dark:bg-stone-800 py-8 px-4 shadow-sm border border-stone-100 dark:border-stone-700 rounded-2xl sm:px-10">
                <!-- Mensajes de error -->
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 text-sm rounded-lg">
                    <ul class="list-disc list-inside space-y-1">
                        <li v-for="error in form.errors" :key="error">{{ error }}</li>
                    </ul>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Campo Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                            Nombre completo
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            autocomplete="name"
                            required
                            :disabled="form.processing"
                            placeholder="Tu nombre completo"
                            class="mt-1 block w-full px-3 py-2 border border-stone-200 dark:border-stone-600 rounded-xl text-stone-900 dark:text-stone-100 bg-white dark:bg-stone-700 focus:ring-rose-500 focus:border-rose-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors placeholder:text-stone-400 dark:placeholder:text-stone-500"
                        />
                    </div>

                    <!-- Campo Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            :disabled="form.processing"
                            placeholder="tu@email.com"
                            class="mt-1 block w-full px-3 py-2 border border-stone-200 dark:border-stone-600 rounded-xl text-stone-900 dark:text-stone-100 bg-white dark:bg-stone-700 focus:ring-rose-500 focus:border-rose-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors placeholder:text-stone-400 dark:placeholder:text-stone-500"
                        />
                    </div>

                    <!-- Campo Contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                            Contraseña
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                :disabled="form.processing"
                                placeholder="Mínimo 8 caracteres"
                                class="block w-full px-3 py-2 pr-10 border border-stone-200 dark:border-stone-600 rounded-xl text-stone-900 dark:text-stone-100 bg-white dark:bg-stone-700 focus:ring-rose-500 focus:border-rose-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors placeholder:text-stone-400 dark:placeholder:text-stone-500"
                            />
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                :disabled="form.processing"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 dark:text-stone-500 hover:text-stone-600 dark:hover:text-stone-400 disabled:cursor-not-allowed"
                            >
                                <span class="text-sm">
                                    {{ showPassword ? '🙈' : '👁️' }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Campo Confirmar Contraseña -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                            Confirmar contraseña
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                :disabled="form.processing"
                                placeholder="Confirma tu contraseña"
                                class="block w-full px-3 py-2 pr-10 border border-stone-200 dark:border-stone-600 rounded-xl text-stone-900 dark:text-stone-100 bg-white dark:bg-stone-700 focus:ring-rose-500 focus:border-rose-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors placeholder:text-stone-400 dark:placeholder:text-stone-500"
                            />
                            <button
                                type="button"
                                @click="togglePasswordConfirmationVisibility"
                                :disabled="form.processing"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 dark:text-stone-500 hover:text-stone-600 dark:hover:text-stone-400 disabled:cursor-not-allowed"
                            >
                                <span class="text-sm">
                                    {{ showPasswordConfirmation ? '🙈' : '👁️' }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Botón de registro -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                    >
                        <svg
                            v-if="form.processing"
                            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Creando cuenta...' : 'Crear cuenta' }}
                    </button>
                </form>

                <!-- Link a login -->
                <div class="mt-6 text-center">
                    <Link
                        v-if="canLogin"
                        href="/login"
                        class="text-xs text-stone-400 dark:text-stone-500 hover:text-rose-500 dark:hover:text-rose-400 transition-colors duration-200"
                    >
                        ¿Ya tienes cuenta? Inicia sesión
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
