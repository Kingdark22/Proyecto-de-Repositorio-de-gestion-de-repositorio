# Estructura de acceso (sin crear roles en BD)

El repositorio **no usa** tabla `roles` de Laravel ni roles nuevos en MySQL. El acceso se modela así:

## 1. Identidad

| Fuente | Uso |
|--------|-----|
| Intranet `usuario` + `persona` | Login (cédula `usu_cedula`) |
| MySQL `repositorio` | Proyectos, comunidades, catálogos, auditorías |

## 2. “Roles” = perfil en sesión

Claves fijas en `config/roles.php` (solo etiquetas de menú y middleware):

| Clave sesión | Cómo se detecta (`UserRoleService::detectAvailableRoles`) |
|--------------|-------------------------------------------------------------|
| `administrador` | `usuario.usu_nombre` PROGRAMADOR/admin o `usu_cod_rol` mapeado a 1 |
| `coordinador` | `usuario.usu_cod_rol` mapeado a 2 (departamento) |
| `estudiante` | Existe fila en `estudiante` (intranet) |
| `profesor proyecto` | `IntranetProfessorService::esProfesorProyectoVigente()` |

### Profesor de proyecto (validación real)

No basta con ser docente. Debe cumplir en **lapso vigente** (`lap_estatus = A`, el más reciente):

1. Lapso activo más reciente (`lap_estatus = A`, mayor `lap_codigo`)
2. `seccion_unidad_docente` activa (`sud_estatus = A`) en UC de proyecto (`AGPFI*`, `AGPFII*`, …)
3. **Solo la asignación vigente** por sección + UC: la fila con mayor `sud_codigo` (docente actual, no históricos)
4. Malla/trayecto vía `seccion` → `malla` → `trayecto`

Opcional: tabla `profesor_proyecto_modulo` (si existe en repositorio) marca `ppm_habilitado` por lapso; **no** es un rol.

### Simular rol

`ROLES_ALLOW_FREE_SESSION=true` (por defecto): puede elegir **cualquier** rol del módulo (Estudiante, Admin, Coordinación, Docente) y el menú/permisos siguen ese rol en sesión, sin exigir que intranet lo detecte. Con `false`, solo roles realmente detectados en intranet.

## 3. Lapsos académicos

**No se gestionan** en el módulo. Se leen de intranet `lapso_academico` (`LapsoAcademico::activos()`, `::vigente()`).

## 4. Grupo de proyecto y equipo (encapsulación)

| Concepto | Qué es |
|----------|--------|
| **Grupo de proyecto** | Conjunto de estudiantes creado para elaborar un expediente (líder, autores). En intranet: `grupo_proyecto` / `grupo_proyecto_estudiante` cuando existan en la BD. |
| **Equipo** | Encapsulación de ese grupo en el repositorio: lo que ve el módulo y lo que se guarda en `proyectos.pry_direccion_logica`. |

Claves:

- `EQGRP:{id}` — grupo **registrado en el módulo** (tabla `grupo_proyecto_modulo` en repositorio; integrantes elegidos de la sección).
- `EQSEC:{lap}:{sec}` — referencia a sección (todos los inscritos activos) si no hay grupo formal en intranet.

Flujo: **Equipos de proyecto** → lapso + PNF + sección → elegir estudiantes (líder + autores) → **Registrar grupo** → más tarde **Registrar proyecto** y elegir ese `EQGRP`.

El **líder** del grupo (`rol_id = 1`) puede registrar el expediente. Servicios: `GrupoProyectoService`, `IntranetEquipoSeccionService`.

## 5. Comunidades (repositorio)

Tabla real: `comunidades` (`com_nombre`, `com_rif`, `com_correo`, `com_numero_telefono`, `com_direccion`, `anio`).

**No existen** en BD: `estados`, `municipios`, `direcciones`, `comunidad_contactos`, `roles`.

| Acción | administrador | coordinador | profesor proyecto | estudiante |
|--------|---------------|-------------|-------------------|------------|
| Listar / buscar | Sí | Sí | Sí | Sí (lectura) |
| Registrar / editar | Sí | Sí | Sí (si vigente UC proyecto) | No |

## 6. Proyectos

| Acción | Quién |
|--------|--------|
| Registrar | Estudiante con inscripción activa o administrador |
| Validar (aprobar/rechazar) | Admin/coordinador: todos los pendientes. Docente: solo expedientes de sus secciones con UC Proyecto (mismo lapso, `EQSEC:lap:sec`). |
| Público repositorio | `pry_estado_` = Aprobado y activo lógico |

