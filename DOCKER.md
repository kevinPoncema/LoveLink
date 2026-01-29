# LoveLink Docker Setup

## Levantar el proyecto

```bash
docker compose up -d
```

## Comandos útiles

### Ver logs
```bash
docker compose logs -f app
docker compose logs -f node
docker compose logs -f nginx
```

### Acceder a la aplicación
- **Frontend**: http://localhost
- **API**: http://localhost/api
- **Vite Dev**: http://localhost:5173

### Ejecutar comandos Artisan
```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan seed
docker compose exec app php artisan tinker
```

### Compilar assets
```bash
docker compose exec node npm run build
```

### Detener el proyecto
```bash
docker compose down
```

### Eliminar volúmenes (CUIDADO: elimina datos)
```bash
docker compose down -v
```

## Servicios

- **app** (PHP 8.4-FPM): Servidor PHP en puerto 8000
- **nginx**: Reverse proxy en puerto 80
- **redis**: Cache y sesiones en puerto 6379
- **node**: Vite dev server en puerto 5173

## Variables de entorno

Las variables están configuradas en `docker-compose.yml`. Puedes personalizarlas editando el archivo o creando un `.env.docker`.

## Base de datos

SQLite se usa por defecto. La base de datos se crea automáticamente en `database/database.sqlite`.

## Notas de desarrollo

- Los cambios en el código se reflejan automáticamente (hot reload)
- Vite HMR está configurado en el puerto 5173
- Redis está disponible para caching
- Los logs están en `storage/logs/`
