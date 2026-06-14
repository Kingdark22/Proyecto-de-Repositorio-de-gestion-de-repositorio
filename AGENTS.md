# Session Summary — Jun 9, 2026

## Problems Solved

### 1. `org_nombre_contacto` cannot be null
- **File**: `app/Livewire/OrganizacionManager.php`
- **Fix**: Changed null fallback for `nombre_contacto`, `apellido_contacto`, `numero_contacto` from `null` to `'-'` in `guardarOrg()`.

### 2. `org_correo` column missing from DB
- **Migration**: `database/migrations/2026_06_05_100000_add_org_correo_to_organizacion_table.php`
- **Status**: ✅ Run
- Adds `org_correo` (varchar 255, nullable) to `organizacion` table.

### 3. `com_codigo` FK missing from `comunidad_contactos`
- **Migration**: `database/migrations/2026_06_05_100001_add_com_codigo_to_comunidad_contactos_table.php`
- **Status**: ✅ Run
- Adds `com_codigo` (bigint, NOT NULL, cascade on delete) to `comunidad_contactos`.

### 4. Academic fields (`trayecto`, `programa`, `seccion`) removed from `Comunidad`
- These columns don't exist in DB (user removed them — data comes from intranet).
- **Removed from**: `config/repositorio_schema.php`, `app/Models/Comunidad.php` (fillable), `app/Services/ComunidadGestionService.php` (validation, form-load, save queries), `app/Livewire/ComunidadManager.php` (properties, rules, reset, save), `resources/views/livewire/comunidad-manager.blade.php` (form selects, table columns).
- `ComunidadGestionService::datosVistaFormulario()` signature changed — removed `$programaId` parameter.
- Removed `Programa`, `Seccion`, `DbHelper`, `Collection`, `LengthAwarePaginator` imports from service.

### 5. Encargado fields removed from `Comunidad`
- `com_nombre_encargado`, `com_apellido_encargado`, `com_telefono_encargado` columns removed from DB by user.
- **Removed from**: `config/repositorio_schema.php` (encargado mappings), `app/Models/Comunidad.php` (fillable), `app/Livewire/ComunidadManager.php` (properties, reset, save, `guardarContactosAhora`), `resources/views/livewire/comunidad-manager.blade.php` (encargado section).

### 6. `ComunidadContacto` primary key wrong
- **File**: `app/Models/ComunidadContacto.php`
- **Fix**: `protected $primaryKey = 'ccon_codigo'` → `'ccom_codigo'`.

### 7. Custom cargo in contactos
- **Config**: `config/comunidades.php` — `cargos_contacto` list: `['responsable', 'autoridad']`.
- **View**: Select includes `"+ Otro (escribir)..."` option; when selected, shows text input.
- **Livewire**: `setCargoSeleccion()` toggles input; `normalizarContactos()` ensures custom cargo is stored in `ccon_cargo`.
- **Service**: `cargarParaEdicion()` detects custom cargos (not in canonical list) and sets `cargo_custom`.

### 8. Migration `create_grupo_proyecto_modulo_table` was pending
- **File**: `database/migrations/2026_05_26_100000_create_grupo_proyecto_modulo_table.php`
- **Status**: ✅ Ran (was `Pending`, now in Batch 3)
- **Root cause**: Table didn't exist → `tablaDisponible()` returned `false` → form never loaded → register button did nothing.

### 9. `UserRoleService::userHasRole()` — also check available roles
- **File**: `app/Services/UserRoleService.php`
- **Fix**: When active session role doesn't match the required role, now also checks user's available detected roles before returning false. E.g., admin user with `gestionador` in available roles can access `role:gestionador` routes.

### 10. `DbHelper` timeout — fsockopen probe before PDO
- **File**: `app/Helpers/DbHelper.php`
- **Fix**: CACHE_TTL reduced 86400→60; checks `intranetAlcanzable()` (fsockopen 300ms) first; `SET statement_timeout` failure is non-fatal.

### 11. Classification fields removed from Proyecto payload/DB
- **File**: `app/Services/ProyectoGestionService.php` — removed `lin_codigo`, `mei_codigo`, `tpu_codigo`, `tin_codigo` from `guardar()` payload, `datosVistaFormulario()`, and `reglasValidacion()`.
- **Migration**: `2026_06_08_091237_make_classification_fields_nullable.php` — made those 4 columns nullable (executed).

### 12. `GrupoProyectoService::decodificarContexto()` — ArrayObject detection
- **File**: `app/Services/GrupoProyectoService.php`
- **Fix**: Added `ArrayObject`/`AsArrayObject` detection before `is_string()` check — model cast was wrapping JSON so `is_string()` always returned false.

### 13. Document serving — dedicated route vs `Storage::url()`
- **File**: `routes/web.php` — added `/documentos/{path}` route with `auth` middleware
- **File**: `resources/views/livewire/proyectos-publicados-manager.blade.php` — all document links use `route('documentos.serve')` instead of `Storage::url()` (which broke due to APP_URL misconfiguration in XAMPP subdirectory).

### 14. Publicaciones detail view — independent project loading
- **File**: `app/Livewire/ProyectosPublicadosManager.php` — `render()` now loads `$selectedProyecto` via `Proyecto::find()` (independent of filtered `$proyectos` collection), preventing blank detail when search/filter changes.
- **File**: `resources/views/livewire/proyectos-publicados-manager.blade.php` — uses `$selectedProyecto` instead of `$proyectos->firstWhere()`.

### 15. `negocio` role removed
- **File**: `config/roles.php` — deleted from labels, module_buttons, and aliases.

