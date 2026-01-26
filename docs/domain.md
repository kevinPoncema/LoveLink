# Modelo de Dominio - UsPage

Documento que define las entidades, relaciones y conceptos fundamentales del proyecto UsPage.

---

## 📋 Tabla de Contenidos

1. [Descripción del Dominio](#descripción-del-dominio)
2. [Entidades](#entidades)
3. [Diagrama Entidad-Relación (ER)](#diagrama-entidad-relación)
4. [Relaciones](#relaciones)
5. [Reglas de Negocio](#reglas-de-negocio)

> **Nota:** Para casos de uso ver [use-cases.md](use-cases.md) y diagramas de clases en [class-diagram.md](class-diagram.md)

---

## Descripción del Dominio

UsPage es una plataforma que permite a usuarios autenticados crear landing pages conmemorativas personalizadas para parejas.

**Conceptos clave:**

- **Usuario:** Registra y autentica; propietario de una landing
- **Landing Page:** Página conmemorativa única por usuario, con slug público
- **Tema:** Estilos visuales personalizables (colores, fondo)
- **Media:** Imágenes asociadas a la landing
- **Slug:** Identificador único y amigable para URL pública

---

## Entidades

### User

Representa un usuario registrado en el sistema.

| Campo | Tipo | Restricción |
|-------|------|------------|
| `id` | INT | PK, AUTO_INCREMENT |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL |
| `password` | VARCHAR(255) | Hashed (bcrypt), NOT NULL |
| `name` | VARCHAR(255) | Nullable |
| `created_at` | TIMESTAMP | Automático |
| `updated_at` | TIMESTAMP | Automático |
| `deleted_at` | TIMESTAMP | Soft delete (nullable) |

**Restricciones:**
- Email único a nivel de BD
- Contraseña mínimo 8 caracteres

---

### Landing

Página conmemorativa asociada a un usuario.

| Campo | Tipo | Restricción |
|-------|------|------------|
| `id` | INT | PK, AUTO_INCREMENT |
| `user_id` | INT | FK → Users (NOT NULL) |
| `theme_id` | INT | FK → Themes (NOT NULL) |
| `slug` | VARCHAR(50) | UNIQUE, NOT NULL |
| `couple_names` | VARCHAR(200) | NOT NULL |
| `anniversary_date` | DATE | NOT NULL |
| `bio_text` | LONGTEXT | Nullable |
| `is_published` | BOOLEAN | DEFAULT TRUE |
| `created_at` | TIMESTAMP | Automático |
| `updated_at` | TIMESTAMP | Automático |
| `deleted_at` | TIMESTAMP | Soft delete |

**Restricciones:**
- Slug: 3-50 caracteres, alfanuméricos + guiones, único
- `user_id` (NO UNIQUE) → Un usuario puede tener múltiples landings
- Validación de slug: no caracteres especiales

---

### Theme

Catálogo de temas visuales personalizables.

| Campo | Tipo | Restricción |
|-------|------|------------|
| `id` | INT | PK, AUTO_INCREMENT |
| `name` | VARCHAR(100) | NOT NULL |
| `slug` | VARCHAR(100) | UNIQUE |
| `description` | TEXT | Nullable |
| `primary_color` | VARCHAR(7) | Ej: #FF5733 |
| `secondary_color` | VARCHAR(7) | Ej: #FFC300 |
| `bg_color` | VARCHAR(7) | Color de fondo |
| `bg_image_url` | VARCHAR(500) | Nullable |
| `css_class` | VARCHAR(100) | Clase CSS principal |
| `is_active` | BOOLEAN | DEFAULT TRUE |
| `created_at` | TIMESTAMP | Automático |

**Ejemplo de Theme:**

```
id: 1
name: "Elegante Dorado"
slug: "elegante-dorado"
primary_color: "#FFD700"
secondary_color: "#FFF"
bg_color: "#F5F5F5"
css_class: "theme-elegant-gold"
```

---

### Media

Imágenes asociadas a una landing.

| Campo | Tipo | Restricción |
|-------|------|------------|
| `id` | INT | PK, AUTO_INCREMENT |
| `landing_id` | INT | FK → Landings |
| `file_path` | VARCHAR(500) | URL del archivo |
| `public_url` | VARCHAR(500) | URL pública (CDN/storage) |
| `type` | ENUM | 'image' (MVP) |
| `mime_type` | VARCHAR(50) | Ej: image/jpeg |
| `file_size` | INT | Bytes |
| `sort_order` | INT | Orden en galería |
| `is_active` | BOOLEAN | DEFAULT TRUE |
| `created_at` | TIMESTAMP | Automático |

**Restricciones:**
- Máximo 50 media por landing
- Tipos: JPG, PNG, WebP
- Tamaño máximo: 5 MB
- Soft delete lógico

---

### SystemControl

Entidad de configuración del sistema para controlar límites y metadatos de media.

| Campo | Tipo | Restricción |
|-------|------|------------|
| `id` | INT | PK, AUTO_INCREMENT |
| `max_images_per_landing` | INT | DEFAULT 50 |
| `max_file_size_mb` | INT | DEFAULT 5 |
| `allowed_mime` | JSON | Lista de MIME permitidos |
| `thumbnails_enabled` | BOOLEAN | DEFAULT TRUE |
| `gif_enabled` | BOOLEAN | DEFAULT FALSE (futuro) |
| `updated_at` | TIMESTAMP | Automático |

**Notas:**
- Una sola fila de configuración global (puede versionarse a futuro).
- Controla validaciones de subida y generación de thumbnails.

---

### Invitation

Página de invitación (San Valentín u otros eventos) con mensajes personalizados.

| Campo | Tipo | Restricción |
|-------|------|------------|
| `id` | INT | PK, AUTO_INCREMENT |
| `user_id` | INT | FK → Users (NOT NULL) |
| `landing_id` | INT | FK → Landings (nullable) |
| `slug` | VARCHAR(50) | UNIQUE, NOT NULL |
| `title` | VARCHAR(200) | DEFAULT '¿Quieres ser mi San Valentín?' |
| `yes_message` | VARCHAR(100) | DEFAULT 'Sí' |
| `no_messages` | JSON | Lista de mensajes negativos |
| `is_published` | BOOLEAN | DEFAULT TRUE |
| `created_at` | TIMESTAMP | Automático |
| `updated_at` | TIMESTAMP | Automático |
| `deleted_at` | TIMESTAMP | Soft delete |

**Valores por defecto de `no_messages`:**
```json
["No", "Tal vez", "No te arrepentirás", "Piénsalo mejor"]
```

**Restricciones:**
- Slug único para acceso público
- `landing_id` nullable: puede existir independiente de landing
- JSON simple para mensajes de "No" (no rompe 3NF, lista atómica)

---

### InvitationMedia

Elementos multimedia (GIFs, imágenes) para invitaciones.

| Campo | Tipo | Restricción |
|-------|------|------------|
| `id` | INT | PK, AUTO_INCREMENT |
| `invitation_id` | INT | FK → Invitations |
| `file_path` | VARCHAR(500) | Ruta del archivo |
| `public_url` | VARCHAR(500) | URL pública (CDN) |
| `type` | ENUM | 'image', 'gif' |
| `mime_type` | VARCHAR(50) | Ej: image/gif |
| `file_size` | INT | Bytes |
| `sort_order` | INT | Orden de visualización |
| `is_active` | BOOLEAN | DEFAULT TRUE |
| `created_at` | TIMESTAMP | Automático |

**Restricciones:**
- Máximo 10 elementos multimedia por invitación
- Tipos permitidos: image/gif, image/png, image/jpeg, image/webp
- Tamaño máximo: 10 MB (GIFs pueden ser más pesados)

---

## Diagrama Entidad-Relación

```
┌────────────────────────────────┐
│         USERS                  │
├────────────────────────────────┤
│ id (PK)                        │
│ email (UNIQUE)                 │
│ password                       │
│ name                           │
│ created_at, updated_at         │
│ deleted_at (soft delete)       │
└────────────────────────────────┘
           │
           │ 1:N (user_id)
           │ ON DELETE CASCADE
           │
┌────────────────────────────────────────────┐
│          LANDINGS                          │
├────────────────────────────────────────────┤
│ id (PK)                                    │
│ user_id (FK)                               │
│ theme_id (FK) ─────────────┐               │
│ slug (UNIQUE)              │               │
│ couple_names               │               │
│ anniversary_date           │               │
│ bio_text                   │               │
│ is_published               │               │
│ created_at, updated_at     │               │
│ deleted_at (soft delete)   │               │
└────────────────────────────────────────────┘
           │
           │ 1:N (landing_id)
           │
┌────────────────────────────────┐
│          MEDIA                 │
├────────────────────────────────┤
│ id (PK)                        │
│ landing_id (FK)                │
│ file_path                      │
│ public_url                     │
│ type (image)                   │
│ mime_type                      │
│ file_size                      │
│ sort_order                     │
│ is_active                      │
│ created_at                     │
└────────────────────────────────┘

┌────────────────────────────────┐
│      SYSTEM_CONTROL            │
├────────────────────────────────┤
│ id (PK)                        │
│ max_images_per_landing         │
│ max_file_size_mb               │
│ allowed_mime (JSON)            │
│ thumbnails_enabled             │
│ gif_enabled                    │
│ updated_at                     │
└────────────────────────────────┘

           M:1 ────────────┐
                           │
┌────────────────────────────────┐
│         THEMES                 │
├────────────────────────────────┤
│ id (PK)                        │
│ name                           │
│ slug (UNIQUE)                  │
│ primary_color                  │
│ secondary_color                │
│ bg_color                       │
│ bg_image_url                   │
│ css_class                      │
│ is_active                      │
│ created_at                     │
└────────────────────────────────┘

[USERS] ────────┐
                │
                │ 1:N (user_id)
                │
┌────────────────────────────────────────────┐
│          INVITATIONS                       │
├────────────────────────────────────────────┤
│ id (PK)                                    │
│ user_id (FK)                               │
│ landing_id (FK, nullable)                  │
│ slug (UNIQUE)                              │
│ title                                      │
│ yes_message                                │
│ no_messages (JSON)                         │
│ is_published                               │
│ created_at, updated_at                     │
│ deleted_at (soft delete)                   │
└────────────────────────────────────────────┘
           │
           │ 1:N (invitation_id)
           │
┌────────────────────────────────┐
│     INVITATION_MEDIA           │
├────────────────────────────────┤
│ id (PK)                        │
│ invitation_id (FK)             │
│ file_path                      │
│ public_url                     │
│ type (image/gif)               │
│ mime_type                      │
│ file_size                      │
│ sort_order                     │
│ is_active                      │
│ created_at                     │
└────────────────────────────────┘
```

**Cumplimiento de 3NF:**

✅ **1NF:** Todos los valores son atómicos
✅ **2NF:** Sin dependencias parciales
✅ **3NF:** `Themes` y `Media` separados evitan redundancia

---

## Relaciones

### User ↔ Landing (1:N)

- Un usuario puede tener múltiples landings
- Cada landing pertenece a un usuario
- ON DELETE CASCADE: Al borrar usuario, se borran todas sus landings

```php
// User.php
public function landings(): HasMany
{
    return $this->hasMany(Landing::class);
}

// Landing.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

---

### Landing ↔ Theme (M:1)

- Muchas landings pueden usar el mismo tema
- El usuario puede cambiar de tema sin perder contenido
- ON DELETE RESTRICT: No se puede borrar tema si hay landings usándolo

```php
// Landing.php
public function theme(): BelongsTo
{
    return $this->belongsTo(Theme::class);
}

// Theme.php
public function landings(): HasMany
{
    return $this->hasMany(Landing::class);
}
```

---

### Landing ↔ Media (1:N)

- Una landing tiene múltiples imágenes
- Las imágenes no existen sin landing
- ON DELETE CASCADE: Al borrar landing, se borran imágenes

```php
// Landing.php
public function media(): HasMany
{
    return $this->hasMany(Media::class)
        ->where('is_active', true)
        ->orderBy('sort_order');
}

// Media.php
public function landing(): BelongsTo
{
    return $this->belongsTo(Landing::class);
}
```

---

### User ↔ Invitation (1:N)

- Un usuario puede crear múltiples invitaciones
- Cada invitación pertenece a un usuario
- ON DELETE CASCADE: Al borrar usuario, se borran todas sus invitaciones

```php
// User.php
public function invitations(): HasMany
{
    return $this->hasMany(Invitation::class);
}

// Invitation.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

---

### Invitation ↔ InvitationMedia (1:N)

- Una invitación tiene múltiples elementos multimedia (GIFs/imágenes)
- Los elementos multimedia no existen sin invitación
- ON DELETE CASCADE: Al borrar invitación, se borran sus multimedia

```php
// Invitation.php
public function media(): HasMany
{
    return $this->hasMany(InvitationMedia::class)
        ->where('is_active', true)
        ->orderBy('sort_order');
}

// InvitationMedia.php
public function invitation(): BelongsTo
{
    return $this->belongsTo(Invitation::class);
}
```

---

### Invitation ↔ Landing (opcional, N:1)

- Una invitación puede estar vinculada a una landing (nullable)
- Permite crear invitaciones independientes o asociadas a una landing específica

```php
// Invitation.php
public function landing(): BelongsTo
{
    return $this->belongsTo(Landing::class);
}

// Landing.php
public function invitations(): HasMany
{
    return $this->hasMany(Invitation::class);
}
```

---

## Reglas de Negocio

### RN1: Generación de Slug

El slug se genera automáticamente a partir del nombre de pareja.

```
Algoritmo:
1. Convertir a minúsculas
2. Remover acentos (á→a, é→e, ñ→n)
3. Reemplazar espacios por guiones
4. Remover caracteres no alfanuméricos (excepto guiones)
5. Validar patrón: ^[a-z0-9\-]{3,50}$
6. Verificar unicidad en BD

Ejemplo:
- Entrada: "Juan & María López"
- Salida: "juan-maria-lopez"
- Si existe, generar: "juan-maria-lopez-1"
```

---

### RN2: Un Usuario Puede Tener Múltiples Landings

Cada usuario autenticado puede crear **múltiples landing pages**.

```
Implementación:
- user_id en landings NO es UNIQUE
- Cada landing tiene su propio slug único
- Un usuario puede gestionar varias landings
- Validación en LandingService::createNewLanding()
```
 no # Modelo de Dominio - UsPage

---

### RN3: Personalización de Tema

El usuario selecciona un tema base y personaliza colores/fondo.

```
Campos editables:
- primary_color (color primario)
- secondary_color (color secundario)
- bg_color (color de fondo)
- bg_image_url (imagen de fondo)

Los cambios se guardan en Landing, no en Theme
```

---

### RN4: Publicación de Landing

El usuario controla la visibilidad de su landing.

```
Estados:
- is_published = false → Solo accesible para propietario (draft)
- is_published = true → Accesible públicamente vía /p/{slug}

Ruta pública valida: is_published && exists(slug)
```

---

### RN5: Soft Delete

Landings eliminadas se marcan pero no se borran físicamente.

```
Implementación:
- Modelo Landing usa SoftDeletes trait
- Campo deleted_at NULL = activa, filled = eliminada
- Queries no devuelven landings eliminadas por defecto
- Solo el propietario puede ver su landing eliminada
```

---

### RN6: Límite de Imágenes

Máximo 50 imágenes por landing.

```
Validación en MediaService::uploadImage()
- Leer límites desde SystemControl (max_images_per_landing, max_file_size_mb, allowed_mime)
- Contar media activas: Media::where('landing_id', $id)
                             ->where('is_active', true)
                             ->count()
- Si count >= max_images_per_landing, rechazar carga
- Validar tamaño y MIME contra configuración
```

---

### RN7: Invitaciones Personalizadas

Entidad independiente para crear invitaciones (ej: San Valentín) con mensajes personalizables.

```
Características:
- Título personalizado (default: "¿Quieres ser mi San Valentín?")
- Mensaje de respuesta afirmativa (default: "Sí")
- Lista de mensajes de respuesta negativa (JSON array)
  Default: ["No", "Tal vez", "No te arrepentirás", "Piénsalo mejor"]
- Slug único para URL pública (/invitaciones/{slug})
- Multimedia independiente: GIFs e imágenes (max 10 elementos)
- Tamaño máximo GIF: 10MB
- Puede vincularse opcionalmente a una Landing (landing_id nullable)
- is_published controla visibilidad pública
- Soft delete habilitado

Validación en InvitationService::createInvitation():
- Slug único generado automáticamente
- Máximo 10 elementos multimedia por invitación
- GIFs solo permitidos si `gif_enabled` en SystemControl es true
- Validar tamaño y MIME contra configuración
```

---

## Patrón Repository

La arquitectura separa acceso a datos de lógica de negocio:

```php
// LandingRepositoryInterface
interface LandingRepositoryInterface {
    public function findBySlug(string $slug): ?Landing;
    public function findByUser(User $user): ?Landing;
    public function create(array $data): Landing;
    public function update(int $id, array $data): Landing;
    public function delete(int $id): void;
}

// EloquentLandingRepository
class EloquentLandingRepository implements LandingRepositoryInterface {
    public function __construct(private Landing $model) {}
    
    public function findBySlug(string $slug): ?Landing {
        return $this->model->where('slug', $slug)
            ->where('is_published', true)
            ->first();
    }
    // ... otros métodos
}

// LandingService
class LandingService {
    public function __construct(
        private LandingRepositoryInterface $repo,
        private SlugService $slugService
    ) {}
    
    public function createNewLanding(User $user, array $data): Landing {
        // Generar slug
        $slug = $this->slugService->generate($data['couple_names']);
        
        // Crear via repositorio
        return $this->repo->create([
            'user_id' => $user->id,
            'theme_id' => $data['theme_id'],
            'slug' => $slug,
            'couple_names' => $data['couple_names'],
            'anniversary_date' => $data['anniversary_date'],
        ]);
    }
}
```

---

**Versión:** 1.0  
**Última actualización:** Enero 2026  
**Autor:** Kevin (Equipo de Desarrollo)
