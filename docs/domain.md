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
| `created_at` | TIMESTAMP | Automático |
| `updated_at` | TIMESTAMP | Automático |

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
| `description` | TEXT | Nullable |
| `primary_color` | VARCHAR(7) | Ej: #FF5733 |
| `secondary_color` | VARCHAR(7) | Ej: #FFC300 |
| `bg_color` | VARCHAR(7) | Color de fondo |
| `bg_image_url` | VARCHAR(500) | Nullable |
| `css_class` | VARCHAR(100) | Clase CSS principal |
| `created_at` | TIMESTAMP | Automático |

**Ejemplo de Theme:**

```
id: 1
name: "Elegante Dorado"
primary_color: "#FFD700"
secondary_color: "#FFF"
bg_color: "#F5F5F5"
css_class: "theme-elegant-gold"
```

---

### Media

Archivos multimedia (imágenes, GIFs) compartidos entre landings e invitaciones.

| Campo | Tipo | Restricción |
|-------|------|------------|
| `id` | INT | PK, AUTO_INCREMENT |
| `file_path` | VARCHAR(500) | Ruta del archivo |
| `type` | ENUM | 'image', 'gif' |
| `file_size` | INT | Bytes |
| `created_at` | TIMESTAMP | Automático |

**Restricciones:**
- Máximo 20 media por landing/invitación
- Tipos: JPG, PNG, WebP, GIF
- Tamaño máximo: 10 MB
- Uso compartido mediante tablas pivot

---

### Invitation

Página de invitación (San Valentín u otros eventos) con mensajes personalizados.

| Campo | Tipo | Restricción |
|-------|------|------------|
| `id` | INT | PK, AUTO_INCREMENT |
| `user_id` | INT | FK → Users (NOT NULL) |
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
- JSON simple para mensajes de "No" (no rompe 3NF, lista atómica)

---

### LandingMedia

Tabla pivot para relacionar Media con Landing, incluyendo orden.

| Campo | Tipo | Restricción |
|-------|------|------------|
| `landing_id` | INT | FK → Landings |
| `media_id` | INT | FK → Media |
| `sort_order` | INT | Orden en galería |

**Restricciones:**
- Clave primaria compuesta: (landing_id, media_id)
- Máximo 20 media por landing

---

### InvitationMedia

Tabla pivot para relacionar Media con Invitation.

| Campo | Tipo | Restricción |
|-------|------|------------|
| `invitation_id` | INT | FK → Invitations |
| `media_id` | INT | FK → Media |

**Restricciones:**
- Clave primaria compuesta: (invitation_id, media_id)
- Máximo 20 media por invitación

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
└────────────────────────────────┘
           │
           │ 1:N (user_id)
           │
    ┌──────┴──────┐
    │             │
    ▼             ▼
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
│ created_at, updated_at     │               │
└────────────────────────────────────────────┘
           │                    │
           │ N:M via            │ M:1
           │ LANDING_MEDIA      │
           ▼                    ▼
┌────────────────────────────────┐ ┌────────────────────────────────┐
│      LANDING_MEDIA (PIVOT)     │ │         THEMES                 │
├────────────────────────────────┤ ├────────────────────────────────┤
│ landing_id (FK)                │ │ id (PK)                        │
│ media_id (FK)                  │ │ name                           │
│ sort_order                     │ │ description                    │
└────────────────────────────────┘ │ primary_color                  │
           │                       │ secondary_color                │
           │ N:1                   │ bg_color                       │
           ▼                       │ bg_image_url                   │
┌────────────────────────────────┐ │ css_class                      │
│          MEDIA                 │ │ created_at                     │
├────────────────────────────────┤ └────────────────────────────────┘
│ id (PK)                        │
│ file_path                      │          ┌────────────────────────────────────────────┐
│ type (image/gif)               │          │          INVITATIONS                       │
│ file_size                      │          ├────────────────────────────────────────────┤
│ created_at                     │          │ id (PK)                                    │
└────────────────────────────────┘          │ user_id (FK) ──────────────────────────────┤
           │                                │ slug (UNIQUE)                              │
           │ 1:N                            │ title                                      │
           ▼                                │ yes_message                                │
┌────────────────────────────────┐          │ no_messages (JSON)                         │
│   INVITATION_MEDIA (PIVOT)     │          │ is_published                               │
├────────────────────────────────┤          │ created_at, updated_at                     │
│ invitation_id (FK) ────────────┼──────────┤ deleted_at (soft delete)                   │
│ media_id (FK)                  │          └────────────────────────────────────────────┘
└────────────────────────────────┘
```

**Cumplimiento de 3NF:**

✅ **1NF:** Todos los valores son atómicos
✅ **2NF:** Sin dependencias parciales  
✅ **3NF:** `Themes` y `Media` separados evitan redundancia, tablas pivot manejan relaciones N:M

---

## Relaciones

### User ↔ Landing (1:N)

- Un usuario puede tener múltiples landings
- Cada landing pertenece a un usuario

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

### Landing ↔ Media (N:M via LandingMedia)

- Una landing puede tener múltiples media y una media puede estar en múltiples landings
- Relación controlada por tabla pivot LandingMedia con orden

```php
// Landing.php
public function media(): BelongsToMany
{
    return $this->belongsToMany(Media::class, 'landing_media')
        ->withPivot('sort_order')
        ->orderBy('sort_order');
}

// Media.php
public function landings(): BelongsToMany
{
    return $this->belongsToMany(Landing::class, 'landing_media')
        ->withPivot('sort_order');
}
```

---

### User ↔ Invitation (1:N)

- Un usuario puede crear múltiples invitaciones
- Cada invitación pertenece a un usuario

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

### Invitation ↔ Media (N:M via InvitationMedia)

- Una invitación puede tener múltiples media y una media puede estar en múltiples invitaciones
- Relación controlada por tabla pivot InvitationMedia

```php
// Invitation.php
public function media(): BelongsToMany
{
    return $this->belongsToMany(Media::class, 'invitation_media');
}

// Media.php
public function invitations(): BelongsToMany
{
    return $this->belongsToMany(Invitation::class, 'invitation_media');
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
