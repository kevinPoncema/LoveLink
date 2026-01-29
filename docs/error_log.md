# Registro de Errores y Soluciones

## Error 403 Forbidden en Archivos Multimedia (29/01/2026)

### Descripción del Problema
Los archivos multimedia subidos devolvían un error `403 Forbidden` al intentar acceder a ellos a través de su URL pública, y las vistas previas de imágenes aparecían rotas.

**Síntomas:**
- URLs de imágenes (ej. `http://localhost:8000/storage/media/users/1/image.png`) devolvían 403.
- Laraveles respondía con una página de error de "Forbidden".
- La vista de galería mostraba iconos de error en lugar de las imágenes.

### Causa Raíz
El enlace simbólico (symlink) `public/storage` estaba configurado incorrectamente como una **ruta absoluta** en el sistema de archivos (`/home/kevin...`), lo que impedía que el servidor web resolviera correctamente la ruta hacia `storage/app/public` debido a restricciones de permisos o configuración del servidor web, especialmente en entornos Docker o con usuarios específicos.

### Solución Implementada
Se reemplazó el enlace simbólico absoluto por uno relativo.

**Pasos realizados:**
1. Eliminación del enlace simbólico incorrecto:
   ```bash
   rm public/storage
   ```

2. Creación del nuevo enlace simbólico relativo desde la carpeta `public`:
   ```bash
   cd public
   ln -s ../storage/app/public storage
   ```

**Verificación:**
Al ejecutar `ls -la public/storage`, ahora muestra:
`storage -> ../storage/app/public`

Esto asegura que el enlace sea válido independientemente de la ruta absoluta donde se encuentre alojado el proyecto.
