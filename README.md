# LoveLink

LoveLink es una **plataforma web moderna** que permite a las parejas crear landing pages conmemorativas personalizadas con galerías de fotos, temas visuales y contenido romántico. Desarrollada con **Laravel 12, Vue 3 e Inertia.js**, implementa patrones de arquitectura empresarial (Repository + Service) y está optimizada para **despliegue en producción con Docker**.

## 🎯 Propósito del Proyecto

Este proyecto demuestra **habilidades de desarrollo full-stack profesional** con énfasis en:
- ✅ **Backend robusto** con arquitectura limpia y SOLID principles
- ✅ **Frontend SPA moderno** con Vue 3 + Composition API
- ✅ **DevOps completo** con Docker + Nginx optimizado
- ✅ **Base de datos normalizada** siguiendo 3NF
- ✅ **Testing automatizado** y prácticas de calidad de código
- ✅ **Integración cloud** con Digital Ocean Spaces

Perfecto para parejas que quieren **inmortalizar su historia de amor** en una página web única y elegante.

---

## 🛠️ Stack Tecnológico

| Componente | Tecnología | Versión |
|-----------|-----------|---------|
| **Backend Framework** | Laravel | 12 |
| **Runtime** | PHP | 8.4+ |
| **Frontend Framework** | Vue 3 (Composition API) | 3 |
| **SPA Meta-Framework** | Inertia.js | 2 |
| **CSS Framework** | Tailwind CSS | 4 |
| **Base de Datos** | MySQL/MariaDB | 11+ |
| **Containerización** | Docker + Docker Compose | Latest |
| **Servidor Web** | Nginx | Latest |
| **Almacenamiento Cloud** | Digital Ocean Spaces | Latest |
| **Build Tools** | Vite + Laravel Mix | Latest |
| **Testing** | PHPUnit + Pest | Latest |

---

## 📋 Características Principales

- ✅ **Sistema de Autenticación Completo:** Registro, login, recuperación de contraseña con Laravel Fortify
- ✅ **Landing Pages Personalizables:** URLs amigables (`/p/{slug}`) con SEO optimizado
- ✅ **Editor Visual de Temas:** Catálogo de temas con colores, fondos y tipografías personalizables
- ✅ **Galería Multimedia Avanzada:** Upload de imágenes (JPG, PNG, WebP) hasta 100MB con thumbnails automáticos
- ✅ **Almacenamiento Cloud:** Integración con Digital Ocean Spaces para escalabilidad
- ✅ **Invitaciones Especiales:** Páginas de San Valentín con mensajes personalizables
- ✅ **Arquitectura Empresarial:** Patrón Repository + Service + DTO para mantenibilidad
- ✅ **Despliegue Production-Ready:** Docker + Nginx + SSL/TLS con Cloudflare
- ✅ **API RESTful:** Endpoints documentados con validación robusta

---

## 🏗️ Arquitectura

El proyecto implementa **Clean Architecture** con separación clara de responsabilidades:

### Capas de la Aplicación

```
🌐 Frontend (Vue 3 + Inertia.js)
     ↓ HTTP Requests
🎮 Controllers (Orchestration Layer)
     ↓ Business Logic
🧠 Services (Domain Logic)
     ↓ Data Access
📦 Repositories (Data Abstraction)
     ↓ ORM Queries
🗄️ Models (Eloquent/Database)
```

### Principios Implementados

- **Single Responsibility:** Cada clase tiene una única responsabilidad
- **Dependency Injection:** Services y Repositories inyectados via Service Container
- **Interface Segregation:** Contratos explícitos para cada Repository
- **Clean Code:** PSR-12, tipado estricto, naming conventions
- **Testing:** Unit tests para Services, Feature tests para Controllers

### Estructura Modular

```
app/
├── Http/Controllers/     # Orquestación de requests/responses
├── Services/            # Lógica de negocio pura
├── Repositories/        # Acceso a datos (abstraction layer)
├── Models/             # Eloquent ORM models
├── DTOs/               # Data Transfer Objects
resources/js/
├── pages/              # Vue SPA pages (Inertia)
├── components/         # Componentes reutilizables
├── composables/        # Vue 3 Composition API logic
└── layouts/           # Layout templates
```

