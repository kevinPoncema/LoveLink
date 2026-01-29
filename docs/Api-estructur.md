# API Estructura - UsPage

Organización conceptual de la API REST siguiendo la arquitectura **Controller → FormRequest → Service → Repository** definiendo responsabilidades, métodos y flujos de datos para cada entidad.

---

## 📋 Tabla de Contenidos

1. [Arquitectura de Capas](#arquitectura-de-capas)
2. [Estructura de Rutas](#estructura-de-rutas)
3. [Entidades y Componentes](#entidades-y-componentes)
4. [Reglas de Validación](#reglas-de-validación)
5. [Métodos de Repositories](#métodos-de-repositories)
6. [Métodos de Controllers](#métodos-de-controllers)

---

## Arquitectura de Capas

### 🎯 Responsabilidades por Capa

**Controller (Capa de Presentación)**
- **QUÉ HACE:** Maneja requests HTTP, coordina flujo, devuelve responses
- **RECIBE:** HTTP Request, parámetros de ruta, datos del formulario
- **DEVUELVE:** JSON responses, códigos de estado HTTP
- **NO DEBE:** Lógica de negocio, acceso directo a base de datos, validaciones complejas

**FormRequest (Capa de Validación)**
- **QUÉ HACE:** Valida datos de entrada, autoriza acciones básicas
- **RECIBE:** Datos del request HTTP
- **DEVUELVE:** Datos validados o errores de validación
- **CONTIENE:** Reglas de validación, mensajes personalizados, autorización de acceso

**Service (Capa de Negocio)**
- **QUÉ HACE:** Implementa lógica de negocio, coordina repositorios, maneja transacciones
- **RECIBE:** Datos validados, entidades de dominio
- **DEVUELVE:** Entidades procesadas, resultados de operaciones
- **CONTIENE:** Reglas de negocio, generación de slugs, validaciones complejas, orchestration

**Repository (Capa de Datos)**
- **QUÉ HACE:** Acceso exclusivo a base de datos, queries específicas
- **RECIBE:** Criterios de búsqueda, datos para persistir
- **DEVUELVE:** Modelos Eloquent, colecciones, resultados de queries
- **CONTIENE:** Solo queries, eager loading, filtros de datos

---

## Estructura de Rutas

### 🔐 Authentication

| Ruta | Método | Qué Hace | Qué Devuelve |
|------|--------|----------|--------------|
| `/api/auth/login` | POST | Autentica usuario con email/password | Token + datos usuario |
| `/api/auth/register` | POST | Registra nuevo usuario | Usuario creado + token |
| `/api/auth/logout` | POST | Cierra sesión del usuario autenticado | Confirmación logout |
| `/api/auth/user` | GET | Obtiene datos del usuario autenticado | Datos del usuario actual |

> **Nota:** Se utiliza `auth:sanctum,web` para permitir acceso híbrido tanto desde aplicaciones externas como desde el frontend de Inertia.

### 🎨 Themes

| Ruta | Método | Qué Hace | Qué Devuelve |
|------|--------|----------|--------------|
| `/api/themes` | GET | Lista temas sistema + temas del usuario | Colección de temas disponibles |
| `/api/themes` | POST | Crea tema personalizado para el usuario | Tema creado |
| `/api/themes/{id}` | GET | Muestra detalles de tema específico | Datos completos del tema |
| `/api/themes/{id}` | PUT | Actualiza tema del usuario | Tema actualizado |
| `/api/themes/{id}` | DELETE | Elimina tema del usuario | Confirmación eliminación |

### 🏠 Landings

| Ruta | Método | Qué Hace | Qué Devuelve |
|------|--------|----------|--------------|
| `/api/landings` | GET | Lista landings del usuario autenticado | Colección de landings |
| `/api/landings` | POST | Crea nueva landing con slug único o enviado | Landing creado |
| `/api/landings/{id}` | GET | Muestra detalles de landing (PÚBLICO) | Landing con media y tema |
| `/api/landings/{slug}` | GET | Muestra landing por slug (PÚBLICO) | Landing con media y tema |
| `/api/landings/{id}` | PUT | Actualiza datos de la landing | Landing actualizado |
| `/api/landings/{id}` | DELETE | Elimina landing del usuario | Confirmación eliminación |
| `/api/landings/{id}/media` | POST | Vincula media a landing con orden | Media attachado |
| `/api/landings/{id}/media/{mediaId}` | DELETE | Desvincula media de landing | Confirmación desvinculación |
| `/api/landings/{id}/media/reorder` | PUT | Reordena media en la landing | Nuevo orden aplicado |

### 📁 Media

| Ruta | Método | Qué Hace | Qué Devuelve |
|------|--------|----------|--------------|
| `/api/media` | GET | Lista media accesible por el usuario | Colección de archivos media |
| `/api/media` | POST | Sube nuevo archivo multimedia | Media creado con path |
| `/api/media/{id}` | DELETE | Elimina media si no está en uso | Confirmación eliminación |

> **Híbrido Local/Nube:** El sistema detecta automáticamente si el archivo está en S3 o local mediante el prefijo de la URL.

### 💌 Invitations

| Ruta | Método | Qué Hace | Qué Devuelve |
|------|--------|----------|--------------|
| `/api/invitations` | GET | Lista invitations del usuario | Colección de invitaciones |
| `/api/invitations` | POST | Crea invitation con slug único y tema | Invitation creado |
| `/api/invitations/{id}` | GET | Muestra detalles de invitation (PÚBLICO) | Invitation con media y tema |
| `/api/invitations/{slug}` | GET | Muestra invitation por slug (PÚBLICO) | Invitation con media y tema |
| `/api/invitations/{id}` | PUT | Actualiza datos de invitation | Invitation actualizado |
| `/api/invitations/{id}` | DELETE | Soft delete de invitation | Confirmación eliminación |
| `/api/invitations/{id}/media` | POST | Vincula media a invitation | Media attachado |
| `/api/invitations/{id}/media/{mediaId}` | DELETE | Desvincula media de invitation | Confirmación desvinculación |

### 🌐 Public Routes

| Ruta | Método | Qué Hace | Qué Devuelve |
|------|--------|----------|--------------|
| `/api/public/landing/{slug}` | GET | Muestra landing público por slug | Landing con tema y media |
| `/api/public/invitation/{slug}` | GET | Muestra invitation público | Invitation con media y tema |

---

## Entidades y Componentes

### 🔐 Authentication

**AuthController:**
- **login:** Recibe email/password → Devuelve token + datos usuario
- **register:** Recibe datos registro → Devuelve usuario creado + token
- **logout:** Recibe token → Devuelve confirmación logout
- **user:** Recibe token → Devuelve datos usuario actual

**LoginRequest:**
- Valida: email formato válido, password requerido
- Autoriza: siempre permitido

**RegisterRequest:**
- Valida: email único, password mínimo 8 caracteres, name opcional
- Autoriza: siempre permitido

**AuthService:**
- **authenticate:** Verifica credenciales → Genera token
- **createUser:** Hashea password, crea usuario → Retorna usuario
- **revokeTokens:** Invalida tokens → Confirma logout

**UserRepository:**
- **findByEmail:** Email → Usuario o null
- **create:** Datos validados → Usuario creado
- **findById:** ID → Usuario o null

### 🎨 Themes

**ThemeController:**
- **index:** Request usuario → Lista temas disponibles (sistema + usuario)
- **store:** Datos tema + archivo imagen opcional → Tema creado para usuario con imagen de fondo subida
- **show:** ID tema → Detalles tema si accesible
- **update:** ID + datos + archivo imagen opcional → Tema actualizado, imagen anterior reemplazada si se envía nueva
- **destroy:** ID tema → Confirmación eliminación (incluye eliminación de imagen de fondo si existe)

**StoreThemeRequest:**
- Valida: name requerido, colores formato hex, css_class válido, bg_image_file archivo imagen válido opcional
- Autoriza: usuario autenticado

**UpdateThemeRequest:**
- Valida: mismas reglas que store pero opcionales, bg_image_file archivo imagen válido opcional
- Autoriza: usuario propietario del tema o tema sistema

**ThemeService:**
- **getAvailableThemes:** Usuario → Temas sistema + temas usuario
- **createUserTheme:** Usuario + datos + archivo imagen opcional → Tema personalizado con imagen subida
- **updateTheme:** ID + datos + usuario + archivo imagen opcional → Tema actualizado, imagen anterior eliminada si se reemplaza
- **canUserModify:** Usuario + tema → boolean permisos
- **uploadBackgroundImage:** Archivo imagen + user ID → Media creado con URL pública
- **deleteOldBackgroundImage:** Media ID → void elimina imagen anterior del storage

**ThemeRepository:**
- **findSystemThemes:** void → Colección temas sistema
- **findUserThemes:** User ID → Temas del usuario
- **create:** Datos + user_id → Tema creado
- **update:** ID + datos → Tema actualizado

### 🏠 Landings

**LandingController:**
- **index:** Usuario → Lista landings del usuario únicamente (eager loads theme)
- **store:** Datos → Landing creado con slug autogenerado
- **show:** ID landing → Detalles landing si es propietario (eager loads theme, media)
- **update:** ID + datos → Landing actualizado
- **destroy:** ID → Landing eliminado

**LandingMediaController:**
- **store:** Landing ID + media → Media attachado con orden
- **destroy:** Landing ID + media ID → Media desvinculado
- **reorder:** Landing ID + array orden → Media reordenado

**StoreLandingRequest:**
- Valida: couple_names requerido, anniversary_date válida, theme_id existe, bio_text opcional
- Autoriza: usuario autenticado

**UpdateLandingRequest:**
- Valida: mismas reglas opcionales
- Autoriza: usuario propietario

**AttachMediaRequest:**
- Valida: media_id existe, sort_order numérico opcional
- Autoriza: usuario propietario de landing y media

**ReorderMediaRequest:**
- Valida: array de media_ids con orden numérico
- Autoriza: usuario propietario

**LandingService:**
- **createLanding:** Usuario + datos → Landing con slug generado si no se envió
- **updateLanding:** ID + datos + usuario → Landing actualizado
- **deleteLanding:** ID + usuario → boolean éxito
- **generateUniqueSlug:** Nombres pareja → slug único (solo si no se envió slug)
- **validateSlugUniqueness:** Slug + user ID → boolean disponible

**LandingMediaService:**
- **validateMediaLimit:** Landing ID → boolean si puede agregar más
- **getNextSortOrder:** Landing ID → próximo número orden
- **attachMediaToLanding:** Landing ID + media ID + user ID → void (usa MediaServices)
- **detachMediaFromLanding:** Landing ID + media ID + user ID → void (usa MediaServices)
- **reorderLandingMedia:** Landing ID + array orden + user ID → void (usa MediaRepository)

**LandingRepository:**
- **findByUser:** User ID → Colección landings usuario
- **findBySlug:** Slug → Landing o null
- **create:** Datos + user_id → Landing creado
- **update:** ID + datos → Landing actualizado
- **attachMedia:** Landing ID + media ID + orden → void
- **detachMedia:** Landing ID + media ID → void

### 📁 Media

**MediaController:**
- **index:** Usuario → Media accesible por usuario (via landings/invitations/themes)
- **store:** Archivo → Media subido y validado
- **destroy:** ID media → Media eliminado si no está en uso

**StoreMediaRequest:**
- Valida: archivo requerido, tipos permitidos (jpg,png,webp,gif), tamaño máximo 10MB
- Autoriza: usuario autenticado

**MediaService:**
- **uploadMedia:** UploadedFile + user ID → Media guardado con path
- **deleteMedia:** Media ID + usuario → boolean éxito
- **validateFile:** File → boolean si cumple restricciones
- **generateFilePath:** File → string path único
- **isMediaInUse:** Media ID → boolean si está vinculado
- **attachToLanding:** Landing ID + media ID + user ID + orden → void
- **detachFromLanding:** Landing ID + media ID + user ID → void
- **attachToInvitation:** Invitation ID + media ID + user ID → void
- **detachFromInvitation:** Invitation ID + media ID + user ID → void
- **attachToTheme:** Theme ID + media ID + user ID → void (para imagen de fondo)
- **detachFromTheme:** Theme ID + media ID + user ID → void
- **validateUserOwnership:** Media ID + user ID → boolean propietario
- **validateMediaLimit:** Entity type + entity ID + limit → boolean puede agregar

**MediaRepository:**
- **findUserAccessible:** User ID → Media del usuario via pivot tables y themes
- **create:** Datos archivo → Media creado
- **delete:** ID → Media eliminado
- **findById:** ID → Media o null

### 💌 Invitations

**InvitationController:**
- **index:** Usuario → Lista invitations del usuario únicamente (eager loads theme)
- **store:** Datos → Invitation creado con slug autogenerado
- **show:** ID invitation → Detalles si es propietario (eager loads theme, media)
- **update:** ID + datos → Invitation actualizado
- **destroy:** ID → Invitation eliminado (soft delete)

**InvitationMediaController:**
- **store:** Invitation ID + media → Media attachado
- **destroy:** Invitation ID + media ID → Media desvinculado

**StoreInvitationRequest:**
- Valida: title requerido, theme_id existe, yes_message opcional, no_messages array opcional
- Autoriza: usuario autenticado

**UpdateInvitationRequest:**
- Valida: mismas reglas opcionales, theme_id opcional
- Autoriza: usuario propietario

**AttachInvitationMediaRequest:**
- Valida: media_id existe y pertenece al usuario
- Autoriza: usuario propietario de invitation y media

**InvitationService:**
- **createInvitation:** Usuario + datos → Invitation con slug generado si no se envió
- **updateInvitation:** ID + datos + usuario → Invitation actualizado
- **deleteInvitation:** ID + usuario → boolean éxito (soft delete)
- **generateUniqueSlug:** Title → slug único (solo si no se envió slug)
- **validateSlugUniqueness:** Slug + user ID → boolean disponible

**InvitationMediaService:**
- **validateMediaLimit:** Invitation ID → boolean si puede agregar más
- **attachMediaToInvitation:** Invitation ID + media ID + user ID → void (usa MediaServices)
- **detachMediaFromInvitation:** Invitation ID + media ID + user ID → void (usa MediaServices)

**InvitationRepository:**
- **findByUser:** User ID → Colección invitations usuario (incluyendo soft deleted)
- **findBySlug:** Slug → Invitation publicado o null
- **create:** Datos + user_id → Invitation creado
- **update:** ID + datos → Invitation actualizado
- **attachMedia:** Invitation ID + media ID → void

### 🌐 Public Routes

**PublicLandingController:**
- **show:** Slug → Landing público con media y tema aplicado

**PublicInvitationController:**
- **show:** Slug → Invitation público con media y tema aplicado

---

## Reglas de Validación

### 🔐 Authentication (Híbrida)

Se utiliza Sanctum con soporte para web sessions (Inertia) y token cases (API externa).

**LoginRequest:**
- email: requerido, formato email válido
- password: requerido, mínimo 1 carácter

**RegisterRequest:**
- email: requerido, formato email válido, único en tabla users
- password: requerido, mínimo 8 caracteres
- name: opcional, máximo 255 caracteres

### 🎨 Themes

**StoreThemeRequest:**
- name: requerido, máximo 100 caracteres
- description: opcional, tipo texto
- primary_color: requerido, formato hex (#RRGGBB)
- secondary_color: requerido, formato hex
- bg_color: requerido, formato hex
- bg_image_file: opcional, archivo de imagen (jpg, jpeg, png, webp), máximo 10MB
- bg_image_url: opcional, URL válida, máximo 500 caracteres (auto-generado si se sube bg_image_file)
- css_class: requerido, máximo 100 caracteres

**UpdateThemeRequest:**
- Mismas reglas que StoreThemeRequest pero todas opcionales
- Si se envía bg_image_file, reemplaza la imagen anterior y actualiza bg_image_url

### 🏠 Landings

**StoreLandingRequest:**
- couple_names: requerido, máximo 200 caracteres
- slug: opcional, si no se envía se genera automáticamente desde couple_names
- anniversary_date: requerido, formato fecha válida
- theme_id: requerido, existe en tabla themes
- bio_text: opcional, tipo texto largo

**UpdateLandingRequest:**
- Mismas reglas que StoreLandingRequest pero todas opcionales

**AttachMediaRequest:**
- media_id: requerido, existe en tabla media
- sort_order: opcional, numérico entero positivo

**ReorderMediaRequest:**
- media_order: requerido, array de objetos con media_id y sort_order

### 📁 Media

**StoreMediaRequest:**
- file: requerido, archivo válido
- file.types: jpg, jpeg, png, webp, gif
- file.size: máximo 10MB (10485760 bytes)

### 💌 Invitations

**StoreInvitationRequest:**
- title: requerido, máximo 200 caracteres
- theme_id: requerido, existe en tabla themes
- slug: opcional, si no se envía se genera automáticamente desde title
- yes_message: opcional, máximo 100 caracteres, default "Sí"
- no_messages: opcional, array de strings, default ["No", "Tal vez", "No te arrepentirás", "Piénsalo mejor"]

**UpdateInvitationRequest:**
- Mismas reglas que StoreInvitationRequest pero todas opcionales
- theme_id: opcional, existe en tabla themes

**AttachInvitationMediaRequest:**
- media_id: requerido, existe en tabla media

---

## Métodos de Repositories

### 🔐 UserRepository

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| findByEmail | string email | User o null | Busca usuario por email |
| create | array datos | User | Crea nuevo usuario |
| findById | int id | User o null | Busca usuario por ID |

### 🎨 ThemeRepository

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| findSystemThemes | void | Collection | Obtiene temas del sistema |
| findUserThemes | int userId | Collection | Obtiene temas del usuario |
| getSystemAndUserThemes | int userId | Collection | Temas sistema + usuario |
| create | array datos | Theme | Crea nuevo tema |
| update | int id, array datos | Theme | Actualiza tema existente |
| findById | int id | Theme o null | Busca tema por ID |
| delete | int id | bool | Elimina tema |

### 🏠 LandingRepository

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| findByUser | int userId | Collection | Landings del usuario |
| findBySlug | string slug | Landing o null | Busca landing por slug |
| create | array datos | Landing | Crea nueva landing |
| update | int id, array datos | Landing | Actualiza landing |
| findById | int id | Landing o null | Busca landing por ID |
| delete | int id | bool | Elimina landing |
| attachMedia | int landingId, int mediaId, int order | void | Vincula media con orden |
| detachMedia | int landingId, int mediaId | void | Desvincula media |
| updateMediaOrder | int landingId, array orders | void | Actualiza orden de media |

### 📁 MediaRepository

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| findUserAccessible | int userId | Collection | Media accesible por usuario |
| create | array datos | Media | Crea nuevo media |
| findById | int id | Media o null | Busca media por ID |
| delete | int id | bool | Elimina media |
| isLinkedToAnyEntity | int mediaId | bool | Verifica si está en uso |
| attachToLanding | int landingId, int mediaId, int order | void | Vincula media a landing |
| detachFromLanding | int landingId, int mediaId | void | Desvincula media de landing |
| attachToInvitation | int invitationId, int mediaId | void | Vincula media a invitation |
| detachFromInvitation | int invitationId, int mediaId | void | Desvincula media de invitation |
| updateLandingMediaOrder | int landingId, array orders | void | Actualiza orden media landing |
| countMediaByEntity | string entityType, int entityId | int | Cuenta media vinculado a entidad |

### 💌 InvitationRepository

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| findByUser | int userId | Collection | Invitations del usuario |
| findBySlug | string slug | Invitation o null | Busca invitation por slug |
| create | array datos | Invitation | Crea nueva invitation |
| update | int id, array datos | Invitation | Actualiza invitation |
| findById | int id | Invitation o null | Busca invitation por ID |
| attachMedia | int invitationId, int mediaId | void | Vincula media |
| detachMedia | int invitationId, int mediaId | void | Desvincula media |

---

## Métodos de Controllers

### 🔐 AuthController

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| login | LoginRequest | JSON token+user | Autentica y genera token |
| register | RegisterRequest | JSON user+token | Registra usuario |
| logout | Request autenticado | JSON success | Revoca tokens |
| user | Request autenticado | JSON user | Datos usuario actual |

### 🎨 ThemeController

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| index | Request autenticado | JSON themes | Lista temas disponibles |
| store | StoreThemeRequest | JSON theme | Crea tema usuario |
| show | Request + id | JSON theme | Detalles tema específico |
| update | UpdateThemeRequest + id | JSON theme | Actualiza tema |
| destroy | Request + id | JSON success | Elimina tema |

### 🏠 LandingController

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| index | Request autenticado | JSON landings | Lista landings usuario (Inertia friendly) |
| store | StoreLandingRequest | JSON landing | Crea landing con slug |
| show | Request + id/slug (PÚBLICO) | JSON landing | Detalles landing con media y tema |
| update | UpdateLandingRequest + id | JSON landing | Actualiza landing |
| destroy | Request + id | JSON success | Elimina landing |

### 🏠 LandingMediaController

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| store | AttachMediaRequest + landingId | JSON success | Vincula media a landing |
| destroy | Request + landingId + mediaId | JSON success | Desvincula media |
| reorder | ReorderMediaRequest + landingId | JSON success | Reordena media |

### �� MediaController

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| index | Request autenticado | JSON media | Lista media usuario |
| store | StoreMediaRequest | JSON media | Sube archivo (Local/S3 auto) |
| destroy | Request + id | JSON success | Elimina media |

### 💌 InvitationController

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| index | Request autenticado | JSON invitations | Lista invitations usuario |
| store | StoreInvitationRequest | JSON invitation | Crea invitation con título y tema |
| show | Request + id/slug (PÚBLICO) | JSON invitation | Detalles invitation con media y tema |
| update | UpdateInvitationRequest + id | JSON invitation | Actualiza invitation (incluyendo tema) |
| destroy | Request + id | JSON success | Soft delete invitation |

### 💌 InvitationMediaController

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| store | AttachInvitationMediaRequest + invitationId | JSON success | Vincula media |
| destroy | Request + invitationId + mediaId | JSON success | Desvincula media |

### 🌐 PublicLandingController

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| show | Request + slug | JSON landing | Landing público optimizado |

### 🌐 PublicInvitationController

| Método | Recibe | Devuelve | Qué Hace |
|--------|--------|----------|----------|
| show | Request + slug | JSON invitation | Invitation público con tema y media |

---

## Flujos de Datos

### 🔄 Flujo Típico de Creación

1. Request HTTP llega al Controller
2. FormRequest valida datos de entrada
3. Controller llama al Service con datos validados
4. Service aplica lógica de negocio (slugs, defaults)
5. Service llama al Repository para persistir
6. Repository ejecuta query y retorna modelo
7. Service retorna resultado al Controller
8. Controller devuelve JSON response

### 📊 Filtrado por Usuario

Principio: Todos los endpoints index filtran por usuario autenticado

- Landings index: Solo landings donde user_id = auth.id
- Invitations index: Solo invitations donde user_id = auth.id  
- Media index: Solo media vinculado a landings/invitations del usuario
- Themes index: Temas sistema + temas donde user_id = auth.id

### 🔗 Gestión de Media Pivot

Landing ↔ Media:
- Tabla pivot: landing_media (landing_id, media_id, sort_order)
- Operaciones: attach, detach, reorder
- Límite: máximo 20 media por landing

Invitation ↔ Media:
- Tabla pivot: invitation_media (invitation_id, media_id)
- Operaciones: attach, detach
- Límite: máximo 20 media por invitation

Theme ↔ Media (Background Images):
- Relación directa: themes.bg_image_url apunta a media.url
- Campo adicional en themes: bg_image_media_id (opcional, para referencia)
- Operaciones: upload, replace, delete
- Límite: 1 imagen de fondo por tema
- Nota: Al eliminar tema, se devuelve boolean pero el service gestiona el storage.

---

## ✅ Criterios de Completitud

### Por cada entidad debe tener:

Controllers:
- Métodos CRUD que filtren por usuario apropiadamente
- Manejo de respuestas Inertia o JSON según el caso
- Eager loading de temas y media para evitar N+1

FormRequests:
- Validaciones completas (especialmente theme_id en invitaciones)
- Autorización basada en ownership

Services:
- Lógica de slugs y gestión de storage
- Orchestration entre repositories

Repositories:
- Consultas optimizadas con relaciones precargadas
