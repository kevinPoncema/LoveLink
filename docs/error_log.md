# Registro de Errores y Soluciones

## Configuración Docker + Nginx + Vue (08/02/2026)

### Error: Nginx no inicia con enlace simbólico existente

**Descripción del Problema:**
Durante la construcción del Dockerfile, el comando `ln -s` fallaba porque ya existía un enlace simbólico en `/etc/nginx/sites-enabled/default`.

**Síntomas:**
```bash
ERROR [stage-0 15/19] RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
failed to create symbolic link '/etc/nginx/sites-enabled/default': File exists
```

**Causa Raíz:**
Nginx viene con una configuración por defecto que debe ser removida antes de crear nuestro enlace simbólico personalizado.

**Solución:**
```dockerfile
RUN rm -f /etc/nginx/sites-enabled/default && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
```

---

### Error: Directiva nginx `fastcgi_include` no reconocida

**Descripción del Problema:**
Nginx fallaba al iniciarse con el error "unknown directive fastcgi_include".

**Síntomas:**
```bash
2026/02/08 13:34:18 [emerg] 14#14: unknown directive "fastcgi_include" in /etc/nginx/sites-enabled/default:25
nginx: configuration file /etc/nginx/nginx.conf test failed
```

**Causa Raíz:**
La directiva correcta es `include`, no `fastcgi_include`.

**Solución:**
```nginx
location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;  # Corregido de fastcgi_include
}
```

---

### Error: 502 Bad Gateway - Comunicación PHP-FPM

**Descripción del Problema:**
Nginx respondía con 502 Bad Gateway porque no podía comunicarse con PHP-FPM.

**Síntomas:**
```bash
HTTP/1.1 502 Bad Gateway
Server: nginx
```

**Causa Raíz:**
La configuración inicial usaba un socket Unix (`unix:/var/run/php/php8.4-fpm.sock`) que no se estaba creando. PHP-FPM en Docker por defecto usa TCP.

**Solución:**
```nginx
location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;  # Cambiado de socket Unix a TCP
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

---

### Error: 500 Internal Server Error - Permisos de storage

**Descripción del Problema:**
Laravel devolvía error 500 porque no podía escribir en `storage/framework/views/`.

**Síntomas:**
```bash
HTTP/1.1 500 Internal Server Error
file_put_contents(/var/www/html/storage/framework/views/9745f6a6f3fcc1ddd95648c9a006bc71.php): Failed to open stream: Permission denied
```

**Causa Raíz:**
Los permisos de los directorios `storage` y `bootstrap/cache` no estaban configurados correctamente para el usuario `www-data`.

**Solución:**
```bash
# En el script de inicio del contenedor
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

---

### Error: Assets cargan desde servidor de desarrollo Vite

**Descripción del Problema:**
La aplicación intentaba cargar assets desde `http://127.0.0.1:5173/` en lugar de usar los assets compilados en `/build/`.

**Síntomas:**
```javascript
GET http://127.0.0.1:5173/@vite/client net::ERR_CONNECTION_REFUSED
GET http://127.0.0.1:5173/resources/js/app.ts net::ERR_CONNECTION_REFUSED
```

**Causa Raíz:**
Existía un archivo `public/hot` que le indica a Laravel que use el servidor de desarrollo de Vite en lugar de los assets compilados.

**Solución:**
```bash
# Eliminar archivo hot para forzar uso de assets compilados
docker exec container_name rm -f /var/www/html/public/hot
docker exec container_name php artisan view:clear
```

**Script automatizado en start-container.sh:**
```bash
# Verificar assets compilados
if [ -d "/var/www/html/public/build" ]; then
    echo "✅ Assets compilados encontrados"
    rm -f /var/www/html/public/hot  # Eliminar archivo hot automáticamente
else
    echo "❌ Assets no encontrados, ejecutando build..."
    npm run build
fi
```

---

### Configuración final optimizada

**Dockerfile mejorado:**
- Instalación de nginx + supervisor en un solo contenedor
- Configuración correcta de permisos
- Eliminación automática de archivos conflictivos
- Build automático de assets

**Script de inicio inteligente:**
- Corrección automática de permisos
- Limpieza de cachés
- Verificación de assets
- Eliminación de archivo `hot`
- Ejecución de migraciones

**Docker-compose simplificado:**
- Un solo contenedor para la aplicación (nginx + php-fpm)
- Red Docker interna para comunicación con MariaDB
- Variables de entorno optimizadas para producción

---

### Error: 413 Request Entity Too Large - Upload de archivos multimedia

**Descripción del Problema:**
Al intentar subir imágenes o archivos multimedia a la aplicación, el servidor respondía con error 413 "Content Too Large", impidiendo el upload de cualquier archivo que excediera los límites por defecto.

**Síntomas:**
```bash
HTTP/1.1 413 Request Entity Too Large
nginx/1.18.0 (Ubuntu)
```

**Causa Raíz:**
Nginx y PHP tenían configuraciones muy restrictivas para el tamaño de archivos:
- Nginx `client_max_body_size` por defecto: 1MB
- PHP `upload_max_filesize` por defecto: 2MB  
- PHP `post_max_size` por defecto: 8MB
- Timeouts muy cortos para uploads largos

**Solución:**

**1. Configuración de Nginx optimizada (`docker/app.conf`):**
```nginx
# Configuración para archivos grandes y multimedia
client_max_body_size 100M;
client_body_timeout 300s;
client_header_timeout 300s;
client_body_buffer_size 128k;
large_client_header_buffers 4 256k;

# Timeouts para uploads largos
send_timeout 300s;
proxy_connect_timeout 300s;
proxy_send_timeout 300s;
proxy_read_timeout 300s;
```

**2. Configuración de PHP personalizada (`docker/php-uploads.ini`):**
```ini
# Configuración para uploads de archivos grandes
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
max_file_uploads = 20

# Buffer para archivos grandes
output_buffering = 4096
max_input_vars = 3000
```

**3. Modificación del Dockerfile:**
```dockerfile
# Copiar configuración PHP personalizada
COPY docker/php-uploads.ini /usr/local/etc/php/conf.d/uploads.ini
```

**4. Script de verificación en `start-container.sh`:**
```bash
# Verificar configuración de PHP para uploads
echo "📁 Configuración PHP upload_max_filesize: $(php -r 'echo ini_get("upload_max_filesize");')"
echo "📁 Configuración PHP post_max_size: $(php -r 'echo ini_get("post_max_size");')"
```

**Verificación:**
Después de aplicar los cambios, la aplicación puede manejar:
- ✅ Archivos hasta 100MB
- ✅ Uploads con timeout de 5 minutos
- ✅ Hasta 20 archivos simultáneos
- ✅ Compatible con Cloudflare proxy

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
