# Estructura Frontend - Laravel Inertia + Vue 3 + TypeScript

## Introducción

Este documento describe la arquitectura frontend de la aplicación, construida sobre **Laravel Inertia.js v2** con **Vue 3** y **TypeScript**. La aplicación funciona como un monolito donde el backend Laravel sirve las páginas Vue a través de Inertia, proporcionando la experiencia de una SPA (Single Page Application) sin la complejidad de arquitecturas completamente separadas.

## Estructura General

```
resources/js/
├── app.ts                    # Punto de entrada principal de la aplicación
├── ssr.ts                    # Configuración para Server-Side Rendering
├── actions/                  # Funciones generadas por Wayfinder para controladores Laravel
├── routes/                   # Funciones generadas por Wayfinder para rutas nombradas
├── wayfinder/               # Configuración y tipos de Wayfinder
├── pages/                   # Páginas Vue (equivalente a vistas/controladores)
├── components/              # Componentes Vue reutilizables
├── layouts/                 # Layouts base para las páginas
├── composables/             # Lógica reutilizable de Vue 3 Composition API
├── lib/                     # Utilidades y funciones auxiliares
└── types/                   # Definiciones de tipos TypeScript
```

## Análisis Detallado por Carpeta

### 1. `app.ts` - Punto de Entrada

Es el archivo principal que configura la aplicación Inertia:

- **Configuración de Inertia**: Establece la resolución de páginas y el título de la aplicación
- **Montaje de Vue**: Crea la instancia de Vue y la monta en el DOM
- **Inicialización de tema**: Configura el sistema de temas light/dark
- **Configuración de progreso**: Define la barra de progreso para navegación

### 2. `pages/` - Páginas de la Aplicación

**Estructura actual:**
- `Dashboard.vue` - Página principal del dashboard
- `Welcome.vue` - Página de bienvenida
- `auth/` - Páginas de autenticación
- `settings/` - Páginas de configuración

**Convenciones:**
- Cada archivo `.vue` representa una página accesible
- Se mapean automáticamente a rutas Laravel através de Inertia
- Deben usar layouts apropiados

### 3. `components/` - Componentes Reutilizables

**Estructura actual:**
```
components/
├── AppContent.vue           # Contenedor principal de contenido
├── AppHeader.vue            # Header de la aplicación
├── AppLogo.vue             # Logo de la aplicación
├── AppShell.vue            # Shell principal de la UI
├── AppSidebar.vue          # Barra lateral
├── Navigation/             # Componentes de navegación
├── Auth/                   # Componentes específicos de autenticación
└── ui/                     # Componentes de UI reutilizables
    ├── alert/
    ├── avatar/
    ├── badge/
    ├── button/
    ├── card/
    ├── input/
    └── ...                 # Más componentes UI
```

**Convenciones:**
- Componentes específicos de dominio en carpetas temáticas
- Componentes UI genéricos en `ui/`
- Nomenclatura PascalCase
- Props tipadas con TypeScript

### 4. `layouts/` - Layouts Base

**Estructura actual:**
- `AppLayout.vue` - Layout principal de la aplicación
- `AuthLayout.vue` - Layout para páginas de autenticación
- Subcarpetas organizadas por contexto (`app/`, `auth/`, `settings/`)

### 5. `composables/` - Lógica Reutilizable

**Composables implementados:**
- `useAppearance.ts` - Gestión de temas (light/dark/system)
- `useCurrentUrl.ts` - Utilidades para URL actual
- `useInitials.ts` - Generación de iniciales de usuario
- `useTwoFactorAuth.ts` - Lógica de autenticación de dos factores
- **`useAuth.ts`** - **NUEVO**: Gestión completa de autenticación
- **`useDashboard.ts`** - **NUEVO**: Estadísticas y datos del dashboard

**Nuevas características:**
```typescript
// useAuth.ts - Estado global de autenticación
const { 
  user,              // Usuario actual (reactivo)
  isAuthenticated,   // Estado de autenticación (computed)
  isLoading,         // Estado de carga
  error,             // Errores de autenticación
  login,             // Función de login
  register,          // Función de registro
  logout,            // Función de logout
  checkAuth          // Verificar autenticación
} = useAuth()

// useDashboard.ts - Estadísticas del dashboard
const {
  stats,             // Estadísticas (landings, invitations, media)
  isLoading,         // Estado de carga
  error,             // Errores de carga
  loadStats,         // Cargar estadísticas
  refreshStats       // Refrescar datos
} = useDashboard()
```

**Convenciones:**
- Prefijo `use` para todos los composables
- Return tipado con TypeScript
- Reutilizables entre páginas y componentes

### 6. `actions/` y `routes/` - Integración Laravel (Wayfinder)

**Laravel Wayfinder** genera automáticamente funciones TypeScript para:
- **`actions/`**: Controladores Laravel invocables
- **`routes/`**: Rutas nombradas de Laravel

