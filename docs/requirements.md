# Requerimientos - UsPage

Documento que especifica los requerimientos funcionales (RF) y no funcionales (RNF) del proyecto UsPage, enfocado en el MVP.

---

## 📋 Tabla de Contenidos

1. [Requerimientos Funcionales](#requerimientos-funcionales)
2. [Requerimientos No Funcionales](#requerimientos-no-funcionales)
3. [Criterios de Aceptación](#criterios-de-aceptación)

---

## Requerimientos Funcionales

### RF1: Gestión de Usuarios

**Descripción:** El sistema debe permitir a los usuarios registrarse, autenticarse y gestionar su perfil personal.

| Aspecto | Detalle |
|---------|---------|
| **Actor Principal** | Usuario sin autenticar / Usuario autenticado |
| **Precondiciones** | El usuario tiene acceso a la aplicación web |
| **Flujo Principal** | 1. Usuario accede a registro → 2. Ingresa email, contraseña, confirmación → 3. Sistema valida y crea cuenta → 4. Usuario se autentica |
| **Flujos Alternativos** | Recuperación de contraseña, cambio de email, eliminación de cuenta |
| **Postcondiciones** | Usuario con cuenta activa en el sistema, puede crear landing pages |

**Subrequerimientos:**
- **RF1.1:** Registro con email único (validación de unicidad en BD)
- **RF1.2:** Autenticación con correo y contraseña (contraseña hasheada con bcrypt)
- **RF1.3:** Recuperación de contraseña mediante email
- **RF1.4:** Visualización y edición de perfil (nombre, email, foto de perfil)
- **RF1.5:** Eliminación de cuenta (soft delete con archivado de datos)

---

### RF2: Creación y Gestión de Landing Pages

**Descripción:** El usuario autenticado podrá crear una landing page conmemorativa única asociada a su cuenta.

| Aspecto | Detalle |
|---------|---------|
| **Actor Principal** | Usuario autenticado |
| **Precondiciones** | Usuario autenticado; usuario aún no tiene landing page activa |
| **Flujo Principal** | 1. Usuario navega a "Crear Landing" → 2. Ingresa datos básicos (nombres, fecha aniversario) → 3. Selecciona tema → 4. Sistema genera slug único → 5. Landing es publicada |
| **Flujos Alternativos** | Edición posterior, cambio de tema, publicación/despublicación |
| **Postcondiciones** | Landing page creada y accesible vía URL pública |

**Subrequerimientos:**
- **RF2.1:** Un usuario = una landing page (relación 1:1)
- **RF2.2:** Generación automática de slug único a partir del nombre
- **RF2.3:** Validación de slug: 3-50 caracteres, alfanuméricos + guiones, sin colisiones
- **RF2.4:** Edición de landing (nombres, fecha, bio, música)
- **RF2.5:** Despublicación/publicación de landing (soft delete)
- **RF2.6:** Visualización de preview antes de publicar

---

### RF3: Personalización de Contenido

**Descripción:** El usuario podrá personalizar el contenido de su landing page (texto, multimedia y estructura).

| Aspecto | Detalle |
|---------|---------|
| **Actor Principal** | Usuario propietario de la landing |
| **Precondiciones** | Landing page creada y en modo edición |
| **Flujo Principal** | 1. Usuario accede a editor → 2. Modifica nombres, fecha, mensaje → 3. Agrega momentos (eventos/links) → 4. Guarda cambios |
| **Postcondiciones** | Cambios reflejados en vista pública inmediatamente |

**Subrequerimientos:**
- **RF3.1:** Edición de nombres de la pareja (máx. 100 caracteres cada uno)
- **RF3.2:** Edición de fecha de aniversario (con cálculo automático de años)
- **RF3.3:** Edición de mensaje principal / bio (máx. 500 caracteres)
- **RF3.4:** Gestión de "Momentos": listado ordenable de eventos/hitos con fechas y descripciones
- **RF3.5:** Edición de música de fondo (URL de Spotify u otro servicio)
- **RF3.6:** Vista previa en tiempo real de cambios (no requiere guardar)

---

### RF4: Gestión de Galería Multimedia

**Descripción:** El usuario podrá subir, organizar y eliminar imágenes asociadas a su landing page.

| Aspecto | Detalle |
|---------|---------|
| **Actor Principal** | Usuario propietario de la landing |
| **Precondiciones** | Landing creada; usuario en sección de galería |
| **Flujo Principal** | 1. Usuario selecciona archivo(s) → 2. Sistema valida formato y tamaño → 3. Imagen se procesa y almacena → 4. Se agrega a galería visible |
| **Flujos Alternativos** | Reordenamiento de imágenes (drag & drop), eliminación, descarga |
| **Postcondiciones** | Imagen visible en galería de landing pública |

**Subrequerimientos:**
- **RF4.1:** Subida de imágenes (JPG, PNG, WebP, máx. 5 MB cada una)
- **RF4.2:** Validación de tipo de archivo (MIME type)
- **RF4.3:** Almacenamiento optimizado (local o S3)
- **RF4.4:** Reordenamiento de imágenes mediante drag & drop
- **RF4.5:** Eliminación lógica de imágenes (marcar como inactivas)
- **RF4.6:** Generación de thumbnails automáticos
- **RF4.7:** Limite de 50 imágenes por landing page

---

### RF5: Selección de Temas Visuales

**Descripción:** El usuario podrá elegir entre múltiples temas visuales predefinidos para su landing.

| Aspecto | Detalle |
|---------|---------|
| **Actor Principal** | Usuario propietario de la landing |
| **Precondiciones** | Landing creada; temas disponibles en sistema |
| **Flujo Principal** | 1. Usuario accede a sección "Temas" → 2. Visualiza previsualizaciones → 3. Selecciona tema → 4. Cambios aplican inmediatamente |
| **Postcondiciones** | Landing renderiza con nuevo tema |

**Subrequerimientos:**
- **RF5.1:** Catálogo mínimo de 5 temas predefinidos
- **RF5.2:** Cada tema contiene: nombre, CSS classes, paleta de colores, config JSON
- **RF5.3:** Previsualización dinámica de tema antes de seleccionar
- **RF5.4:** Cambio de tema no afecta datos de contenido
- **RF5.5:** Extensibilidad: soporte para temas custom (futuro)

---

### RF6: Visualización Pública de Landing Pages

**Descripción:** Cualquier visitante (autenticado o no) podrá acceder y visualizar landing pages públicas mediante una URL única.

| Aspecto | Detalle |
|---------|---------|
| **Actor Principal** | Visitante anónimo / Usuario autenticado |
| **Precondiciones** | Landing page publicada; visitante tiene URL de slug |
| **Flujo Principal** | 1. Visitante accede a `/p/{slug}` → 2. Sistema verifica existencia y publicación → 3. Renderiza landing con tema seleccionado → 4. Galería, momentos y metadata cargan |
| **Flujos Alternativos** | Landing no existe (404), landing despublicada (403), error de carga |
| **Postcondiciones** | Visitante ve landing renderizada completamente |

**Subrequerimientos:**
- **RF6.1:** Ruta pública: `/p/{slug}` con validación de slug
- **RF6.2:** Caché de landing pública (2 horas) para optimizar rendimiento
- **RF6.3:** Metadata dinámica (Open Graph) para compartir en redes sociales
- **RF6.4:** Responsividad total (mobile-first)
- **RF6.5:** Prevención de acceso a landings despublicadas

---

### RF7: Validación y Manejo de Errores

**Descripción:** El sistema debe validar toda entrada de usuario y proporcionar mensajes de error claros.

| Aspecto | Detalle |
|---------|---------|
| **Validaciones Críticas** | Slug único, email válido, fecha en formato correcto, tipos de archivo en subida |
| **Mensajes de Error** | Localizados, específicos y accionables |
| **Manejo de Excepciones** | Registros en logs, notificación a usuario, sin exposición de datos sensibles |

---

## Requerimientos No Funcionales

### RNF1: Persistencia y Normalización de Datos

**Especificación:** El diseño de la base de datos debe cumplir con la **Tercera Forma Normal (3NF)** para garantizar integridad referencial, evitar anomalías y reducir redundancia.

| Aspecto | Detalle |
|---------|---------|
| **Nivel de Normalización** | 3NF (Tercera Forma Normal) |
| **Integridad Referencial** | Foreign keys con cascadas apropiadas |
| **Evitar Dependencias Transitivas** | Separación de temas, media, y landing en tablas distintas |
| **Reducción de Redundancia** | Cada dato se almacena una única vez |
| **Índices** | Índices en FK, PK, y campos frecuentemente consultados (slug, user_id) |

**Tablas Principales (3NF):**

```sql
-- Users (1NF)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Themes (1NF - independiente de Landing)
CREATE TABLE themes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    css_class VARCHAR(100),
    config_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Landings (2NF - elimina dependencias parciales)
CREATE TABLE landings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    theme_id INT NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    couple_names VARCHAR(200) NOT NULL,
    anniversary_date DATE NOT NULL,
    bio_text TEXT,
    music_url VARCHAR(500),
    is_published BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE RESTRICT,
    INDEX idx_slug (slug),
    INDEX idx_user_id (user_id)
);

-- Media (3NF - separado de Landing, 1:N)
CREATE TABLE media (
    id INT PRIMARY KEY AUTO_INCREMENT,
    landing_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    type ENUM('image', 'video') DEFAULT 'image',
    order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (landing_id) REFERENCES landings(id) ON DELETE CASCADE,
    INDEX idx_landing_id (landing_id),
    INDEX idx_order (order)
);

-- Moments (Eventos/hitos de la pareja)
CREATE TABLE moments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    landing_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    moment_date DATE NOT NULL,
    order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (landing_id) REFERENCES landings(id) ON DELETE CASCADE,
    INDEX idx_landing_id (landing_id),
    INDEX idx_order (order)
);
```

---

### RNF2: Arquitectura Escalable con Repositorios y Servicios

**Especificación:** Implementación obligatoria del patrón **Repository Pattern** y **Service Layer** para aislar lógica de negocio de la persistencia.

| Aspecto | Detalle |
|---------|---------|
| **Repository Pattern** | Interfaz para cada entidad; implementaciones con Eloquent |
| **Service Layer** | Lógica de negocio pura sin dependencia directa de BD |
| **Inyección de Dependencias** | Todos los servicios reciben repositorios vía constructor |
| **Testabilidad** | Facilitar mocks de repositorios en tests unitarios |

**Estructura:**

```
app/
├── Repositories/
│   ├── Interfaces/
│   │   ├── LandingRepositoryInterface.php
│   │   ├── MediaRepositoryInterface.php
│   │   └── UserRepositoryInterface.php
│   └── Eloquent/
│       ├── EloquentLandingRepository.php
│       ├── EloquentMediaRepository.php
│       └── EloquentUserRepository.php
├── Services/
│   ├── LandingService.php
│   ├── MediaService.php
│   └── UserService.php
└── Http/Controllers/
    ├── DashboardController.php
    ├── LandingController.php
    └── MediaController.php
```

---

### RNF3: Rendimiento y Optimización

**Especificación:** La aplicación debe garantizar tiempos de respuesta rápidos y carga optimizada.

| Métrica | Objetivo | Implementación |
|---------|----------|-----------------|
| **Tiempo de carga (TTFB)** | < 200 ms | Caché de landing pública, índices en BD, CDN para assets |
| **Time to Interactive (TTI)** | < 3 s | Code splitting, lazy loading de componentes Vue |
| **Image Optimization** | WebP + thumbnails | Sharp.js o similar; servir múltiples formatos |
| **Lazy Loading** | Imágenes de galería | Intersection Observer en componentes Vue |
| **Query Optimization** | N+1 problem eliminado | Eager loading con Eloquent (with()) |
| **Caché de BD** | 2h para landings públicas | Redis o file cache |
| **Compresión** | Gzip + Brotli | Middleware de compresión |

---

### RNF4: Seguridad

**Especificación:** Aplicar medidas robustas para proteger datos de usuario y prevenir ataques comunes.

| Amenaza | Medida de Control |
|---------|------------------|
| **SQL Injection** | Queries parametrizadas con Eloquent (ORM) |
| **CSRF** | Tokens CSRF en todos los formularios |
| **XSS (Cross-Site Scripting)** | Sanitización de entrada, escape de output en Vue |
| **Brute Force** | Rate limiting en login (Fortify) |
| **Acceso No Autorizado** | Políticas de autorización (Policies) por landing |
| **Contraseñas Débiles** | Validación con reglas Laravel (min. 8 caracteres, complejas) |
| **Session Hijacking** | Cookies HTTP-only, SameSite strict |
| **CORS** | Configuración restrictiva si hay API externa |
| **Slug Enumeration** | Validación estricta; prevención de patrones predecibles |

---

### RNF5: Escalabilidad Horizontal

**Especificación:** Arquitectura preparada para crecer sin degradación significativa de rendimiento.

| Aspecto | Requisito |
|---------|-----------|
| **Statelessness** | Backend stateless; sesiones en BD o Redis |
| **Load Balancing** | Soporte para múltiples instancias de aplicación |
| **File Storage** | S3-compatible (AWS/MinIO) para escalabilidad; no local |
| **Queue** | Tareas pesadas en cola (procesamiento de imágenes) |
| **Monitoring** | Logs centralizados; alertas configurables |

---

### RNF6: Usabilidad y Experiencia de Usuario

**Especificación:** Interfaz intuitiva, responsive y accesible.

| Aspecto | Requisito |
|---------|-----------|
| **Responsividad** | Mobile-first; funcional en dispositivos desde 320px |
| **Accesibilidad** | WCAG 2.1 AA (contraste, alt text, navegación por teclado) |
| **Idioma** | Interfaz en español (i18n configurado) |
| **Feedback Visual** | Loading states, toast notifications, validación en vivo |
| **Navegación** | Menú claro, breadcrumbs, sitemap |

---

### RNF7: Mantenibilidad y Documentación

**Especificación:** Código limpio, bien documentado y fácil de mantener.

| Aspecto | Requisito |
|---------|-----------|
| **Code Style** | PSR-12 (Laravel Pint) |
| **Type Safety** | PHP 8 type hints en 100% del código |
| **PHPDoc** | Bloques PHPDoc en métodos públicos |
| **Testing** | Cobertura mínima 70%; tests Feature y Unit |
| **Documentación** | README, docs/ con especificaciones |
| **Versionado** | Semántico (MAJOR.MINOR.PATCH) |

---

### RNF8: Compatibilidad y Requisitos de Plataforma

**Especificación:** Funcionalidad verificada en múltiples entornos.

| Componente | Versión Mínima | Versión Recomendada |
|------------|-----------------|-------------------|
| PHP | 8.4 | 8.4+ |
| Laravel | 12 | 12 |
| Node.js | 18 | 20 LTS |
| MySQL | 8.0 | 8.0+ |
| MariaDB | 10.6 | 11+ |
| Navegadores | Chrome 90, Firefox 88, Safari 14 | Últimas 2 versiones |

---

## Restricciones Técnicas

1. **Monolito Laravel:** No usar microservicios en fase inicial; arquitectura monolítica escalable.
2. **SPA Moderno:** Frontend con Vue 3 + Inertia (no Blade directo).
3. **Type Safety:** TypeScript en frontend; PHP 8 type hints en backend.
4. **Versionamiento:** Git flow; semantic versioning.
5. **CI/CD:** Pruebas automáticas en cada push (GitHub Actions o similar).
6. **Depuración:** Debugbar en desarrollo; logs centralizados en producción.

---

## Criterios de Aceptación

Toda funcionalidad debe cumplir:

- ✅ **Código:** Sigue PSR-12 y Laravel conventions
- ✅ **Tests:** Mínimo 1 test Feature; happy path + 1 error path
- ✅ **Documentación:** README actualizado; cambios en docs/ si aplica
- ✅ **Performance:** No degradación de métricas (TTFB, TTI)
- ✅ **Seguridad:** Sin vulnerabilidades conocidas (OWASP Top 10)
- ✅ **Mobile:** Funcional en pantallas pequeñas (320px+)
- ✅ **Versioning:** Código pusheado con mensaje de commit descriptivo

---

## Matriz de Trazabilidad

| RF | RNF Aplicable | Criticidad | Estado | Responsable |
|----|---------------|-----------|--------|-------------|
| RF1 | RNF1, RNF2, RNF4, RNF6, RNF7 | ALTA | Pendiente | Kevin |
| RF2 | RNF1, RNF2, RNF3, RNF4, RNF7 | CRÍTICA | Pendiente | Kevin |
| RF3 | RNF1, RNF2, RNF6, RNF7 | MEDIA | Pendiente | Kevin |
| RF4 | RNF1, RNF2, RNF3, RNF5, RNF7 | ALTA | Pendiente | Kevin |
| RF5 | RNF1, RNF2, RNF6, RNF7 | MEDIA | Pendiente | Kevin |
| RF6 | RNF1, RNF2, RNF3, RNF4, RNF6, RNF7 | CRÍTICA | Pendiente | Kevin |
| RF7 | RNF2, RNF4, RNF7 | ALTA | Pendiente | Kevin |

---

**Versión:** 1.0  
**Última actualización:** Enero 2026  
**Autor:** Kevin (Equipo de Desarrollo)

