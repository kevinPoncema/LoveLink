
Documento que define las entidades, relaciones, y conceptos fundamentales del dominio de negocio de UsPage, asegurando una arquitectura escalable y mantenible.

---

## 📋 Tabla de Contenidos

1. [Descripción del Dominio](#descripción-del-dominio)
2. [Análisis de Casos de Uso](#análisis-de-casos-de-uso)
3. [Entidades del Dominio](#entidades-del-dominio)
4. [Diagrama de Entidad-Relación (3NF)](#diagrama-de-entidad-relación-3nf)
5. [Diagrama de Clases](#diagrama-de-clases)
6. [Relaciones y Restricciones](#relaciones-y-restricciones)
7. [Reglas de Negocio](#reglas-de-negocio)
8. [Patrón Repository y Service](#patrón-repository-y-service)

---

## Descripción del Dominio

### Contexto

UsPage es una plataforma de conmemoración digital que permite a usuarios crear landing pages personalizadas para parejas. El dominio central gira en torno a:

- **Usuarios:** Propietarios de landings; autenticación y autorización.
- **Landing Pages:** Contenedores de información conmemorativa; slug único como punto de acceso público.
- **Contenido:** Nombres de pareja, fecha de aniversario, momentos de vida, galería multimedia.
- **Temas Visuales:** Estilos predefinidos que personalizan la presentación visual.

### Actores Principales

```
┌─────────────────────────────────────────┐
│  Usuario Propietario (Autenticado)      │
│  - Crear landing                        │
│  - Editar contenido                     │
│  - Gestionar galería                    │
│  - Seleccionar tema                     │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Visitante (Anónimo)                    │
│  - Ver landing pública                  │
│  - Compartir en redes sociales          │
│  - No puede editar                      │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Administrador (Futuro)                 │
│  - Gestionar temas                      │
│  - Moderar contenido                    │
│  - Analítica                            │
└─────────────────────────────────────────┘
```

---

## Análisis de Casos de Uso

### Diagrama de Casos de Uso (UML)

```
@startuml

left to right direction

actor Usuario as U
actor Visitante as V
actor Admin as A

rectangle "UsPage" {
  usecase "Autenticarse" as UC1
  usecase "Crear Landing" as UC2
  usecase "Editar Landing" as UC3
  usecase "Gestionar Galería" as UC4
  usecase "Seleccionar Tema" as UC5
  usecase "Ver Landing Pública" as UC6
  usecase "Compartir en Redes" as UC7
  usecase "Gestionar Temas" as UC8
  usecase "Validar Slug" as UC9

  U --> UC1 : autenticarse
  U --> UC2 : crear
  U --> UC3 : editar
  U --> UC4 : gestionar multimedia
  U --> UC5 : personalizar
  
  V --> UC6 : ver
  V --> UC7 : compartir
  
  UC2 .> UC1 : <<include>>
  UC3 .> UC1 : <<include>>
  UC4 .> UC1 : <<include>>
  UC5 .> UC1 : <<include>>
  UC6 .> UC9 : <<include>>
  
  UC2 .> UC9 : <<extend>>
  UC3 .> UC9 : <<extend>>
  
  A --> UC8 : administrar
}

@enduml
```

---

## Entidades del Dominio

### 1. User (Usuario)

Representa un usuario registrado en el sistema con capacidad de crear y gestionar landing pages.

```php
/**
 * Entidad: User
 * 
 * Responsabilidades:
 * - Almacenar datos de autenticación
 * - Mantener relación 1:1 con Landing
 * - Gestionar sesiones y tokens
 */
class User {
    public int $id;                      // PK
    public string $email;                // UNIQUE
    public string $password;             // HASHED (bcrypt)
    public string $name;                 // Nombre completo
    public ?string $profile_image_url;   // Foto de perfil (nullable)
    public bool $is_active;              // Soft delete lógico
    public Carbon $created_at;           // Timestamp de creación
    public Carbon $updated_at;           // Timestamp de actualización
    public Carbon $deleted_at;           // Soft delete (nullable)
}
```

**Restricciones:**
- Email única a nivel de base de datos
- Contraseña mínimo 8 caracteres, con complejidad
- Campo `is_active` para control de estado

---

### 2. Landing (Página Conmemorativa)

Entidad central que representa la landing page personalizada de un usuario.

```php
/**
 * Entidad: Landing
 * 
 * Responsabilidades:
 * - Almacenar metadatos de la página
 * - Mantener relación 1:N con Media
 * - Mantener relación 1:N con Moments
 * - Generar y mantener slug único
 */
class Landing {
    public int $id;                          // PK
    public int $user_id;                     // FK -> Users
    public int $theme_id;                    // FK -> Themes
    public string $slug;                     // UNIQUE, 3-50 caracteres
    public string $couple_names;             // Ej: "Juan & María"
    public Date $anniversary_date;           // Fecha de aniversario
    public string $bio_text;                 // Descripción/mensaje principal
    public ?string $music_url;               // URL de música de Spotify (nullable)
    public bool $is_published;               // Publicada o borrador
    public int $view_count;                  // Contador de visitas
    public Carbon $created_at;
    public Carbon $updated_at;
    public Carbon $deleted_at;               // Soft delete
}
```

**Restricciones:**
- Relación 1:1 con User (un usuario = una landing)
- Slug único y normalizado (lowercase, sin acentos)
- Validación de slug: `^[a-z0-9\-]{3,50}$`
- Fecha de aniversario no puede ser futura
- Campo `is_published` controla visibilidad pública

---

### 3. Theme (Tema Visual)

Catálogo de temas predefinidos que determinan la presentación visual de la landing.

```php
/**
 * Entidad: Theme
 * 
 * Responsabilidades:
 * - Definir estilos CSS y configuración
 * - Servir como catálogo reutilizable
 * - Permitir extensión futura con temas custom
 */
class Theme {
    public int $id;                      // PK
    public string $name;                 // Ej: "Elegante Dorado"
    public string $slug;                 // Identificador único
    public string $css_class;            // Clase CSS principal
    public string $description;          // Descripción del tema
    public array $config;                // JSON con paleta, tipografía, etc.
    public string $preview_image_url;    // Imagen preview
    public bool $is_active;              // Disponible para uso
    public int $sort_order;              // Orden en listado
    public Carbon $created_at;
}
```

**Ejemplo de config JSON:**

```json
{
  "primary_color": "#FFD700",
  "secondary_color": "#FFF",
  "accent_color": "#FF69B4",
  "font_family": "Playfair Display",
  "font_secondary": "Open Sans",
  "gradient": "linear-gradient(135deg, #FFD700, #FF69B4)"
}
```

---

### 4. Media (Multimedia)

Representa imágenes y videos asociados a una landing page, normalizados en tabla separada (3NF).

```php
/**
 * Entidad: Media
 * 
 * Responsabilidades:
 * - Almacenar referencias a archivos
 * - Mantener orden de galería
 * - Permitir soft delete sin perder datos
 */
class Media {
    public int $id;                      // PK
    public int $landing_id;              // FK -> Landings
    public string $file_path;            // URL o ruta relativa
    public string $original_filename;    // Nombre original del archivo
    public string $type;                 // 'image' | 'video'
    public string $mime_type;            // Ej: 'image/jpeg'
    public int $file_size;               // Bytes
    public ?string $thumbnail_path;      // URL del thumbnail (nullable)
    public int $sort_order;              // Orden en galería
    public bool $is_active;              // Soft delete lógico
    public Carbon $created_at;
}
```

**Restricciones:**
- Máximo 50 media por landing
- Tipos permitidos: JPG, PNG, WebP (imágenes); MP4, WebM (video)
- Tamaño máximo: 5 MB por archivo
- Validación MIME type

---

### 5. Moment (Momento/Evento)

Representa hitos o eventos significativos en la cronología de la pareja.

```php
/**
 * Entidad: Moment
 * 
 * Responsabilidades:
 * - Almacenar cronología de eventos
 * - Mantener orden temporal
 * - Permitir edición y eliminación
 */
class Moment {
    public int $id;                      // PK
    public int $landing_id;              // FK -> Landings
    public string $title;                // Ej: "Primer beso"
    public ?string $description;         // Descripción del evento
    public Date $moment_date;            // Fecha del evento
    public ?string $image_url;           // Imagen asociada (nullable)
    public int $sort_order;              // Orden en cronología
    public bool $is_active;              // Soft delete
    public Carbon $created_at;
    public Carbon $updated_at;
}
```

---

## Diagrama de Entidad-Relación (3NF)

### Estructura Normalizada

```
┌─────────────────────────────────┐
│         USERS                   │
├─────────────────────────────────┤
│ PK: id (INT)                    │
│    email (VARCHAR, UNIQUE)      │
│    password (VARCHAR)           │
│    name (VARCHAR)               │
│    profile_image_url (VARCHAR)  │
│    is_active (BOOLEAN)          │
│    created_at (TIMESTAMP)       │
│    updated_at (TIMESTAMP)       │
│    deleted_at (TIMESTAMP)       │
└─────────────────────────────────┘
         │
         │ 1:1 (user_id FK UNIQUE)
         │
┌─────────────────────────────────────────────┐
│         LANDINGS                            │
├─────────────────────────────────────────────┤
│ PK: id (INT)                                │
│ FK: user_id (INT, UNIQUE)                   │
│ FK: theme_id (INT)                          │
│    slug (VARCHAR, UNIQUE)                   │
│    couple_names (VARCHAR)                   │
│    anniversary_date (DATE)                  │
│    bio_text (LONGTEXT)                      │
│    music_url (VARCHAR)                      │
│    is_published (BOOLEAN)                   │
│    view_count (INT)                         │
│    created_at (TIMESTAMP)                   │
│    updated_at (TIMESTAMP)                   │
│    deleted_at (TIMESTAMP)                   │
└─────────────────────────────────────────────┘
         │
         │ 1:N (landing_id FK)
         │
    ┌────┴────────────────┐
    │                     │
┌──────────────────┐  ┌──────────────────────┐
│    MEDIA         │  │    MOMENTS           │
├──────────────────┤  ├──────────────────────┤
│ PK: id (INT)     │  │ PK: id (INT)         │
│ FK: landing_id   │  │ FK: landing_id       │
│    file_path     │  │    title             │
│    type          │  │    description       │
│    sort_order    │  │    moment_date       │
│    is_active     │  │    image_url         │
│    created_at    │  │    sort_order        │
└──────────────────┘  │    is_active         │
                      │    created_at        │
                      └──────────────────────┘

┌─────────────────────────────┐
│       THEMES                │
├─────────────────────────────┤
│ PK: id (INT)                │
│    name (VARCHAR)           │
│    slug (VARCHAR, UNIQUE)   │
│    css_class (VARCHAR)      │
│    description (TEXT)       │
│    config (JSON)            │
│    preview_image_url        │
│    is_active (BOOLEAN)      │
│    sort_order (INT)         │
│    created_at (TIMESTAMP)   │
└─────────────────────────────┘
         │
         │ M:1 (theme_id FK en Landings)
         │
         └──> Landings
```

### Cumplimiento de 3NF

✅ **Primera Forma Normal (1NF):** Todos los valores son atómicos; no hay grupos repetitivos.

✅ **Segunda Forma Normal (2NF):** No hay dependencias parciales; cada campo depende completamente de la PK.

✅ **Tercera Forma Normal (3NF):** No hay dependencias transitivas; `Themes` está separado de `Landings` para evitar redundancia.

**Justificación:**
- `Themes` en tabla separada → Evita duplicación de config JSON en cada landing
- `Media` en tabla separada → Permite gestión independiente de multimedia
- `Moments` en tabla separada → Permite escalabilidad de cronología
- Índices en FK y campos de búsqueda → Optimización de queries

---

## Diagrama de Clases

### Estructura de Modelos Eloquent

```
@startuml

package "Entidades (Models)" {
    class User {
        - id : int
        - email : string
        - password : string
        - name : string
        --
        + landing() : HasOne
        + isActiveAndNotDeleted() : bool
    }
    
    class Landing {
        - id : int
        - user_id : int
        - theme_id : int
        - slug : string
        - couple_names : string
        - anniversary_date : date
        - bio_text : string
        - is_published : bool
        --
        + user() : BelongsTo
        + theme() : BelongsTo
        + media() : HasMany
        + moments() : HasMany
        + getYearsTogetherAttribute() : int
        + incrementViewCount() : void
    }
    
    class Theme {
        - id : int
        - name : string
        - slug : string
        - css_class : string
        - config : array
        --
        + landings() : HasMany
        + getConfigAttribute() : array
    }
    
    class Media {
        - id : int
        - landing_id : int
        - file_path : string
        - type : enum
        - sort_order : int
        --
        + landing() : BelongsTo
    }
    
    class Moment {
        - id : int
        - landing_id : int
        - title : string
        - moment_date : date
        - sort_order : int
        --
        + landing() : BelongsTo
        + getDaysPassedAttribute() : int
    }
}

package "Repositorios (Data Access)" {
    interface LandingRepositoryInterface {
        + findBySlug(slug : string) : ?Landing
        + findByUser(userId : int) : ?Landing
        + findPublished(slug : string) : ?Landing
        + create(data : array) : Landing
        + update(id : int, data : array) : Landing
        + delete(id : int) : void
    }
    
    class EloquentLandingRepository {
        - landing : Landing
        --
        + findBySlug(slug : string) : ?Landing
        + findByUser(userId : int) : ?Landing
        + findPublished(slug : string) : ?Landing
        + create(data : array) : Landing
        + update(id : int, data : array) : Landing
        + delete(id : int) : void
    }
    
    interface MediaRepositoryInterface {
        + findByLanding(landingId : int) : Collection
        + create(landingId : int, data : array) : Media
        + reorder(landingId : int, order : array) : void
        + delete(id : int) : void
    }
    
    class EloquentMediaRepository {
        - media : Media
        --
        + findByLanding(landingId : int) : Collection
        + create(landingId : int, data : array) : Media
        + reorder(landingId : int, order : array) : void
        + delete(id : int) : void
    }
}

package "Servicios (Business Logic)" {
    class LandingService {
        - landingRepo : LandingRepositoryInterface
        - mediaService : MediaService
        --
        + createNewLanding(user : User, data : array) : Landing
        + updateLanding(landingId : int, data : array) : Landing
        + publishLanding(landingId : int) : void
        + getPublicLanding(slug : string) : ?Landing
        + generateSlug(coupleNames : string) : string
        + isSlugAvailable(slug : string) : bool
    }
    
    class MediaService {
        - mediaRepo : MediaRepositoryInterface
        - storageService : StorageService
        --
        + uploadImage(landing : Landing, file : File) : Media
        + deleteMedia(mediaId : int) : void
        + reorderGallery(landingId : int, order : array) : void
        + generateThumbnail(filePath : string) : string
    }
    
    class SlugService {
        --
        + generate(text : string) : string
        + validate(slug : string) : bool
        + sanitize(slug : string) : string
    }
}

package "Controladores (Presentación)" {
    class LandingController {
        - landingService : LandingService
        --
        + show(slug : string) : Response
        + edit(id : int) : Response
        + update(id : int, request : UpdateLandingRequest) : Response
    }
}

' Relaciones
User "1" -- "1" Landing
Landing "1" -- "*" Media
Landing "1" -- "*" Moment
Theme "1" -- "*" Landing

LandingService --> LandingRepositoryInterface
EloquentLandingRepository ..|> LandingRepositoryInterface
LandingService --> MediaService
MediaService --> MediaRepositoryInterface
EloquentMediaRepository ..|> MediaRepositoryInterface
LandingService --> SlugService

LandingController --> LandingService

@enduml
```

---

## Relaciones y Restricciones

### 1. User ↔ Landing (1:1)

```php
// En User.php
public function landing(): HasOne
{
    return $this->hasOne(Landing::class);
}

// En Landing.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

**Restricciones:**
- `user_id` UNIQUE en tabla `landings` → Un usuario = una landing
- ON DELETE CASCADE → Al borrar usuario, se borra landing

---

### 2. Landing ↔ Theme (M:1)

```php
// En Landing.php
public function theme(): BelongsTo
{
    return $this->belongsTo(Theme::class);
}

// En Theme.php
public function landings(): HasMany
{
    return $this->hasMany(Landing::class);
}
```

**Restricciones:**
- `theme_id` NOT NULL → Todo landing debe tener tema
- ON DELETE RESTRICT → No se puede borrar tema si hay landings usándolo

---

### 3. Landing ↔ Media (1:N)

```php
// En Landing.php
public function media(): HasMany
{
    return $this->hasMany(Media::class);
}

// En Media.php
public function landing(): BelongsTo
{
    return $this->belongsTo(Landing::class);
}
```

**Restricciones:**
- Máximo 50 media por landing (validación en servicio)
- ON DELETE CASCADE → Al borrar landing, se eliminan medias

---

### 4. Landing ↔ Moment (1:N)

```php
// En Landing.php
public function moments(): HasMany
{
    return $this->hasMany(Moment::class)->orderBy('moment_date', 'asc');
}

// En Moment.php
public function landing(): BelongsTo
{
    return $this->belongsTo(Landing::class);
}
```

**Restricciones:**
- Ordenamiento automático por fecha
- ON DELETE CASCADE → Al borrar landing, se eliminan momentos

---

## Reglas de Negocio

### RN1: Validación de Slug

```
REGLA: Slug debe ser único, normalizado y validar contra patrones peligrosos.

Algoritmo:
1. Convertir a minúsculas
2. Reemplazar acentos (á→a, é→e, ñ→n)
3. Reemplazar espacios con guiones
4. Eliminar caracteres no alfanuméricos (excepto guiones)
5. Validar patrón: ^[a-z0-9\-]{3,50}$
6. Verificar unicidad en BD
7. Si existe, generar variante: slug + número incremental

Ejemplo:
- Entrada: "Juan & María"
- Salida: "juan-maria"
- Si existe, generar: "juan-maria-1", "juan-maria-2", etc.
```

### RN2: Cálculo de Años Juntos

```
REGLA: Calcular automáticamente años de aniversario desde fecha dada.

Algoritmo:
1. Obtener fecha de aniversario del landing
2. Comparar con fecha actual
3. Si fecha actual < aniversario en el año, restar 1
4. Retornar: date.diff(today).years

Ejemplo:
- Aniversario: 15/02/2020
- Hoy: 26/01/2026
- Años: 5 (porque aún no llega 15/02/2026)
```

### RN3: Soft Delete en Cascada

```
REGLA: Al eliminar una entidad padre, marcar hijos como inactivos sin borrarlos.

Implementación:
- User soft delete → Landing is_published = FALSE (no ON DELETE CASCADE)
- Landing soft delete → Media is_active = FALSE, Moment is_active = FALSE
- Esto preserva datos para auditoría y recuperación
```

### RN4: Autorización de Edición

```
REGLA: Solo el propietario de una landing puede editarla.

Validación:
- Landing->user_id == Auth::user()->id
- Implementar Policy: LandingPolicy@update
- Usar middleware: middleware('can:update,landing')
```

### RN5: Publicación y Visibilidad

```
REGLA: Landings no publicadas (is_published = FALSE) no son accesibles en ruta pública.

Flujo:
1. Usuario crea landing en borrador (is_published = FALSE)
2. Usuario completa contenido
3. Usuario clica "Publicar" → is_published = TRUE
4. Landing es accesible en /p/{slug}
5. Si usuario "Despublica" → is_published = FALSE → 404 para visitantes
```

### RN6: Límite de Multimedia

```
REGLA: Máximo 50 imágenes por landing; máximo 5 MB por archivo.

Validación:
- En MediaService::uploadImage()
- Contar media activas de landing
- Si count >= 50, rechazar nueva carga
- Validar tamaño: file.size <= 5 * 1024 * 1024
```

---

## Patrón Repository y Service

### Objetivo

Aislar la lógica de negocio de la persistencia, mejorando testabilidad, mantenibilidad y escalabilidad.

### Flujo de Datos

```
HTTP Request
    ↓
Controller (orquesta)
    ↓
Service (lógica de negocio)
    ↓
Repository (interfaz de acceso a datos)
    ↓
Eloquent Model (mapeo relacional)
    ↓
MySQL/MariaDB
```

### Ejemplo: Crear una Landing

```php
// 1. Route (routes/web.php)
Route::post('/landings', [LandingController::class, 'store'])->middleware('auth');

// 2. Controller (HTTP/Controllers/LandingController.php)
class LandingController {
    public function __construct(
        private LandingService $landingService
    ) {}
    
    public function store(StoreLandingRequest $request): RedirectResponse
    {
        $landing = $this->landingService->createNewLanding(
            auth()->user(),
            $request->validated()
        );
        
        return redirect("/landings/{$landing->id}/edit");
    }
}

// 3. Service (Services/LandingService.php)
class LandingService {
    public function __construct(
        private LandingRepositoryInterface $landingRepository,
        private SlugService $slugService
    ) {}
    
    public function createNewLanding(User $user, array $data): Landing
    {
        // Generar slug único
        $slug = $this->slugService->generate($data['couple_names']);
        
        // Validar que usuario no tenga landing
        if ($user->landing) {
            throw new UserAlreadyHasLandingException();
        }
        
        // Crear via repositorio
        return $this->landingRepository->create([
            'user_id' => $user->id,
            'theme_id' => $data['theme_id'],
            'slug' => $slug,
            'couple_names' => $data['couple_names'],
            'anniversary_date' => $data['anniversary_date'],
            'bio_text' => $data['bio_text'] ?? '',
        ]);
    }
}

// 4. Repository Interface (Repositories/Interfaces/LandingRepositoryInterface.php)
interface LandingRepositoryInterface {
    public function findBySlug(string $slug): ?Landing;
    public function findByUser(User $user): ?Landing;
    public function create(array $data): Landing;
    public function update(int $id, array $data): Landing;
    public function delete(int $id): void;
}

// 5. Repository Implementation (Repositories/Eloquent/EloquentLandingRepository.php)
class EloquentLandingRepository implements LandingRepositoryInterface {
    public function __construct(private Landing $model) {}
    
    public function findBySlug(string $slug): ?Landing
    {
        return $this->model->where('slug', $slug)
            ->where('is_published', true)
            ->first();
    }
    
    public function create(array $data): Landing
    {
        return $this->model->create($data);
    }
    
    public function update(int $id, array $data): Landing
    {
        $landing = $this->model->findOrFail($id);
        $landing->update($data);
        return $landing;
    }
}

// 6. Service Provider (Providers/AppServiceProvider.php)
class AppServiceProvider {
    public function register(): void
    {
        $this->app->bind(
            LandingRepositoryInterface::class,
            EloquentLandingRepository::class
        );
    }
}
```

### Ventajas de Este Patrón

| Ventaja | Beneficio |
|---------|-----------|
| **Testabilidad** | Mockear repositorios en tests unitarios; lógica sin BD |
| **Mantenibilidad** | Cambiar BD (Eloquent → Query API) sin tocar servicios |
| **Escalabilidad** | Fácil agregar caché, eventos, colas |
| **Legibilidad** | Servicios expresan intención; código auto-documentado |
| **Reutilización** | Servicios usables desde controladores, comandos, jobs |

---

## Índices y Optimización

### Índices Recomendados

```sql
-- Búsqueda por slug (acceso público)
CREATE INDEX idx_landings_slug ON landings(slug);

-- Búsqueda de landing por usuario (dashboard)
CREATE INDEX idx_landings_user_id ON landings(user_id);

-- Filtrado de landing publicadas
CREATE INDEX idx_landings_published ON landings(is_published);

-- Ordenamiento de media
CREATE INDEX idx_media_landing_id_order ON media(landing_id, sort_order);

-- Ordenamiento de momentos
CREATE INDEX idx_moments_landing_id_order ON moments(landing_id, moment_date);

-- Búsqueda de usuario por email
CREATE INDEX idx_users_email ON users(email);
```

### Consultas Optimizadas (Eager Loading)

```php
// ❌ MAL: N+1 problem
$landing = Landing::find(1);
foreach ($landing->media as $media) {
    echo $media->file_path; // Query adicional por cada media
}

// ✅ BIEN: Eager loading
$landing = Landing::with('media', 'moments')->find(1);
foreach ($landing->media as $media) {
    echo $media->file_path; // Sin queries adicionales
}

// ✅ MÁS BIEN: En repositorio
public function findWithRelations(int $id): Landing
{
    return $this->model->with([
        'user',
        'theme',
        'media' => fn($q) => $q->where('is_active', true)->orderBy('sort_order'),
        'moments' => fn($q) => $q->where('is_active', true)->orderBy('moment_date'),
    ])->findOrFail($id);
}
```

---

**Versión:** 1.0  
**Última actualización:** Enero 2026  
**Autor:** Kevin (Equipo de Desarrollo)