**Ejemplo de uso:**
```typescript
// Para controladores
import StorePost from '@/actions/App/Http/Controllers/StorePostController'
StorePost({ title: 'Mi Post' })

// Para rutas nombradas  
import { dashboard } from '@/routes'
dashboard()
```

### 7. `services/` - Servicios API Organizados por Entidad

**NUEVA IMPLEMENTACIÓN**: Servicios completamente estructurados por dominio:

```
services/
├── ApiClient.ts                 # Cliente HTTP base con interceptors
├── index.ts                     # Exportaciones centralizadas
├── auth/AuthService.ts          # Autenticación y gestión de usuarios
├── landing/LandingService.ts    # CRUD de landing pages
├── invitation/InvitationService.ts # CRUD de invitaciones
├── media/MediaService.ts        # Upload y gestión de archivos
└── theme/ThemeService.ts        # CRUD de temas personalizados
```

**Características:**
- **Cliente HTTP inteligente**: Interceptors automáticos para tokens y errores 401
- **Validación de archivos**: Tipos MIME y tamaños permitidos
- **Manejo de FormData**: Upload de archivos con progress
- **Error handling**: Gestión centralizada de errores de red
- **Singleton pattern**: Instancias únicas y reutilizables

**Ejemplo de uso:**
```typescript
import { authService, landingService, mediaService } from '@/services'

// Login
const authResponse = await authService.login({ email, password })

// Crear landing
const landing = await landingService.createLanding({
  couple_names: 'Juan & María',
  anniversary_date: '2024-02-14',
  theme_id: 1
})

// Upload media
const media = await mediaService.uploadMedia(file)
await landingService.attachMedia(landing.id, media.id)
```

### 8. `lib/` - Utilidades

Contiene funciones auxiliares y utilidades generales:
- `utils.ts` - Funciones de utilidad general

### 8. `lib/` - Utilidades

Contiene funciones auxiliares y utilidades generales:
- `utils.ts` - Funciones de utilidad general

### 9. `types/` - Definiciones TypeScript

**Tipos actualizados:**
- `auth.ts` - Tipos completos de autenticación, API responses, dashboard stats
- `navigation.ts` - Tipos de navegación
- `ui.ts` - Tipos para componentes UI
- `globals.d.ts` - Declaraciones globales
- `vue-shims.d.ts` - Declaraciones para Vue

**Nuevos tipos implementados:**
```typescript
// Autenticación
export type LoginData = { email: string; password: string }
export type RegisterData = { email: string; password: string; name?: string }
export type AuthResponse = { user: User; token: string; message?: string }
export type ApiResponse<T> = { data?: T; message?: string; errors?: Record<string, string[]> }
export type DashboardStats = { landings: number; invitations: number; media: number }
```

## Comunicación con el Backend

### Peticiones API con Wayfinder + Inertia

#### 1. Navegación (Inertia)
Para navegación entre páginas:
```typescript
import { router } from '@inertiajs/vue3'

// Navegación simple
router.visit('/dashboard')

// Con datos
router.post('/posts', {
  title: 'Mi Post',
  content: 'Contenido...'
})
```

#### 2. Formularios con Inertia
```typescript
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  title: '',
  content: ''
})

// Envío
form.post('/posts')
```

#### 3. Peticiones AJAX/API
Para peticiones que no requieren navegación:
```typescript
import axios from 'axios'

// Petición GET
const response = await axios.get('/api/users')

// Con autenticación Sanctum (automática con cookies)
axios.defaults.withCredentials = true
```

#### 4. Usando Wayfinder
```typescript
import StoreUser from '@/actions/App/Http/Controllers/StoreUserController'
import { users } from '@/routes'

// Controlador invocable
const result = await StoreUser.form(userData).submit()

// Ruta nombrada
router.visit(users({ filter: 'active' }))
```

## Gestión del Estado

### 1. Props de Inertia
- **Shared Data**: Datos compartidos globalmente (usuario autenticado, configuración)
- **Page Props**: Datos específicos de cada página

### 2. Composables Reactivos
Para estado local y lógica reutilizable:
```typescript
// composables/useUserManagement.ts
export function useUserManagement() {
  const users = ref([])
  const loading = ref(false)
  
  const fetchUsers = async () => {
    loading.value = true
    // lógica de fetch
    loading.value = false
  }
  
  return {
    users: readonly(users),
    loading: readonly(loading),
    fetchUsers
  }
}
```

### 3. Provide/Inject
Para compartir estado entre componentes relacionados.

## Sistema de Temas

Implementado través de `useAppearance.ts`:
- **Modos**: `light`, `dark`, `system`
- **Persistencia**: LocalStorage
- **Detección automática**: Media queries del sistema
- **Aplicación**: Classes CSS automáticas

## Manejo de Errores

### 1. Errores de Validación
Inertia maneja automáticamente errores de validación Laravel:
```vue
<template>
  <input v-model="form.email" :class="form.errors.email ? 'error' : ''" />
  <span v-if="form.errors.email">{{ form.errors.email }}</span>
</template>
```

