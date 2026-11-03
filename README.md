# Recetario de Cocina Digital

Proyecto semestral TIS-1, Ingeniería Civil Informática, UCSC.
Aplicación web hecha con Laravel y PostgreSQL para ingresar, buscar, valorar y consultar recetas de cocina de una familia.

## Requisitos

- Laragon 8.7 o superior (incluye PHP, Composer y una terminal)
- PHP 8.3 o superior, con las extensiones `pdo_pgsql` y `pgsql` activadas en el `php.ini`
- Composer
- PostgreSQL 14 o superior (probado con la 18) y pgAdmin
- Navegador: Chrome 110+, Firefox 110+ o Safari 16+
- Conexión a internet (Bootstrap y la fuente se cargan desde un CDN)

El proyecto usa Laravel 13, que se instala con `composer install`.

## Instalación

Todos los comandos se ejecutan en la terminal de Laragon, dentro de la carpeta del proyecto.

1. **Entrar a la carpeta e instalar las dependencias:**
   ```bash
   cd ruta\a\Proyecto-TIS-1
   composer install
   ```

2. **Crear la base de datos** en pgAdmin (o psql) con el nombre `Proyecto_TIS1`, respetando mayúsculas y minúsculas.

3. **Importar el script con las tablas y los datos de prueba**, `Base_de_datos/Recetario_base_de_datos.sql`. Hay dos formas:
   - En pgAdmin: clic derecho en `Proyecto_TIS1`, luego Query Tool, abrir el archivo y ejecutarlo.
   - Con psql:
     ```bash
     psql -U postgres -d Proyecto_TIS1 -f Base_de_datos/Recetario_base_de_datos.sql
     ```

   La búsqueda y el ingreso de recetas comparan nombres sin distinguir tildes, y para eso necesitan la extensión `unaccent`. Si el script no la crea, ejecutar en la base:
   ```sql
   CREATE EXTENSION IF NOT EXISTS unaccent;
   ```

4. **Crear el archivo de configuración:**
   ```bash
   copy .env.example .env
   ```
   En Linux o macOS se usa `cp .env.example .env`.

5. **Editar el `.env`** con los datos de tu PostgreSQL. Además de la conexión, hay que cambiar tres líneas, porque la base no tiene tablas de sesiones, caché ni colas:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=Proyecto_TIS1
   DB_USERNAME=postgres
   DB_PASSWORD=tu_contraseña

   SESSION_DRIVER=file
   CACHE_STORE=file
   QUEUE_CONNECTION=sync
   ```
   El `.env` es personal de cada computador y no se sube a GitHub.

6. **Generar la clave de la aplicación:**
   ```bash
   php artisan key:generate
   ```
7. **Iniciar el servidor:**
   ```bash
   php artisan serve
   ```
   Abrir http://127.0.0.1:8000

### Comprobar que la conexión funciona

```bash
php artisan config:clear
php artisan db:show
```
Debe mostrar PostgreSQL, la base `Proyecto_TIS1` y sus tablas.

## Funcionalidades

| Requisito | Responsable | Ruta | Controlador | Vista |
|---|---|---|---|---|
| R.1 Ingreso de recetas | Bárbara González | `/ingreso` | `IngresoController` | `Ingreso_receta` y `ver_receta` |
| R.2 Valoración de recetas | Bastián Núñez | `/valorar` | `CalificacionController` | `valorar_receta` |
| R.3 Recetas populares | Diego Burgos | `/top10` | `TopController` | `top10` |
| R.4 Ajuste de ingredientes | Edinson Ramos | `/ajuste-ingredientes` | `ajuste_ingredientesController` | `ajuste_ingredientes` |
| R.5 Búsqueda y filtrado | Leandro Torres | `/buscar` | `BusquedaController` | `buscar` |

Otras rutas:

| Ruta | Descripción |
|---|---|
| `/` | Página de inicio |
| `/lista` | Lista de todas las recetas |
| `/mostrar/{id}` | Detalle de una receta |
| `/receta/{id}/{nombre}` | Página de la receta recién ingresada (la que abre `LINK_RECETA`) |

## Solución de problemas

| Mensaje o síntoma | Causa y solución |
|---|---|
| `No application encryption key has been specified` | Falta la clave: ejecutar `php artisan key:generate` |
| `Database file at path [...database.sqlite] does not exist` | El `.env` sigue con SQLite: poner `DB_CONNECTION=pgsql` y quitar el `#` de las líneas `DB_` |
| `could not find driver` | Activar `pdo_pgsql` y `pgsql` en el `php.ini` y reiniciar la terminal |
| `la autentificación password falló` | `DB_PASSWORD` no es la contraseña de PostgreSQL de este computador |
| `database "Proyecto_TIS1" does not exist` | Falta crear la base (paso 2) |
| `relation "sessions" does not exist` | `SESSION_DRIVER`, `CACHE_STORE` y `QUEUE_CONNECTION` deben ser `file`, `file` y `sync` |
| `function unaccent(...) does not exist` | Ejecutar `CREATE EXTENSION IF NOT EXISTS unaccent;` en la base |
| La imagen de la receta aparece rota | Falta `php artisan storage:link`, o la receta se ingresó en otro computador |
| Error al subir una imagen de más de 2 MB | Aumentar `upload_max_filesize` y `post_max_size` en el `php.ini` (paso 8) |
| `Route [...] not defined` o una página no abre | Ejecutar `php artisan route:clear` y revisar `routes/web.php` |
| La vista muestra algo antiguo | Ejecutar `php artisan view:clear` |

## Integrantes

- Diego Burgos
- Bárbara González
- Bastián Núñez
- Edinson Ramos
- Leandro Torres
