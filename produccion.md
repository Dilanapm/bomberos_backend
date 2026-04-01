Tengo un proyecto Laravel 11 dockerizado que quiero desplegar en producción en un VPS.
Te doy toda la información técnica del proyecto para que me guíes paso a paso.

---

## Stack técnico

- **PHP 8.3-fpm-alpine** (Laravel 11)
- **PostgreSQL 16-alpine** — DB: `bomberos`, user: `dilan_dam`
- **Redis 7-alpine** — sesión, caché y colas
- **Nginx 1.25-alpine** — actualmente HTTP only, sin SSL
- **Node 20-alpine** — Vite (actualmente corre en modo DEV, hay que compilar para prod)
- **Queue worker** — contenedor separado con `php artisan queue:work`
- **Livewire 3** + **Chart.js** (CDN) en el panel admin
- **Sanctum** para API móvil (Flutter)
- **Spatie Permissions** — roles: admin, instructor, aprendiz
- **laragear/webauthn 4.1** — passkeys para admin
- **barryvdh/laravel-dompdf** — generación de PDF

---

## Estructura de archivos clave

docker-compose.yml ← desarrollo (bind mounts, vite dev)
docker/
php/Dockerfile ← PHP 8.3-fpm-alpine
default.conf ← HTTP only, sin SSL, sin gzip
nginx/nginx.conf
redis/redis.conf
src/ ← código Laravel
composer.json
package.json
vite.config.js
---

## docker-compose.yml actual (desarrollo)

