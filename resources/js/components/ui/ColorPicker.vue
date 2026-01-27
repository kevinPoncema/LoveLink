<script setup lang="ts">
import { ref, watch, computed } from 'vue';

interface Props {
    modelValue: string;
    label?: string;
    placeholder?: string;
    disabled?: boolean;
    presetColors?: Array<{name: string; value: string}>;
}

interface Emits {
    (e: 'update:modelValue', value: string): void;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: '#000000',
    disabled: false,
    presetColors: () => []
});

const emit = defineEmits<Emits>();

// Estado local
const showPicker = ref(false);
const colorInput = ref<HTMLInputElement>();

// Computed
const isValidColor = computed(() => {
    const hexRegex = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
    return hexRegex.test(props.modelValue);
});

const displayValue = computed({
    get: () => props.modelValue,
    set: (value: string) => emit('update:modelValue', value)
});

// Colores predefinidos por defecto
const defaultPresets = [
    { name: 'Rojo', value: '#EF4444' },
    { name: 'Naranja', value: '#F97316' },
    { name: 'Amarillo', value: '#EAB308' },
    { name: 'Verde', value: '#22C55E' },
    { name: 'Azul', value: '#3B82F6' },
    { name: 'Índigo', value: '#6366F1' },
    { name: 'Púrpura', value: '#A855F7' },
    { name: 'Rosa', value: '#EC4899' },
    { name: 'Negro', value: '#000000' },
    { name: 'Gris', value: '#6B7280' },
    { name: 'Blanco', value: '#FFFFFF' },
];

const presets = computed(() => {
    return props.presetColors.length > 0 ? props.presetColors : defaultPresets;
});

// Métodos
const openColorPicker = () => {
    if (!props.disabled) {
        showPicker.value = true;
    }
};

const closeColorPicker = () => {
    showPicker.value = false;
};

const selectPreset = (color: string) => {
    displayValue.value = color;
    closeColorPicker();
};

const handleInputChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    displayValue.value = target.value;
};

const handleColorPickerChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    displayValue.value = target.value;
};

// Cerrar al hacer clic fuera
const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.color-picker-container')) {
        closeColorPicker();
    }
};

watch(() => showPicker.value, (newValue) => {
    if (newValue) {
        document.addEventListener('click', handleClickOutside);
    } else {
        document.removeEventListener('click', handleClickOutside);
    }
});
</script>

<template>
    <div class="color-picker-container relative">
        <label v-if="label" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
            {{ label }}
        </label>
        
        <div class="relative">
            <!-- Input principal con preview del color -->
            <div 
                @click="openColorPicker"
                class="flex items-center w-full px-3 py-2 border border-stone-200 dark:border-stone-600 rounded-xl bg-white dark:bg-stone-700 cursor-pointer hover:border-stone-300 dark:hover:border-stone-500 transition-colors"
                :class="[
                    disabled ? 'opacity-50 cursor-not-allowed' : '',
                    !isValidColor ? 'border-red-300 dark:border-red-600' : ''
                ]"
            >
                <!-- Vista previa del color -->
                <div 
                    class="w-8 h-8 rounded-lg border-2 border-stone-200 dark:border-stone-600 mr-3 flex-shrink-0"
                    :style="{ backgroundColor: isValidColor ? modelValue : '#cccccc' }"
                ></div>
                
                <!-- Campo de texto -->
                <input
                    :value="modelValue"
                    @input="handleInputChange"
                    :placeholder="placeholder"
                    :disabled="disabled"
                    class="flex-1 bg-transparent text-stone-900 dark:text-stone-100 placeholder:text-stone-400 dark:placeholder:text-stone-500 focus:outline-none text-sm font-mono"
                />
                
                <!-- Ícono -->
                <div class="text-stone-400 dark:text-stone-500 ml-2">
                    🎨
                </div>
            </div>
            
            <!-- Input color picker oculto -->
            <input
                ref="colorInput"
                type="color"
                :value="modelValue"
                @input="handleColorPickerChange"
                class="absolute opacity-0 pointer-events-none"
                :disabled="disabled"
            />
        </div>

        <!-- Dropdown con paleta de colores -->
        <div
            v-if="showPicker"
            class="absolute z-50 mt-2 w-80 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl shadow-lg p-4"
        >
            <!-- Color picker nativo -->
            <div class="mb-4">
                <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-2">
                    Selector de color
                </label>
                <input
                    type="color"
                    :value="modelValue"
                    @input="handleColorPickerChange"
                    class="w-full h-10 rounded-lg border border-stone-200 dark:border-stone-600 cursor-pointer"
                />
            </div>
            
            <!-- Input manual -->
            <div class="mb-4">
                <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-2">
                    Código hexadecimal
                </label>
                <input
                    :value="modelValue"
                    @input="handleInputChange"
                    placeholder="#000000"
                    class="w-full px-3 py-2 border border-stone-200 dark:border-stone-600 rounded-lg bg-white dark:bg-stone-700 text-stone-900 dark:text-stone-100 text-sm font-mono focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                    :class="!isValidColor ? 'border-red-300 dark:border-red-600' : ''"
                />
                <p v-if="!isValidColor" class="mt-1 text-xs text-red-500 dark:text-red-400">
                    Formato inválido. Use #000000 o #000
                </p>
            </div>
            
            <!-- Paleta predefinida -->
            <div>
                <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-2">
                    Colores predefinidos
                </label>
                <div class="grid grid-cols-6 gap-2">
                    <button
                        v-for="preset in presets"
                        :key="preset.value"
                        @click="selectPreset(preset.value)"
                        :title="preset.name"
                        class="w-8 h-8 rounded-lg border-2 hover:scale-110 transition-transform focus:outline-none focus:ring-2 focus:ring-rose-500"
                        :style="{ backgroundColor: preset.value }"
                        :class="[
                            preset.value === modelValue 
                                ? 'border-rose-500 ring-2 ring-rose-500' 
                                : 'border-stone-200 dark:border-stone-600'
                        ]"
                    ></button>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-stone-200 dark:border-stone-700">
                <button
                    @click="closeColorPicker"
                    class="px-3 py-1.5 text-sm text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200"
                >
                    Cerrar
                </button>
            </div>
        </div>
        
        <!-- Error state -->
        <p v-if="!isValidColor" class="mt-1 text-xs text-red-500 dark:text-red-400">
            Color inválido
        </p>
    </div>
</template>

<style scoped>
/* Estilos adicionales si necesarios */
</style>