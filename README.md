# Repositorio institucional UPTP (SOGAC)

Módulo Laravel para gestión y consulta de proyectos comunitarios, con datos académicos en **intranet** y datos del módulo en MySQL **repositorio**.

## Requisitos

- PHP 8.2+, Composer, MySQL, acceso a PostgreSQL intranet (o simulación MySQL)
- Extensiones PHP: pdo_mysql, pdo_pgsql

## Configuración

1. Copiar variables de entorno (`.env` no se versiona).
2. `composer install`
3. `php artisan key:generate`
4. `php artisan config:clear`

No usar `php artisan migrate` para tablas académicas: deben existir en intranet/simulación (ver `database/README.md`).

## Acceso

- Público: `/repositorio`
- Sistema: enlace mágico → `php artisan app:generate-login-link {cedula}`
- Simular rol en sesión: menú **Simular rol** (`ROLES_ALLOW_FREE_SESSION=true` por defecto)

## Arquitectura de datos

| Base | Contenido |
|------|-----------|
| **Intranet** | Solo **lectura** académica (personas, secciones, inscripciones, lapsos, SUD). **No** se inserta ni actualiza desde el módulo. |
| **Simulación** | Respaldo: copia **solo** de lo leído en intranet (`app:mirror-intranet-user`). Si intranet cae, `DbHelper` lee simulación. |
| **Repositorio** | **Escritura** del módulo: proyectos, comunidades, `grupo_proyecto_modulo`, `profesor_proyecto_modulo`, catálogos. |

Sin FK entre bases: se relacionan en PHP (`ConexionDualService`) por cédula, lapso, sección y claves `EQGRP`.

**Lapsos:** solo lectura en intranet. **Grupos/equipos:** `grupo_proyecto_modulo`. **Catálogos** (coordinaciones, líneas, metodologías, tipos): MySQL repositorio vía `RepositorioModel` + `repositorio_schema.php` + `ModuloRepositorioService`.

## Comandos útiles

```bash
php artisan app:inspect-databases
php artisan app:generate-login-link 12345678
php artisan app:mirror-intranet-user {cedula}   # intranet → simulación (no escribe repositorio)
```

## Roles y validaciones (sin tabla `roles`)

Ver [docs/ESTRUCTURA_ROLES_Y_VALIDACIONES.md](docs/ESTRUCTURA_ROLES_Y_VALIDACIONES.md): perfiles en sesión, intranet (lapso, malla, UC proyecto, inscripción) y tablas repositorio.

## Estructura relevante

- `app/Services/UserRoleService.php` — roles en sesión
- `app/Services/IntranetProfessorService.php` — docentes por lapso (intranet)
- `app/Support/NavigationMenu.php` — permisos del menú lateral
- `config/dual_database.php` — tablas intranet vs repositorio
- `config/repositorio_schema.php` — mapeo columnas legacy → esquema `pry_*`, `com_*`
