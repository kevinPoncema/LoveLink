# Estructura Frontend - Laravel Inertia + Vue 3 + TypeScript

## Introducción

Este documento describe la arquitectura frontend de la aplicación, construida sobre **Laravel Inertia.js v2** con **Vue 3**, **Tailwind CSS v4** y **TypeScript**. La aplicación funciona como un monolito donde el backend Laravel sirve las páginas Vue a través de Inertia, proporcionando la experiencia de una SPA (Single Page Application).

## Estructura General

```
resources/js/
├── app.ts                    # Punto de entrada principal de la aplicación
├── ssr.ts                    # Configuración para Server-Side Rendering
├── actions/                  # Funciones generadas por Wayfinder para controladores Laravel
├── routes/                   # Funciones generadas por Wayfinder para rutas nombradas
├── components/              # Componentes Vue reutilizables (incluye ui/)
├── layouts/                 # Layouts base para las páginas
├── composables/             # Lógica reutilizable de Vue 3 Composition API
├── services/                # Servicios de comunicación con la API
├── lib/                     # Utilidades y funciones auxiliares
└── types/                   # Definiciones de tipos TypeScript
```

## Análisis Detallado por Carpeta

### 1. `pages/` - Páginas de la Aplicación

**Estructura actual:**
- `Dashboard.vue` - Estadísticas y overview.
- `Welcome.vue` - Landing page pública.
- `Themes.vue` - Gestión de temas personalizados.
- `Media.vue` - Biblioteca de medios.
- `Invitations/`
    - `Index.vue` - Listado de invitaciones del usuario.
    - `Form.vue` - Creación y edición de invitaciones (incluye selector de temas).
- `Public/`
    - `Invitation.vue` - Vista pública de la invitación (Optimizada para móviles).
- `auth/` - Páginas de autenticación (Login, Register).
- `settings/` - Configuración de perfil.

### 2. `composables/` - Lógica Reutilizable

**Composables clave:**
- `useInvitations.ts` - CRUD y lógica de estado para invitaciones.
- `useThemes.ts` - Carga y filtrado de temas (Sistema vs Usuario).
- `useAuth.ts` - Gestión de sesión y usuario actual.
- `useDashboard.ts` - Consumo de estadísticas.

### 3. `services/` - Capa de Comunicación API

Se utiliza una arquitectura de servicios para peticiones que no requieren navegación Inertia (AJAX):
- `InvitationService.ts`: Maneja `GET`, `POST`, `PUT`, `DELETE` de invitaciones.
- `ThemeService.ts`: Gestión de temas y subida de imágenes de fondo.
- `MediaService.ts`: Subida agnóstica de archivos (soporta Local y S3).

### 4. `components/` - UI y Layouts

**Componentes Destacados:**
- `ui/`: Basados en Shadcn/Vue, estilizados con Tailwind v4.
- `AppShell.vue`: Estructura principal con Sidebar y Header.

## Estilado con Tailwind CSS v4

La aplicación utiliza **Tailwind CSS v4**, que introduce cambios significativos en la configuración:

- **Configuración vía CSS**: Ya no se usa `tailwind.config.js` de forma extensiva, las variables se definen en `@theme` dentro de `app.css`.
- **Nuevas Utilidades**: 
    - `aspect-3/4`: Utilizado para las tarjetas de invitación.
    - `bg-linear-to-b`: Nuevos degradados.
    - Soporte nativo para contenedores fluidos y contenedores `@container`.

## Integración con Wayfinder

**Laravel Wayfinder** vincula el backend con el frontend generando tipos y funciones para las rutas:

```typescript
// Ejemplo en Invitations/Index.vue
import { invitations } from '@/routes';
import { Link } from '@inertiajs/vue3';

// Navigate to edit
<Link :href="invitations.edit(id)">Editar</Link>
```

## Manejo de Imágenes y Almacenamiento

El frontend maneja URLs de imágenes que pueden ser:
1. **Locales**: `/storage/media/filename.jpg`
2. **Nube (S3/R2)**: `https://bucket.s3.amazonaws.com/path/filename.jpg`

El modelo `Media` en el backend asegura que la URL sea absoluta, permitiendo que el componente `Invitation.vue` renderice fondos sin importar el driver de almacenamiento.

## Ciclo de Vida de una Invitación

1. **Creación**: El usuario elige un título y un tema en `Invitations/Form.vue`.
2. **Personalización**: Se asocian imágenes mediante `MediaService`.
3. **Publicación**: Se genera un slug único.
4. **Visualización**: El invitado accede a `/i/{slug}`, donde `Public/Invitation.vue` renderiza el contenido usando el tema seleccionado (colores, fuentes, fondos).

---

## Solución de Autenticación Híbrida

Se implementó una solución para el error `401 Unauthenticated` en peticiones API desde Inertia:
- **Middleware**: `EnsureFrontendRequestsAreStateful` habilitado en `api` group.
- **Axios**: `withCredentials: true` configurado globalmente en `ApiClient.ts`.
- **Contexto**: Permite usar las bondades de las rutas API autenticadas sin manejar tokens JWT manualmente en el navegador.