```yaml
services:
  nginx:
    image: nginx:1.25-alpine
    ports: ["8081:80"]
    volumes:
      - ./src:/var/www/html          # bind mount (dev only)
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf:ro
    depends_on: [app]
    networks: [bomberos_net]

  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    volumes:
      - ./src:/var/www/html          # bind mount (dev only)
    env_file: .env.docker
    depends_on: [postgres, redis]
    networks: [bomberos_net]

  vite:
    image: node:20-alpine
    working_dir: /var/www/html
    volumes: [./src:/var/www/html]
    command: sh -lc "npm install && npm run dev -- --host 0.0.0.0 --port 5173"
    ports: ["5173:5173"]
    networks: [bomberos_net]

  queue:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    command: php artisan queue:work --tries=3 --timeout=90
    volumes: [./src:/var/www/html]
    env_file: .env.docker
    depends_on: [app, redis]
    networks: [bomberos_net]

  mailpit:
    image: axllent/mailpit:latest
    ports: ["8025:8025", "1025:1025"]
    networks: [bomberos_net]

  postgres:
    image: postgres:16-alpine
    restart: unless-stopped
    ports: ["5433:5432"]
    environment:
      POSTGRES_DB: bomberos
      POSTGRES_USER: dilan_dam
      POSTGRES_PASSWORD: N3w_S3cur3_P@ssw0rd_2026!
    volumes: [postgres_data:/var/lib/postgresql/data]
    networks: [bomberos_net]

  redis:
    image: redis:7-alpine
    command: ["redis-server", "/usr/local/etc/redis/redis.conf"]
    volumes: [./docker/redis/redis.conf:/usr/local/etc/redis/redis.conf:ro]
    networks: [bomberos_net]

networks:
  bomberos_net:
    driver: bridge

volumes:
  postgres_data:
docker/php/Dockerfile actual
FROM php:8.3-fpm-alpine
RUN apk add --no-cache bash curl git unzip libzip-dev oniguruma-dev icu-dev \
    postgresql-dev postgresql-client $PHPIZE_DEPS
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip intl
RUN apk add --no-cache freetype-dev libjpeg-turbo-dev libpng-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install gd
RUN pecl install redis && docker-php-ext-enable redis
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
RUN addgroup -g 1000 www && adduser -G www -g www -s /bin/sh -D -u 1000 www
USER www

default.conf actual
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php;

    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_read_timeout 60;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
Variables de entorno relevantes (actualmente en .env.docker)
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8081
APP_KEY=base64:...   # ya existe, NO regenerar

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=bomberos
DB_USERNAME=dilan_dam
DB_PASSWORD=N3w_S3cur3_P@ssw0rd_2026!

REDIS_HOST=redis
REDIS_PASSWORD=R3d1s_Str0ng_P@ss_2026!
REDIS_PORT=6379

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

WEBAUTHN_ID=localhost
WEBAUTHN_ORIGINS=http://localhost:8081

API_CLIENT_KEY=b268aa0a224d7361174815675b940a16c8c1b047f1b1982270ddf6aa3749bed8

SANCTUM_MOBILE_TOKEN_EXPIRATION=43200
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
Seeders disponibles
RolesAndPermissionsSeeder — crea roles y permisos base
AdminUserSeeder — crea usuario admin (admin@bomberos.local / Admin12345!!)
Admin2UserSeeder — segundo admin de prueba
EppDemoSeeder — datos de demo (NO usar en producción)
EppTestSeeder — datos de prueba (NO usar en producción)
Problemas conocidos que deben resolverse para producción
Sin SSL — nginx solo escucha en HTTP (puerto 80), necesita HTTPS con Let's Encrypt u otro certificado
Vite en modo DEV — el servicio vite corre npm run dev, en prod se debe ejecutar npm run build y servir los assets compilados desde nginx, no correr el servidor Vite
Bind mounts — los volúmenes ./src:/var/www/html son para desarrollo, en prod el código debe estar copiado dentro de la imagen con COPY
Sin OPcache — el Dockerfile no tiene OPcache, necesario en producción
APP_ENV=local / APP_DEBUG=true — deben cambiar a production / false
WEBAUTHN_ID=localhost — debe coincidir exactamente con el dominio real (sin protocolo, sin puerto, solo el hostname)
APP_URL=http://localhost:8081 — debe ser la URL real con HTTPS
Sin restart: always en todos los servicios (queue y app no lo tienen)
Sin health checks en postgres y redis
Sin Laravel caches — en prod se deben ejecutar: config:cache, route:cache, view:cache, event:cache
Sin storage:link — enlace simbólico public/storage → storage/app/public
mailpit — en prod se reemplaza por un SMTP real (Mailtrap, SES, Resend, etc.)
Lo que necesito que me generes
docker/php/Dockerfile.prod — con OPcache, sin $PHPIZE_DEPS, con COPY del código, composer install --no-dev --optimize-autoloader, permisos correctos para www en storage/ y bootstrap/cache/
docker/nginx/default.conf.prod — con SSL (Certbot/Let's Encrypt), redirect HTTP→HTTPS, server_name TU_DOMINIO, gzip, fastcgi_read_timeout aumentado, headers de seguridad completos (incluir HSTS)
docker-compose.prod.yml — sin servicio vite, sin bind mounts (usar el Dockerfile.prod que hace COPY), restart: always en todo, health checks para postgres y redis, puerto 80 y 443, mailpit reemplazado por SMTP real
.env.production.example — todas las variables con los valores correctos para prod (APP_ENV=production, APP_DEBUG=false, APP_URL=https://..., WEBAUTHN_ID=tudominio.com, etc.)
deploy.sh — script de despliegue completo que:
Hace pull del repo
Copia .env.production a .env
Corre npm run build dentro del contenedor o imagen Node temporal
Construye las imágenes: docker compose -f docker-compose.prod.yml build --no-cache
Baja los contenedores anteriores: docker compose -f docker-compose.prod.yml down
Levanta los nuevos: docker compose -f docker-compose.prod.yml up -d
Ejecuta dentro del contenedor app: php artisan migrate --force, php artisan config:cache, php artisan route:cache, php artisan view:cache, php artisan event:cache, php artisan storage:link
Para primer despliegue también corre: php artisan db:seed --class=RolesAndPermissionsSeeder y php artisan db:seed --class=AdminUserSeeder
Instrucciones para obtener el certificado SSL con Certbot en el VPS (antes del primer docker compose up)
Notas sobre WebAuthn/Passkeys: qué exactamente debe coincidir en WEBAUTHN_ID y WEBAUTHN_ORIGINS con el dominio real
El VPS tendrá: Ubuntu 22.04, Docker 24+, Docker Compose v2, dominio propio con DNS apuntando al servidor.