```
uspage/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Controladores (orquestadores)
│   │   ├── Middleware/           # Middlewares
│   │   └── Requests/             # Form Requests (validación)
│   ├── Models/                   # Modelos Eloquent
│   ├── Repositories/             # Capa de Acceso a Datos
│   │   ├── Interfaces/           # Contratos de repositorios
│   │   └── Eloquent/             # Implementaciones con Eloquent
│   ├── Services/                 # Capa de Negocio
│   └── Providers/                # Service Providers
├── resources/
│   ├── js/
│   │   ├── components/           # Componentes Vue reutilizables
│   │   ├── pages/                # Páginas (vistas Inertia)
│   │   ├── layouts/              # Layouts reutilizables
│   │   ├── composables/          # Composables de Vue
│   │   ├── actions/              # Wayfinder (rutas tipadas)
│   │   └── types/                # Tipos TypeScript
│   └── css/
│       └── app.css               # Estilos globales (Tailwind)
├── routes/
│   ├── web.php                   # Rutas web públicas
│   └── console.php               # Comandos Artisan
├── database/
│   ├── migrations/               # Migraciones
│   ├── factories/                # Factories para testing
│   └── seeders/                  # Seeders para datos iniciales
├── tests/
│   ├── Feature/                  # Tests de características
│   └── Unit/                     # Tests unitarios
├── docs/                         # Documentación del proyecto
│   ├── requirements.md           # Requerimientos funcionales
│   ├── domain.md                 # Modelo de dominio
│   └── architecture.md           # Guía de arquitectura
├── bootstrap/
│   ├── app.php                   # Configuración de la app
│   └── providers.php             # Providers registrados
├── config/                       # Archivos de configuración
├── public/                       # Activos públicos
├── storage/                      # Almacenamiento (logs, cache)
├── vendor/                       # Dependencias (Composer)
├── .env.example                  # Variables de entorno (plantilla)
├── composer.json                 # Dependencias de PHP
├── package.json                  # Dependencias de Node.js
├── vite.config.ts                # Configuración de Vite
├── tailwind.config.js            # Configuración de Tailwind CSS
└── phpunit.xml                   # Configuración de PHPUnit
```

---

## 🚀 Instalación y Configuración

### Requisitos Previos

- **PHP 8.4+** con extensiones: `curl`, `json`, `mbstring`, `tokenizer`, `xml`
- **Composer** (gestor de dependencias de PHP)
- **Node.js 18+** y **npm 9+**
- **MySQL 8.0+** o **MariaDB 10.6+**
- **Git** (control de versiones)

### Pasos de Instalación

#### 1. Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/uspage.git
cd uspage
```

#### 2. Instalar Dependencias PHP

```bash
composer install
```

#### 3. Instalar Dependencias Node.js

```bash
npm install
```

#### 4. Configurar Variables de Entorno

```bash
cp .env.example .env
```

Edita `.env` y configura:
- `APP_NAME`, `APP_URL`, `APP_DEBUG`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `MAIL_*` (si usas correo)
- **Digital Ocean Spaces** (para almacenamiento de media):
  - `CLOUD_ACCESS_KEY_ID`: Tu Access Key de DO Spaces
  - `CLOUD_SECRET_ACCESS_KEY`: Tu Secret Key de DO Spaces
  - `MEDIA_STORAGE_DRIVER=s3` (para producción) o `local` (para desarrollo)

#### 5. Generar Clave de Aplicación

```bash
php artisan key:generate
```

#### 6. Ejecutar Migraciones

```bash
php artisan migrate
```

#### 7. (Opcional) Poblar Base de Datos

```bash
php artisan db:seed
```

#### 8. Iniciar el Servidor de Desarrollo

En una terminal:

```bash
php artisan serve
```

En otra terminal (para compilar assets):

```bash
npm run dev
```

La aplicación estará disponible en `http://localhost:8000`.

---

## 🌐 Almacenamiento de Media (Digital Ocean Spaces)

El proyecto está configurado para usar **Digital Ocean Spaces** (Amsterdam) para almacenamiento de imágenes en producción, con fallback local para desarrollo.

### 🚀 Configuración de Producción

Para usar Digital Ocean Spaces en producción, configura estas variables en tu `.env`:

```bash
# Cloud Storage Configuration (Multi-provider: Digital Ocean Spaces, AWS S3, etc.)
MEDIA_STORAGE_DRIVER=s3
CLOUD_ACCESS_KEY_ID=tu_cloud_access_key
CLOUD_SECRET_ACCESS_KEY=tu_cloud_secret_key
CLOUD_DEFAULT_REGION=ams3
CLOUD_BUCKET=uspage-storage
CLOUD_URL=https://uspage-storage.ams3.digitaloceanspaces.com
CLOUD_ENDPOINT=https://ams3.digitaloceanspaces.com
```

### 🛠️ Configuración de Desarrollo

```bash
# En .env para desarrollo local:
MEDIA_STORAGE_DRIVER=local
```

### 📋 Setup Digital Ocean Spaces

1. **Crear Bucket en Digital Ocean:**
   - Nombre: `uspage-storage`
   - Región: Amsterdam (`ams3`)
   - Acceso: Privado (recomendado para seguridad)

2. **Generar API Keys:**
   - Dashboard → API → Spaces Keys
   - Crear nuevo par de claves
   - Copiar Access Key ID y Secret Key

3. **Probar Conexión:**
   ```bash
   php artisan test:cloud-storage
   ```

### 🌍 ¿Por qué Amsterdam?

- ✅ **Latencia óptima**: ~20-30ms desde España
- ✅ **Conectividad LATAM**: Excelentes rutas a América Latina  
- ✅ **Compliance EU**: Cumple con GDPR y normativas europeas
- ✅ **Costo-efectivo**: Mejor precio que AWS S3 equivalente
- ✅ **CDN global**: Red de distribución automática

### ⚙️ Características Técnicas

