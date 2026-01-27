<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import { useAuth } from '@/composables/useAuth';
import type { RegisterData } from '@/types/auth';

defineProps<{
    canLogin?: boolean;
}>();

// Composables
const { register, isLoading, error, clearError } = useAuth();

// Form data reactivo
const form = ref<RegisterData & { password_confirmation: string }>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// Estados locales
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Computed
const isFormValid = computed(() => {
    return form.value.name.length > 0 && 
           form.value.email.length > 0 && 
           form.value.password.length >= 8 && 
           form.value.password === form.value.password_confirmation;
});

const buttonText = computed(() => {
    return isLoading.value ? 'Creando cuenta...' : 'Crear cuenta';
});

const passwordsMatch = computed(() => {
    if (form.value.password_confirmation.length === 0) return true;
    return form.value.password === form.value.password_confirmation;
});

// Métodos
const handleRegister = async () => {
    if (!isFormValid.value || isLoading.value) return;
    
    clearError();
    
    try {
        const registerData: RegisterData = {
            name: form.value.name,
            email: form.value.email,
            password: form.value.password,
        };
        
        await register(registerData);
    } catch (err) {
        // El error ya se maneja en el composable
        console.error('Register failed:', err);
    }
};

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const togglePasswordConfirmationVisibility = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
};
</script>

<template>
    <div class="min-h-screen bg-stone-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <Head title="Crear Cuenta" />
        
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <h1 class="text-4xl font-serif text-stone-900 mb-2">
                UsPage<span class="text-rose-500">.love</span>
            </h1>
            <p class="text-stone-500 font-sans">Únete a nuestra comunidad del amor.</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-sm border border-stone-100 rounded-2xl sm:px-10">
                <!-- Mensaje de error -->
                <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg">
                    {{ error }}
                </div>

                <form @submit.prevent="handleRegister" class="space-y-6">
                    <!-- Campo Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-stone-700">
                            Nombre completo
                        </label>
                        <input 
                            id="name"
                            v-model="form.name" 
                            type="text" 
                            autocomplete="name"
                            required 
                            :disabled="isLoading"
                            @input="clearError"
                            placeholder="Tu nombre completo"
                            class="mt-1 block w-full px-3 py-2 border border-stone-200 rounded-xl focus:ring-rose-500 focus:border-rose-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors" 
                        />
                    </div>

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
                        <label for="password" class="block text-sm font-medium text-stone-700">
                            Contraseña
                        </label>
                        <div class="relative">
                            <input 
                                id="password"
                                v-model="form.password" 
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password" 
                                required 
                                :disabled="isLoading"
                                @input="clearError"
                                placeholder="Mínimo 8 caracteres"
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
                        <p v-if="form.password.length > 0 && form.password.length < 8" class="mt-1 text-xs text-red-500">
                            La contraseña debe tener al menos 8 caracteres
                        </p>
                    </div>

                    <!-- Campo Confirmar Contraseña -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-stone-700">
                            Confirmar contraseña
                        </label>
                        <div class="relative">
                            <input 
                                id="password_confirmation"
                                v-model="form.password_confirmation" 
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                autocomplete="new-password" 
                                required 
                                :disabled="isLoading"
                                @input="clearError"
                                placeholder="Confirma tu contraseña"
                                :class="[
                                    'block w-full px-3 py-2 pr-10 rounded-xl focus:ring-rose-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors',
                                    passwordsMatch ? 'border-stone-200 focus:border-rose-500' : 'border-red-300 focus:border-red-500'
                                ]"
                            />
                            <button
                                type="button"
                                @click="togglePasswordConfirmationVisibility"
                                :disabled="isLoading"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 hover:text-stone-600 disabled:cursor-not-allowed"
                            >
                                <span class="text-sm">
                                    {{ showPasswordConfirmation ? '🙈' : '👁️' }}
                                </span>
                            </button>
                        </div>
                        <p v-if="!passwordsMatch" class="mt-1 text-xs text-red-500">
                            Las contraseñas no coinciden
                        </p>
                    </div>

                    <!-- Botón de registro -->
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

                <!-- Link a login -->
                <div class="mt-6 text-center">
                    <Link 
                        v-if="canLogin"
                        href="/login" 
                        class="text-xs text-stone-400 hover:text-rose-500 transition-colors duration-200"
                    >
                        ¿Ya tienes cuenta? Inicia sesión
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Full name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="6"
                    >Log in</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