## 7. Dos bases de datos (sin relaciones FK entre ellas)

| Base | Uso en el módulo |
|------|------------------|
| **Intranet** (PostgreSQL) | Solo **lectura**: usuario, persona, estudiante, sección, inscripción, lapso, SUD, etc. |
| **Simulación** (MySQL) | Respaldo: se llena **solo** copiando lo leído de intranet (`IntranetSimulationMirrorService`), cuando se solicita o al consultar con intranet activa. |
| **Repositorio** (MySQL) | **Escritura** del módulo: `proyectos`, `comunidades`, `grupo_proyecto_modulo`, `profesor_proyecto_modulo`, catálogos. |

La relación entre bases es en **PHP** (`ConexionDualService`): cédula, contexto académico en `grp_contexto` (JSON, sin columnas FK a intranet), clave `EQGRP:id` en `pry_direccion_logica`. **No** hay claves foráneas cruzadas.

Servicios:

- `App\Services\ConexionDualService` — `consultaAcademica()`, `consultaModulo()`, `espejarLecturaIntranetASimulacion()`.
- `App\Services\ModuloRepositorioService` — catálogos del módulo en repositorio (`coordinaciones`, `linea_investigacions`, `metodologia_investigacions`, etc.) con mapeo `config/repositorio_schema.php`.
- `App\Services\GrupoProyectoService` — grupos `EQGRP` + contexto JSON (sin columnas FK a intranet).

### Catálogos del módulo (repositorio)

| Tabla repositorio | Uso | Relación intranet |
|-------------------|-----|-------------------|
| `coordinaciones` | PNF / coordinación rectora del SOGAC | Ninguna (catálogo local) |
| `linea_investigacions` | Líneas de investigación | `coord_codigo` → `coordinaciones` (misma BD) |
| `metodologia_investigacions`, `tipo_investigacions`, `tipo_publicacions` | Catálogos de registro de proyecto | Ninguna |
| `comunidades`, `proyectos` | Expedientes y comunidades | Lectura de personas/secciones solo al validar o mostrar |
| `grupo_proyecto_modulo` | Grupos `EQGRP` | Contexto académico en JSON; nombres PNF/sección guardados al registrar |

Los modelos extienden `RepositorioModel` y traducen columnas lógicas (`nombre`, `activo`) a columnas físicas (`coord_nombre`, `lin_estado`, …) vía `MapsLegacyColumns`.

## 8. Espejo intranet → simulación (respaldo)

**No se crean tablas nuevas** con migraciones Laravel en MySQL `repositorio` ni copias de tablas que ya están en intranet (`lapso_academico`, `coordinador_coordinacion`, etc.).

La BD `simulacion` debe ser el mismo modelo que intranet (export/restore). El código solo hace `UPDATE/INSERT` en tablas que **ya existen** en simulación/intranet.

Cuando la conexión activa es **intranet** (`INTRANET_MIRROR_TO_SIMULATION=true`):

| Momento | Qué se espeja |
|---------|----------------|
| Login / enlace mágico | Contexto del usuario (solo tablas presentes en simulación) |
| Consultas `DualDatabase` | Filas leídas de tablas académicas existentes en ambas BDs |

Si intranet cae, `DbHelper::connection()` pasa a **simulacion** y las consultas usan el último espejo.

```bash
php artisan app:mirror-intranet-user 31057795
```

## 9. Validación de expedientes (paso 2)

- Listado **Validar pendientes**: si el rol activo es **profesor proyecto**, solo proyectos cuya `pry_direccion_logica` coincide con una sección donde el docente tiene `sud` activo en UC de proyecto.
- **Aprobar / rechazar**: misma regla; si no aplica, mensaje de error en pantalla.

## 10. Rol en sesión (paso 3)

En cada petición, `EnsureActiveRole` comprueba que el rol simulado sigue en `detectAvailableRoles` y que, si es docente, sigue siendo **profesor de proyecto vigente** en intranet; si no, redirige a **Simular rol**.

## 11. Archivos clave

- `app/Services/UserRoleService.php` — detección y sesión
- `app/Services/IntranetProfessorService.php` — docente UC proyecto + lapso
- `app/Services/IntranetEquipoSeccionService.php` — equipo por sección
- `app/Support/NavigationMenu.php` — menú lateral
- `config/repositorio_schema.php` — columnas legacy