### 2. Errores de Red
```typescript
import { usePage } from '@inertiajs/vue3'

const page = usePage()
watch(() => page.props.errors, (errors) => {
  if (errors) {
    // Manejar errores globales
  }
})
```

## Optimización y Performance

### 1. Lazy Loading
```typescript
// Carga diferida de componentes pesados
const HeavyComponent = defineAsyncComponent(() => import('./HeavyComponent.vue'))
```

### 2. Props Diferidas (Inertia v2)
```php
// En el controlador Laravel
return Inertia::render('Dashboard', [
  'user' => $user,
  'stats' => Inertia::defer(fn() => $this->getExpensiveStats())
])
```

### 3. Prefetching
```typescript
import { router } from '@inertiajs/vue3'

// Precargar página antes de navegar
router.preload('/dashboard')
```

## Convenciones de Desarrollo

### 1. Nomenclatura
- **Componentes**: PascalCase (`UserCard.vue`)
- **Composables**: camelCase con prefijo `use` (`useUserData.ts`)
- **Tipos**: PascalCase (`interface UserData`)
- **Constantes**: UPPER_CASE (`API_ENDPOINTS`)

### 2. Estructura de Componentes
```vue
<script setup lang="ts">
// 1. Imports
// 2. Props/Emits definitions
// 3. Composables
// 4. Reactive data
// 5. Computed
// 6. Methods
// 7. Lifecycle hooks
</script>

<template>
  <!-- Template -->
</template>

<style scoped>
/* Estilos con Tailwind CSS */
</style>
```

### 3. Manejo de Props
```typescript
interface Props {
  user: User
  optional?: string
}

const props = withDefaults(defineProps<Props>(), {
  optional: 'default value'
})
```

### 4. Testing
- **Unit Tests**: Para composables y utilidades
- **Component Tests**: Para componentes Vue
- **Feature Tests**: Para flujos completos (Laravel)

## Herramientas de Desarrollo

### 1. TypeScript
- Tipado estricto habilitado
- Inferencia automática en templates Vue
- Integración con Vue 3 Composition API

### 2. Vite
- Hot Module Replacement (HMR)
- Bundling optimizado
- Soporte para TypeScript out-of-the-box

### 3. ESLint + Prettier
- Linting automático
- Formateo de código consistente
- Integración con VS Code

### 4. Vue DevTools
- Debugging de componentes
- Inspección de Composables
- Timeline de eventos

## Mejores Prácticas

### 1. Separación de Responsabilidades
- **Páginas**: Solo coordinación y layout
- **Componentes**: Presentación y lógica local
- **Composables**: Lógica de negocio reutilizable
- **Services**: Comunicación con APIs

### 2. Performance
- Usar `v-memo` para listas grandes
- Lazy loading para rutas y componentes
- Optimizar re-renders con `computed`

### 3. Seguridad
- Sanitización automática de Vue
- Validación en cliente Y servidor
- Uso de tokens CSRF (automático con Inertia)

### 4. Accesibilidad
- Uso de componentes UI accesibles
- Navegación por teclado
- Etiquetas semánticas
- Soporte para lectores de pantalla

Este documento sirve como guía base para el desarrollo frontend. La arquitectura está diseñada para ser escalable, mantenible y eficiente, aprovechando al máximo las capacidades de Laravel Inertia + Vue 3.

## Solución de Problemas Comunes

### Error 401 en Rutas API (Unauthenticated)

**Problema:**
Al intentar acceder a rutas protegidas de la API (`/api/themes`, etc.) desde el frontend, se recibía un error `401 Unauthenticated` a pesar de estar logueado en la aplicación web.

**Causa:**
Laravel Sanctum por defecto espera un token Bearer en el header `Authorization` para rutas API. Sin embargo, en una arquitectura híbrida SPA/Monolito como esta (Inertia), la autenticación se maneja vía cookies de sesión (web guard), y Sanctum no estaba configurado para aceptar estas cookies en las rutas API.

**Solución:**

1. **Backend (`bootstrap/app.php`):**
   Se agregó el middleware `EnsureFrontendRequestsAreStateful` al grupo de rutas API. Esto permite que Sanctum autentique usuarios usando las cookies de sesión de Laravel en lugar de requerir un token.

   ```php
   $middleware->api(prepend: [
       \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
   ]);
   ```

2. **Frontend (`resources/js/services/ApiClient.ts`):**
   Se refactorizó el cliente API para asegurar que las cookies se envíen en cada petición y mejorar el manejo de errores.

   - Se configuró `withCredentials: true` en la instancia de Axios.
   - Se añadió un interceptor para inyectar el token CSRF (`X-XSRF-TOKEN` o `X-CSRF-TOKEN`).
   - Se eliminó la lógica innecesaria de intentar enviar tokens Bearer manualmente.
   - Se mejoró el tipado con TypeScript.

**Resultado:**
Las peticiones a la API ahora funcionan correctamente autenticándose con la sesión del usuario logueado en Inertia, sin necesidad de gestión manual de tokens JWT.