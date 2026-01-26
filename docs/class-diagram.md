# Diagrama de Clases - UsPage

Documento con diagramas UML de clases para la arquitectura del proyecto.

---

## 📋 Tabla de Contenidos

1. [Diagrama de Modelos Eloquent](#diagrama-de-modelos-eloquent)
2. [Diagrama de Services](#diagrama-de-services)
3. [Diagrama de Repositories](#diagrama-de-repositories)
4. [Diagrama Completo de Arquitectura](#diagrama-completo-de-arquitectura)

---

## Diagrama de Modelos Eloquent

Entidades principales del dominio.

```
@startuml

package "Models" {
  class User {
    - id: int
    - email: string
    - password: string
    - name: string
    - created_at: timestamp
    - updated_at: timestamp
    - deleted_at: timestamp (nullable)
    --
    + landings(): HasMany
    + isActive(): bool
  }

  class Landing {
    - id: int
    - user_id: int
    - theme_id: int
    - slug: string
    - couple_names: string
    - anniversary_date: date
    - bio_text: string
    - is_published: boolean
    - created_at: timestamp
    - updated_at: timestamp
    - deleted_at: timestamp (nullable)
    --
    + user(): BelongsTo
    + theme(): BelongsTo
    + media(): HasMany
    + getYearsTogetherAttribute(): int
  }

  class Theme {
    - id: int
    - name: string
    - slug: string
    - description: string
    - primary_color: string
    - secondary_color: string
    - bg_color: string
    - bg_image_url: string (nullable)
    - css_class: string
    - is_active: boolean
    - created_at: timestamp
    --
    + landings(): HasMany
  }

  class Media {
    - id: int
    - landing_id: int
    - file_path: string
    - public_url: string
    - type: enum
    - mime_type: string
    - file_size: int
    - sort_order: int
    - is_active: boolean
    - created_at: timestamp
    --
    + landing(): BelongsTo
  }

  class SystemControl {
    - id: int
    - max_images_per_landing: int
    - max_file_size_mb: int
    - allowed_mime: json
    - thumbnails_enabled: bool
    - gif_enabled: bool
    - updated_at: timestamp
  }

  class Invitation {
    - id: int
    - user_id: int
    - landing_id: int (nullable)
    - slug: string
    - title: string
    - yes_message: string
    - no_messages: json
    - is_published: boolean
    - created_at: timestamp
    - updated_at: timestamp
    - deleted_at: timestamp (nullable)
    --
    + user(): BelongsTo
    + landing(): BelongsTo (nullable)
    + media(): HasMany
    + getNoMessagesAttribute(): array
  }

  class InvitationMedia {
    - id: int
    - invitation_id: int
    - file_path: string
    - public_url: string
    - type: enum
    - mime_type: string
    - file_size: int
    - sort_order: int
    - is_active: boolean
    - created_at: timestamp
    --
    + invitation(): BelongsTo
    + isGif(): bool
  }
}

User "1" --> "*" Landing : has many
User "1" --> "*" Invitation : has many
Landing "*" --> "1" Theme : belongs to
Landing "1" --> "*" Media : has many
Landing "1" --> "*" Invitation : has many (optional)
Invitation "1" --> "*" InvitationMedia : has many
SystemControl ..> Media : config limits
SystemControl ..> InvitationMedia : config limits

@enduml
```

---

## Diagrama de Services

Capa de lógica de negocio.

```
@startuml

package "Services" {
  class LandingService {
    - landingRepo: LandingRepositoryInterface
    - mediaService: MediaService
    - slugService: SlugService
    --
    + createNewLanding(user, data): Landing
    + updateLanding(id, data): Landing
    + publishLanding(id): void
    + unpublishLanding(id): void
    + getPublicLanding(slug): ?Landing
    + deleteLanding(id): void
  }

  class MediaService {
    - mediaRepo: MediaRepositoryInterface
    - storageService: StorageService
    --
    + uploadImage(landing, file): Media
    + deleteMedia(id): void
    + reorderGallery(landingId, order): void
    + validateFile(file): bool
    + checkLimit(landingId): bool
  }

  class SlugService {
    --
    + generate(text): string
    + validate(slug): bool
    + sanitize(text): string
    + isUnique(slug): bool
  }

  class ThemeService {
    - themeRepo: ThemeRepositoryInterface
    --
    + getActiveThemes(): Collection
    + getThemeById(id): Theme
    + applyThemeToLanding(landing, themeId): void
  }

  class InvitationService {
    - invitationRepo: InvitationRepositoryInterface
    - invitationMediaService: InvitationMediaService
    - slugService: SlugService
    --
    + createInvitation(user, data): Invitation
    + updateInvitation(id, data): Invitation
    + publishInvitation(id): void
    + unpublishInvitation(id): void
    + getPublicInvitation(slug): ?Invitation
    + deleteInvitation(id): void
    + linkToLanding(invitationId, landingId): void
  }

  class InvitationMediaService {
    - invitationMediaRepo: InvitationMediaRepositoryInterface
    - storageService: StorageService
    --
    + uploadMedia(invitation, file): InvitationMedia
    + deleteMedia(id): void
    + reorderMedia(invitationId, order): void
    + validateFile(file): bool
    + checkLimit(invitationId): bool
    + isGifAllowed(): bool
  }
}

LandingService --> MediaService : uses
LandingService --> SlugService : uses
LandingService --> ThemeService : uses
InvitationService --> InvitationMediaService : uses
InvitationService --> SlugService : uses

@enduml
```

---

## Diagrama de Repositories

Capa de acceso a datos con patrón Repository.

```
@startuml

package "Repositories" {
  package "Interfaces" {
    interface LandingRepositoryInterface {
      + findBySlug(slug): ?Landing
      + findById(id): ?Landing
      + findByUser(user): Collection
      + findPublished(slug): ?Landing
      + create(data): Landing
      + update(id, data): Landing
      + delete(id): void
      + count(userId): int
    }

    interface MediaRepositoryInterface {
      + findByLanding(landingId): Collection
      + create(landingId, data): Media
      + update(id, data): Media
      + delete(id): void
      + reorder(landingId, order): void
      + count(landingId): int
    }

    interface ThemeRepositoryInterface {
      + findById(id): ?Theme
      + findActive(): Collection
      + findBySlug(slug): ?Theme
      + create(data): Theme
      + update(id, data): Theme
    }

    interface UserRepositoryInterface {
      + findByEmail(email): ?User
      + findById(id): ?User
      + create(data): User
      + update(id, data): User
      + delete(id): void
    }

    interface InvitationRepositoryInterface {
      + findBySlug(slug): ?Invitation
      + findById(id): ?Invitation
      + findByUser(user): Collection
      + findPublished(slug): ?Invitation
      + findByLanding(landingId): Collection
      + create(data): Invitation
      + update(id, data): Invitation
      + delete(id): void
      + count(userId): int
    }

    interface InvitationMediaRepositoryInterface {
      + findByInvitation(invitationId): Collection
      + create(invitationId, data): InvitationMedia
      + update(id, data): Media
      + delete(id): void
      + reorder(invitationId, order): void
      + count(invitationId): int
    }
  }

  package "Eloquent" {
    class EloquentLandingRepository {
      - model: Landing
      --
      + findBySlug(slug): ?Landing
      + findById(id): ?Landing
      + findByUser(user): Collection
      + findPublished(slug): ?Landing
      + create(data): Landing
      + update(id, data): Landing
      + delete(id): void
      + count(userId): int
    }

    class EloquentMediaRepository {
      - model: Media
      --
      + findByLanding(landingId): Collection
      + create(landingId, data): Media
      + update(id, data): Media
      + delete(id): void
      + reorder(landingId, order): void
      + count(landingId): int
    }

    class EloquentThemeRepository {
      - model: Theme
      --
      + findById(id): ?Theme
      + findActive(): Collection
      + findBySlug(slug): ?Theme
      + create(data): Theme
      + update(id, data): Theme
    }

    class EloquentUserRepository {
      - model: User
      --
      + findByEmail(email): ?User
      + findById(id): ?User
      + create(data): User
      + update(id, data): User
      + delete(id): void
    }

    class EloquentInvitationRepository {
      - model: Invitation
      --
      + findBySlug(slug): ?Invitation
      + findById(id): ?Invitation
      + findByUser(user): Collection
      + findPublished(slug): ?Invitation
      + findByLanding(landingId): Collection
      + create(data): Invitation
      + update(id, data): Invitation
      + delete(id): void
      + count(userId): int
    }

    class EloquentInvitationMediaRepository {
      - model: InvitationMedia
      --
      + findByInvitation(invitationId): Collection
      + create(invitationId, data): InvitationMedia
      + update(id, data): Media
      + delete(id): void
      + reorder(invitationId, order): void
      + count(invitationId): int
    }
  }

  EloquentLandingRepository ..|> LandingRepositoryInterface
  EloquentMediaRepository ..|> MediaRepositoryInterface
  EloquentThemeRepository ..|> ThemeRepositoryInterface
  EloquentUserRepository ..|> UserRepositoryInterface
  EloquentInvitationRepository ..|> InvitationRepositoryInterface
  EloquentInvitationMediaRepository ..|> InvitationMediaRepositoryInterface
}

@enduml
```

---

## Diagrama Completo de Arquitectura

Integración de todas las capas.

```
@startuml

package "Presentación" {
  class LandingController {
    - landingService: LandingService
    --
    + create(): View
    + store(request): Response
    + edit(id): View
    + update(id, request): Response
    + show(slug): View
    + destroy(id): Response
  }

  class MediaController {
    - mediaService: MediaService
    --
    + store(request): Response
    + destroy(id): Response
    + reorder(request): Response
  }

  class AuthController {
    --
    + register(request): Response
    + login(request): Response
    + logout(): Response
  }

  class InvitationController {
    - invitationService: InvitationService
    --
    + create(): View
    + store(request): Response
    + edit(id): View
    + update(id, request): Response
    + show(slug): View
    + destroy(id): Response
  }

  class InvitationMediaController {
    - invitationMediaService: InvitationMediaService
    --
    + store(request): Response
    + destroy(id): Response
    + reorder(request): Response
  }
}

package "Lógica de Negocio" {
  class LandingService {
    - landingRepo: LandingRepositoryInterface
    - mediaService: MediaService
    - slugService: SlugService
    --
    + createNewLanding(user, data): Landing
    + updateLanding(id, data): Landing
    + publishLanding(id): void
  }

  class MediaService {
    - mediaRepo: MediaRepositoryInterface
    --
    + uploadImage(landing, file): Media
    + deleteMedia(id): void
  }

  class SlugService {
    --
    + generate(text): string
    + validate(slug): bool
  }

  class InvitationService {
    - invitationRepo: InvitationRepositoryInterface
    - invitationMediaService: InvitationMediaService
    - slugService: SlugService
    --
    + createInvitation(user, data): Invitation
    + updateInvitation(id, data): Invitation
    + publishInvitation(id): void
  }

  class InvitationMediaService {
    - invitationMediaRepo: InvitationMediaRepositoryInterface
    --
    + uploadMedia(invitation, file): InvitationMedia
    + deleteMedia(id): void
  }
}

package "Acceso a Datos" {
  interface LandingRepositoryInterface {
    + findBySlug(slug): ?Landing
    + create(data): Landing
    + update(id, data): Landing
  }

  interface MediaRepositoryInterface {
    + findByLanding(landingId): Collection
    + create(landingId, data): Media
  }

  interface InvitationRepositoryInterface {
    + findBySlug(slug): ?Invitation
    + create(data): Invitation
    + update(id, data): Invitation
  }

  interface InvitationMediaRepositoryInterface {
    + findByInvitation(invitationId): Collection
    + create(invitationId, data): InvitationMedia
  }

  class EloquentLandingRepository {
    - model: Landing
  }

  class EloquentMediaRepository {
    - model: Media
  }

  class EloquentInvitationRepository {
    - model: Invitation
  }

  class EloquentInvitationMediaRepository {
    - model: InvitationMedia
  }

  EloquentLandingRepository ..|> LandingRepositoryInterface
  EloquentMediaRepository ..|> MediaRepositoryInterface
  EloquentInvitationRepository ..|> InvitationRepositoryInterface
  EloquentInvitationMediaRepository ..|> InvitationMediaRepositoryInterface
}

package "Modelos" {
  class User
  class Landing
  class Theme
  class Media
  class Invitation
  class InvitationMedia
}

LandingController --> LandingService : uses
MediaController --> MediaService : uses
InvitationController --> InvitationService : uses
InvitationMediaController --> InvitationMediaService : uses

LandingService --> LandingRepositoryInterface : depends on
LandingService --> MediaService : uses
MediaService --> MediaRepositoryInterface : depends on
InvitationService --> InvitationRepositoryInterface : depends on
InvitationService --> InvitationMediaService : uses
InvitationMediaService --> InvitationMediaRepositoryInterface : depends on

LandingRepositoryInterface --> Landing : works with
MediaRepositoryInterface --> Media : works with
Landing --> User : belongs to
Landing --> Theme : belongs to
Landing --> Media : has many

@enduml
```

---

## Flujo de Creación de Landing

Diagrama de secuencia simplificado.

```
@startuml

actor User as user
participant LandingController as ctrl
participant LandingService as svc
participant SlugService as slug
participant LandingRepository as repo
participant Landing as model

user -> ctrl: POST /landings (create form)
ctrl -> svc: createNewLanding(user, data)
svc -> slug: generate(couple_names)
slug --> svc: "juan-maria-lopez"
svc -> repo: create(data + slug)
repo -> model: Landing::create()
model --> repo: landing object
repo --> svc: landing
svc --> ctrl: landing
ctrl --> user: redirect /landings/:id/edit

@enduml
```

---

**Versión:** 1.0  
**Última actualización:** Enero 2026  
**Autor:** Kevin (Equipo de Desarrollo)
