# Requerimientos - UsPage

Documento que especifica los requerimientos funcionales (RF) y no funcionales (RNF) del proyecto UsPage en su fase MVP.

---

## 📋 Tabla de Contenidos

1. [Requerimientos Funcionales](#requerimientos-funcionales)
2. [Requerimientos No Funcionales](#requerimientos-no-funcionales)
3. [Criterios de Aceptación](#criterios-de-aceptación)

---

## Requerimientos Funcionales

### RF1: Gestión de Usuarios

El sistema permite registro e inicio de sesión con email y contraseña.

- **RF1.1** - Registro: Email único, contraseña hasheada (bcrypt)
- **RF1.2** - Autenticación: Login con email y contraseña
- **RF1.3** - Sesión: Persistencia en aplicación
- **RF1.4** - Logout: Cerrar sesión

---

### RF2: Creación de Landing Page

Un usuario autenticado puede crear **múltiples landing pages**, cada una con su propio slug único.

- **RF2.1** - Un usuario puede tener N landings (relación 1:N)
- **RF2.2** - Slug único generado automáticamente (3-50 caracteres, alfanumérico + guiones) o personalizado
- **RF2.3** - Campos: nombres de pareja, fecha de aniversario, bio
- **RF2.4** - Selección de tema base al crear

---

### RF3: Personalización de Landing

El propietario personaliza contenido y apariencia.

- **RF3.1** - Editar nombres, fecha, bio
- **RF3.2** - Cambiar tema (sin perder contenido)
- **RF3.3** - Personalizar colores: primario, secundario, fondo
- **RF3.4** - Cambiar imagen de fondo
- **RF3.5** - Vista previa en tiempo real

---

### RF4: Galería Multimedia

El usuario gestiona imágenes en su landing.

- **RF4.1** - Subir imágenes: JPG, PNG, WebP (máx. 15 MB)
- **RF4.2** - Máximo  10 imágenes por landing
- **RF4.3** - Reordenamiento drag & drop (opcional)
- **RF4.4** - Eliminación lógica
- **RF4.5** - **(OPCIONAL)** Thumbnails automáticos
- **RF4.6** - Guardar URL pública (CDN/storage) de cada imagen

---

### RF5: Temas Visuales Personalizables

El usuario puede seleccionar un tema del sitema o crear y personalizar un tema.

- **RF5.1** - Catálogo de al menos 3 temas
- **RF5.2** - Cada tema: nombre, colores por defecto, config
- **RF5.3** - Editar colores y fondo sin perder datos
- **RF5.4** - Cambios aplican inmediatamente

---

### RF6: Visualización Pública

Visitantes acceden a landings publicadas.

- **RF6.1** - Ruta: `/p/{slug}`
- **RF6.2** - Solo landings publicadas accesibles
- **RF6.3** - Responsive (mobile-first)
- **RF6.4** - Visualiza: nombres, fecha, bio, galería, tema personalizado

---

### RF7: Invitaciones Personalizadas

Entidad independiente para crear invitaciones (ej: San Valentín) con mensajes personalizables y multimedia.

- **RF7.1** - Título personalizado (default: "¿Quieres ser mi San Valentín?")
- **RF7.2** - Mensaje de respuesta afirmativa editable (default: "Sí")
- **RF7.3** - Lista de mensajes de respuesta negativa personalizables (default: ["No", "Tal vez", "No te arrepentirás", "Piénsalo mejor"])
- **RF7.4** - Slug único generado automáticamente para URL pública (`/invitaciones/{slug}`)
- **RF7.5** - Soporte para multimedia (imágenes y GIFs, max 5 elementos)
- **RF7.6** - GIFs habilitados hasta 10MB
- **RF7.7** - Soft delete habilitado
- **RF7.8** - Gestión independiente de multimedia con `InvitationMedia`

---

### RF8: Validación y Manejo de Errores

- **RF8.1** - Slug: unicidad, formato validado
- **RF8.2** - Email: formato correcto
- **RF8.3** - Archivos: tipo, tamaño, MIME type
- **RF8.4** - Mensajes claros al usuario

---

## Requerimientos No Funcionales

### RNF1: Arquitectura Escalable

- **RNF1.1** - Patrón Repository para acceso a datos
- **RNF1.2** - Capa Service para lógica de negocio
- **RNF1.3** - Controladores delgados
- **RNF1.4** - Form Requests para validación centralizada

---

### RNF2: Base de Datos (3NF)

- **RNF2.1** - Cumplimiento de Tercera Forma Normal
- **RNF2.2** - Tablas: Users, Landings, Themes, Media, SystemControl, Invitations, InvitationMedia
- **RNF2.3** - Relaciones definidas: 
  - 1:N (User-Landing, User-Invitation)
  - M:1 (Landing-Theme)
  - 1:N (Landing-Media, Invitation-InvitationMedia)
  - N:1 opcional (Invitation-Landing, nullable)
- **RNF2.4** - SystemControl: configuración global para límites de media (máx. imágenes, tamaño, MIME permitidos, thumbnails, GIF enabled)
- **RNF2.5** - Índices en: slug, user_id, theme_id, landing_id, invitation_id
- **RNF2.6** - Soft delete en Users, Landings, Invitations

---

### RNF3: Seguridad Básica (MVP)

- **RNF3.1** - Autenticación con Laravel Breeze
- **RNF3.2** - CSRF tokens en formularios
- **RNF3.3** - Sanitización de slugs
- **RNF3.4** - Hashing bcrypt en contraseñas
- **RNF3.5** - Validación en Form Requests
- **RNF3.6** - Policies para autorización (solo propietario edita)

---

### RNF4: Testing

- **RNF4.1** - Tests Feature para casos principales
---

### RNF5: Rendimiento

- **RNF5.1** - Eager loading (evitar N+1)
- **RNF5.2** - Índices en columnas frecuentes solo las esenciales
- **RNF5.3** - **(FUTURO)** Caché de landings públicas

---

### RNF6: Monitorización

- **RNF6.1** - Laravel Telescope en desarrollo
- **RNF6.2** - Logs estructurados para errores

---

### RNF7: Frontend

- **RNF7.1** - Componentes reutilizables Vue
- **RNF7.2** - TypeScript para type safety
- **RNF7.3** - Tailwind CSS para estilos
- **RNF7.4** - Validación en cliente

---

## Criterios de Aceptación

Toda funcionalidad debe cumplir:

✅ **Código:**
- Estándar PSR-12 (Pint)
- Type hints en PHP 8
- Sin errores en análisis estático

✅ **Tests:**
- Mínimo 1 test Feature
- Happy path + 1 caso error

✅ **Seguridad:**
- Validación en Form Requests
- Sin SQL injection (Eloquent)

✅ **Mobile:**
- Responsive 320px+
- Funcional en navegadores modernos

---

**Versión:** 1.2 
**Última actualización:** Enero 2026  
**Autor:** Kevin (Equipo de Desarrollo)
