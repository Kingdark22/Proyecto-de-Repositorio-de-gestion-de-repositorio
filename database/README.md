# Base de datos

**No ejecutar migraciones** que dupliquen tablas de **intranet** en repositorio.

| Dónde | Qué |
|-------|-----|
| Intranet | Datos académicos — solo lectura desde Laravel |
| Simulación | Espejo opcional intranet→simulación |
| Repositorio | Registros del módulo (sin FK hacia intranet) |

Tablas propias del módulo en repositorio (relación lógica por cédula/lapso/sección en código): `grupo_proyecto_modulo`, `coordinaciones`, `linea_investigacions`, `metodologia_investigacions`, `tipo_investigacions`, `proyectos`, etc.

Migración de catálogos: `database/migrations/2026_05_28_100000_create_modulo_catalog_tables.php`

Mapeo columnas lógicas ↔ físicas: `config/repositorio_schema.php`. Servicio: `App\Services\ModuloRepositorioService`.
