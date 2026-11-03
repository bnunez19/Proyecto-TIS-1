# Recetario de Cocina Digital

Proyecto semestral **TIS-1** · Ingeniería Civil Informática · Universidad Católica de la Santísima Concepción (UCSC)

Aplicación web para que un grupo familiar registre, valore, busque y adapte recetas de cocina. Está desarrollada con **Laravel 13** y **PostgreSQL**, y se ejecuta localmente usando **Laragon**.

---

## Índice

1. [Funcionalidades](#funcionalidades)
2. [Tecnologías](#tecnologías)
3. [Requisitos previos](#requisitos-previos)
4. [Instalación paso a paso](#instalación-paso-a-paso)
5. [Ejecución](#ejecución)
6. [Integrantes](#integrantes)

---

## Funcionalidades

| Requisito | Ruta | Descripción | Responsable |
|---|---|---|---|
| — | `/` | Página de inicio con acceso a todas las funciones | — |
| — | `/lista` | Listado general de recetas (orden alfabético) | — |
| — | `/receta/{id}` | Detalle completo de una receta | — |
| **R.1** | `/ingreso` | Ingreso de recetas (ingredientes, pasos, imagen, categoría y régimen) | Bárbara González |
| **R.2** | `/valorar` | Valoración de recetas con calificación de 1 a 5 por usuario | Bastián Núñez |
| **R.3** | `/top10` | Las 10 recetas más populares por categoría | Diego Burgos |
| **R.4** | `/ajuste-ingredientes` | Ajuste de cantidades de ingredientes según el número de personas | Edinson Ramos |
| **R.5** | `/buscar` | Búsqueda y filtrado por nombre, categoría y régimen alimenticio | Leandro Torres |

---

## Tecnologías

- **Backend:** PHP 8.3 + Laravel 13 (Eloquent ORM)
- **Base de datos:** PostgreSQL (el esquema se crea directamente con un script SQL, **sin migraciones**)
- **Frontend:** Blade, HTML5, CSS3, JavaScript y Bootstrap 5.3 (cargado desde CDN)
- **Entorno de desarrollo:** Laragon (Windows)

---

## Requisitos previos

| Software | Versión | Notas |
|---|---|---|
| [Laragon](https://laragon.org/download/) | 8.7 o superior | Incluye PHP, Composer y terminal |
| PHP | 8.3 o superior | Con la extensión **`pdo_pgsql`** activada |
| Composer | 2.x | Viene incluido en Laragon |
| PostgreSQL | 14 o superior | Probado con la versión 18 |
| Navegador | Chrome 110+, Firefox 110+ o Safari 16+ | |
| Internet | — | Necesario para cargar Bootstrap desde el CDN |

> **Importante:** Laragon no incluye PostgreSQL por defecto. Debe instalarlo por separado (o agregarlo a Laragon) y tener un cliente como **pgAdmin** o **psql** para crear e importar la base de datos.

### Activar `pdo_pgsql` en Laragon

1. Clic derecho sobre Laragon → **PHP** → **Extensiones**.
2. Marcar `pdo_pgsql` y `pgsql`.
3. Reiniciar Laragon (**Detener todo** → **Iniciar todo**).

Para verificar, en la terminal de Laragon ejecute:

```bash
php -m | findstr pgsql
```

Debe aparecer `pdo_pgsql` y `pgsql`.

---

## Instalación paso a paso

### 1. Obtener el proyecto

Descomprima (o clone) el proyecto dentro de la carpeta `www` de Laragon, por ejemplo:

```
C:\laragon\www\Proyecto-TIS-1
```

### 2. Instalar dependencias

Abra la **terminal de Laragon** (botón *Terminal*) y ejecute:

```bash
cd C:\laragon\www\Proyecto-TIS-1
composer install
```

### 3. Crear el archivo de configuración y la clave de la aplicación

```bash
copy .env.example .env
php artisan key:generate
```

> En la terminal de Laragon (Cmder) también funciona `cp .env.example .env`.

### 4. Crear la base de datos

Desde **pgAdmin** (clic derecho en *Databases* → *Create* → *Database*) o desde **psql**, cree una base de datos llamada:

```
Proyecto_TIS1
```

Con psql:

```sql
CREATE DATABASE "Proyecto_TIS1";
```

> Las comillas son necesarias porque el nombre contiene mayúsculas.

### 5. Importar la base de datos con los datos de prueba

El script `Recetario_base_de_datos.sql` (carpeta `Base_de_datos/`) crea las tablas y carga recetas, ingredientes, pasos, regímenes y usuarios de ejemplo.

**Opción A: desde la terminal**

```bash
psql -U postgres -d Proyecto_TIS1 -f Base_de_datos/Recetario_base_de_datos.sql
```

**Opción B: desde pgAdmin**

Clic derecho sobre `Proyecto_TIS1` → **Query Tool** → abrir el archivo `.sql` → ejecutar.

### 6. Configurar el archivo `.env`

Abra `.env` y ajuste **estas líneas** (reemplace `TU_CONTRASEÑA` por la de su PostgreSQL):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=Proyecto_TIS1
DB_USERNAME=postgres
DB_PASSWORD=TU_CONTRASEÑA

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

> **No omita las tres últimas líneas.** Por defecto Laravel guarda sesiones, caché y colas en la base de datos, pero el script SQL del proyecto no crea esas tablas. Sin este cambio, la aplicación fallará con errores del tipo `relation "sessions" does not exist`.

Después de editar `.env`, limpie la caché de configuración:

```bash
php artisan config:clear
```

---

## Ejecución

En la terminal de Laragon, dentro de la carpeta del proyecto:

```bash
php artisan serve
```

Abra en el navegador: **http://localhost:8000**

Para detener el servidor, presione `Ctrl + C`.

> **Alternativa con Laragon:** si deja el proyecto en `C:\laragon\www`, Laragon puede crear un host virtual automático (por ejemplo `http://proyecto-tis-1.test`). En ese caso debe apuntar a la carpeta `public` del proyecto.

---

## Integrantes

- Diego Burgos
- Bárbara González
- Bastián Núñez
- Leandro Torres
- Edinson Ramos

**Profesor:** Pedro Rossel Cid
**Entrega:** Noviembre de 2026