- **Límite por imagen**: 10MB máximo por archivo
- **Formatos soportados**: JPG, PNG, WebP, GIF
- **URLs firmadas**: Acceso seguro con expiración
- **Backup automático**: Digital Ocean maneja redundancia
- **Switching automático**: El sistema cambia entre local/cloud según configuración

---

## 📝 Comandos Útiles

### Laravel (Backend)

```bash
# Crear una migración
php artisan make:migration create_table_name

# Crear un modelo con migración
php artisan make:model ModelName -m

# Crear un controlador
php artisan make:controller ControllerName

# Ejecutar migraciones
php artisan migrate

# Revertir última migración
php artisan migrate:rollback

# Ejecutar tests
php artisan test

# Ejecutar tests de un archivo específico
php artisan test tests/Feature/ExampleTest.php

# Formatear código con Pint
vendor/bin/pint

# Ejecutar Tinker (REPL interactivo)
php artisan tinker

# Crear enlace simbólico para storage público (solo desarrollo)
php artisan storage:link

# Test de conectividad con Cloud Storage (Digital Ocean Spaces, AWS S3, etc.)
php artisan test:cloud-storage

# También puedes probar manualmente con Tinker
php artisan tinker
# Dentro de tinker: Storage::disk('media_cloud')->put('test.txt', 'Hello Cloud!');
```

### Frontend (Node.js)

```bash
# Compilar assets para desarrollo
npm run dev

# Compilar assets para producción
npm run build

# Ver vista previa de producción
npm run preview
```

---

## 🗄️ Base de Datos

El proyecto utiliza **MySQL/MariaDB** siguiendo la **Tercera Forma Normal (3NF)** para garantizar integridad referencial y evitar redundancia de datos.

### Diagrama de Entidades

```
┌─────────────────┐
│     Users       │
├─────────────────┤
│ id (PK)         │
│ email (UNIQUE)  │
│ password        │
│ timestamps      │
└─────────────────┘
         │
         │ 1:1
         │
┌─────────────────────────────┐
│       Landings              │
├─────────────────────────────┤
│ id (PK)                     │
│ user_id (FK)                │
│ theme_id (FK)               │
│ slug (UNIQUE)               │
│ couple_names                │
│ anniversary_date            │
│ bio_text                    │
│ music_url (nullable)        │
│ timestamps                  │
└─────────────────────────────┘
         │
         │ 1:N
         │
┌─────────────────────────────┐
│       Media                 │
├─────────────────────────────┤
│ id (PK)                     │
│ landing_id (FK)             │
│ file_path                   │
│ type (image/video)          │
│ order                       │
│ timestamps                  │
└─────────────────────────────┘

┌─────────────────┐
│     Themes      │
├─────────────────┤
│ id (PK)         │
│ name            │
│ css_class       │
│ config_json     │
│ timestamps      │
└─────────────────┘
```

---

## 🧪 Testing

El proyecto utiliza **PHPUnit** para garantizar la calidad del código.

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests con output compacto
php artisan test --compact

# Ejecutar un archivo de test específico
php artisan test tests/Feature/DashboardTest.php

# Ejecutar tests con patrón de nombre
php artisan test --filter=testMethodName
```

---

## 🔒 Seguridad (Básica para MVP)

- ✅ **Autenticación:** Laravel Breeze con sesiones
- ✅ **CSRF Protection:** Tokens en formularios
- ✅ **Validación:** Form Requests en cada controller
- ✅ **Sanitización:** Slugs normalizados, sin caracteres especiales
- ✅ **Hashing:** Contraseñas con bcrypt
- ✅ **Autorización:** Policies para verificar propietario de landing
- ⚠️ **Rate Limiting:** No implementado en MVP (futuro)

---

## � Monitorización en Desarrollo

El proyecto utiliza **Laravel Telescope** para debuguear requests, queries, y eventos en tiempo real:

```bash
php artisan telescope:publish
# Accesible en http://localhost:8000/telescope
```

---

## 📚 Documentación del Proyecto

Consulta la carpeta `docs/` para detalles técnicos:

- **[requirements.md](docs/requirements.md)** - Requerimientos funcionales (RF) y no funcionales (RNF)
- **[domain.md](docs/domain.md)** - Modelo de dominio, entidades, ER, SystemControl
- **[use-cases.md](docs/use-cases.md)** - Casos de uso (incluye invitación San Valentín)
- **[class-diagram.md](docs/class-diagram.md)** - Diagramas UML de clases, services y repositorios

**Nota:** Este es un MVP; la documentación se enfoca en lo esencial.

---

## 🤝 Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/amazing-feature`)
3. Commit tus cambios (`git commit -m 'Add amazing feature'`)
4. Push a la rama (`git push origin feature/amazing-feature`)
5. Abre un Pull Request

---

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Consulta el archivo `LICENSE` para más detalles.

---

## 👨‍💻 Autor

**Kevin** - Desarrollador Backend | Estudiando Ingeniería de Software

Este proyecto es parte de mi portafolio para demostrar conocimientos en:
- Arquitectura de software (Repositories, Services)
- Modelado de datos (3NF)
- Testing unitario y funcional
- Frontend con Vue 3 + Inertia (aprendizaje en progreso)

---

**Última actualización:** Enero 2026
