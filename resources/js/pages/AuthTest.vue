<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useAuth } from '@/composables/useAuthSimple';

// Props que vienen del servidor
interface Props {
    test_data?: {
        user: any;
        is_authenticated: boolean;
        email_verified: string | null;
        guard: string;
    };
}

const props = defineProps<Props>();

// Estado del frontend con el nuevo composable
const { user, isAuthenticated } = useAuth();

console.log('[DEBUG] AuthTest - Props del servidor:', props.test_data);
console.log('[DEBUG] AuthTest - Estado del frontend:', {
    user: user.value,
    isAuthenticated: isAuthenticated.value
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 py-12">
        <Head title="Test de Autenticación" />
        
        <div class="max-w-4xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">🔍 Test de Autenticación</h1>
                
                <!-- Estado del Servidor (Backend) -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">📊 Estado del Servidor (Backend)</h2>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div v-if="props.test_data" class="space-y-2">
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-40">Usuario:</span>
                                <span v-if="props.test_data.user" class="text-green-600">
                                    ✅ {{ props.test_data.user.name }} ({{ props.test_data.user.email }})
                                </span>
                                <span v-else class="text-red-600">❌ No autenticado</span>
                            </div>
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-40">Autenticado:</span>
                                <span :class="props.test_data.is_authenticated ? 'text-green-600' : 'text-red-600'">
                                    {{ props.test_data.is_authenticated ? '✅ SI' : '❌ NO' }}
                                </span>
                            </div>
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-40">Email verificado:</span>
                                <span :class="props.test_data.email_verified ? 'text-green-600' : 'text-red-600'">
                                    {{ props.test_data.email_verified ? '✅ SI' : '❌ NO' }}
                                </span>
                            </div>
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-40">Guard:</span>
                                <span class="text-blue-600">{{ props.test_data.guard }}</span>
                            </div>
                        </div>
                        <div v-else class="text-red-600">❌ No hay datos del servidor</div>
                    </div>
                </div>

                <!-- Estado del Frontend (Vue/JavaScript) -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">🖥️ Estado del Frontend (Vue/JavaScript)</h2>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-40">Usuario:</span>
                                <span v-if="user" class="text-green-600">
                                    ✅ {{ user.name }} ({{ user.email }})
                                </span>
                                <span v-else class="text-red-600">❌ No autenticado</span>
                            </div>
                            <div class="flex items-center">
                                <span class="font-medium text-gray-700 w-40">Autenticado:</span>
                                <span :class="isAuthenticated ? 'text-green-600' : 'text-red-600'">
                                    {{ isAuthenticated ? '✅ SI' : '❌ NO' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instrucciones -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">📝 Instrucciones</h2>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div v-if="!isAuthenticated && !props.test_data?.is_authenticated" class="text-blue-800">
                            <p class="font-medium mb-2">🚨 NO ESTÁS AUTENTICADO</p>
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Ve a <a href="/login" class="text-blue-600 underline hover:text-blue-800">/login</a></li>
                                <li>Inicia sesión con: <strong>kevin1@gmail.com</strong> / <strong>12345678</strong></li>
                                <li>Regresa a esta página para verificar</li>
                                <li>Luego ve a <a href="/themes" class="text-blue-600 underline hover:text-blue-800">/themes</a></li>
                            </ol>
                        </div>
                        <div v-else class="text-green-800">
                            <p class="font-medium mb-2">✅ AUTENTICADO CORRECTAMENTE</p>
                            <p>Ya puedes ir a <a href="/themes" class="text-green-600 underline hover:text-green-800">/themes</a> sin problemas</p>
                        </div>
                    </div>
                </div>

                <!-- Enlaces rápidos -->
                <div class="flex gap-4">
                    <a href="/login" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        🔑 Ir a Login
                    </a>
                    <a href="/dashboard" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        📊 Dashboard
                    </a>
                    <a href="/themes" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                        🎨 Themes
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>