### 16. Rich HTML email with full project details + all PDF attachments
- **File**: `app/Livewire/ProyectosPublicadosManager.php`
- **Fix**: Changed from `Mail::raw()` (plain text) to `Mail::html()` with a full HTML body built by `buildEmailHtml()`.
- **HTML body includes**: user's custom message, project title, resumen, fecha, comunidad, document list, and optionally comments (controlled by `$includeComments` checkbox).
- **All PDFs attached**: main project PDF (`archivo_path`) + all component documents (`pry_documentos.*.archivo_path`), each with a sanitized filename.
- **Property**: `$includeComments` (bool, default `true`).
- **UI**: Checkbox in email panel: "Incluir comentarios del proyecto en el correo".

### 17. Email comments are user-written (not auto-fetched from DB)
- **File**: `app/Livewire/ProyectosPublicadosManager.php`
- **Change**: Removed `$includeComments` property and `ComentarioProyecto` query from `buildEmailHtml()`. The `emailBody` textarea serves as the user's optional comments.
- **`emailBody` validation**: changed from `required|min:10` to `nullable|max:5000` — comments are optional.
- **UI**: Label changed from "Mensaje:" to "Comentarios:" with placeholder "Comentarios opcionales...".
- **Email body**: user's comments shown under "Comentarios:" heading only when non-empty; no auto-fetched DB comments.

### 18. Gestionar Organizaciones CRUD removed
- **Files deleted**: `app/Livewire/OrganizacionManager.php`, `resources/views/livewire/organizacion-manager.blade.php`, `resources/views/organizaciones/` (entire directory)
- **Files modified**: `routes/web.php` — removed `/organizaciones` route; `resources/views/components/sidebar.blade.php` — removed "Vinculación" menu; `app/Support/NavigationMenu.php` — removed `canManageOrganizaciones` flag; `config/repositorio_schema.php` — removed `organizacion` schema mapping
- **Models kept**: `Organizacion`, `OrgContacto` — still used by publicaciones email panel (reading org/contact data for sending project PDFs)

### 19. Button restructuring by role in Gestión de Proyectos
- **File**: `app/Services/ProyectoGestionService.php` — `usuarioPuedeRegistrar()` now returns true for `profesor proyecto` role; added `proyectoIdsLideradosPor()` and `paginarProyectosLider()` methods; `datosVistaListado()` accepts `$onlyLeaderProjects` flag.
- **File**: `app/Livewire/ProyectoManager.php` — `usuarioEsLider()` now also excludes `profesor proyecto` (not just admin/coordinador). `render()` passes `$esLiderGlobal` as `$onlyLeaderProjects` to `datosVistaListado()`, so leaders only see their own projects.
- **File**: `resources/views/livewire/proyecto-manager.blade.php` — "REGISTRAR NUEVO PROYECTO" button now shows for `$esAdmin || ($canRegister && !$esLider)`, hiding it from leaders.
- **Result**: Admin & Profesor proyecto → "Registrar" button, full CRUD, validate tab. Leaders (students) → only see their leader projects with "Actualizar" button. No "Registrar" button, no "Validar" tab for leaders.

## Key Patterns
- `MapsLegacyColumns` trait only works on Model instances (after `get()`). The `LegacyColumnBuilder` only overrides `where()` and `orderBy()` — all other QB methods (`whereIn`, `whereNotNull`, `whereNull`, `pluck`, `select`, `groupBy`, `update`, `delete`, etc.) bypass the mapping.
- **Fix rule**: For `whereIn()`, `whereNotNull()`, `whereNull()` — use the **physical column name** directly.
- **Fix rule**: For `update()` on QB — fetch model instance first, then `->fill($data)->save()`.
- **Fix rule**: For `pluck()` on QB — use `->get()->pluck('col')` (Collection pluck uses model accessor).
- **Fix rule**: For `select()` on QB — use physical column names, or fetch model and access attributes.
- Academic data (trayecto, programa, seccion, encargado) comes from intranet PostgreSQL — not stored in MySQL repositorio.
- `comunidad_contactos.ccon_cargo` is a varchar — custom cargos stored directly, no separate cargos table.
- `create_grupo_proyecto_modulo_table` migration must be run before users can register project teams.
- Document links must use dedicated route (`/documentos/{path}`), not `Storage::url()`, when APP_URL lacks subdirectory path.
- For detail views in filtered lists, load the selected item via Model::find() (independent of the list query) to prevent disappearing when search/filter changes.
- `AsArrayObject` model casts require `instanceof ArrayObject` check before `json_decode` in service code.
- DbHelper: fsockopen (300ms) before PDO (2s) for intranet reachability — `SET statement_timeout` failure is non-fatal.
- `userHasRole()` should check both active session role AND available detected roles.
- **Leader selection in ProyectoManager**: Professor selects up to 2 leaders when registering a group project. `$selectedLeaders` (cedulas array), `$miembrosGrupo` (member list), `toggleLider()` toggles selection with max-2 enforcement. `cargarMiembrosGrupo()` pre-selects existing leaders from group roles.
- **Leader assignment on save**: `ProyectoGestionService::guardar()` accepts `$leaders` param. When creating a new project (non-admin), `asignarLideresGrupo()` delegates to `GrupoProyectoService::asignarLideres()` which updates `grp_miembros` JSON on `grupo_proyecto_modulo`.
- **Notification for leaders**: `NotificacionService` shows "Has sido seleccionado como líder del proyecto. Sube los documentos: {title}" for projects with `actualizado_por_estudiante = false` where the current user is a leader (checks `GrupoProyectoModulo::grp_miembros` JSON for cedula with `rol_id = 1`).
- **Validation**: At least 1 leader required when registering a new group project (EQGRP:).
