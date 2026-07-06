<?php $__env->startSection('title', $proyecto->exists ? 'Editar: ' . $proyecto->titulo : 'Nuevo proyecto'); ?>
<?php $__env->startSection('header', $proyecto->exists ? 'Actualizar proyecto' : 'Registrar proyecto'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .cm-btn {
        display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;
        padding: 0.5rem 0.9rem; font-size: 0.9rem; font-weight: 600;
        border: 1px solid transparent; cursor: pointer;
        transition: background-color 0.2s ease, transform 0.2s ease;
        text-decoration: none;
    }
    .cm-btn:hover { transform: translateY(-1px); }
    .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
    .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
    .cm-btn-warning { background: #f0b606; border-color: #d99e00; color: #212529; }
    .cm-btn-secondary { background: #f4f4f4; border-color: #c2c2c2; color: #222; }
    .cm-btn-sm { padding: 0.3rem 0.6rem; font-size: 0.8rem; }
    .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
    .pgm-btn-cancel { background-color: #dc3545; color: #fff; border: 0 none; border-radius: 4px; padding: 6px 12px; font-size: 12px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; }
    .pgm-btn-save { background-color: #28a745; color: #fff; border: 1px solid #218838; border-radius: 4px; padding: 6px 12px; font-size: 12px; font-weight: bold; cursor: pointer; }
    .validation-error { color: #dc3545; font-size: 11px; }
    .obligatorio { color: red; font-weight: bold; }
    .filter-input, .filter-select { height: 30px; padding: 3px 6px; font-size: 11px; border: 1px solid #ccc; border-radius: 4px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;"><?php echo e(session('error')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold;">
            <ul style="margin:0;padding-left:20px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <li><?php echo e($error); ?></li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <a href="<?php echo e(route('proyectos.gestion')); ?>" class="pgm-btn-cancel" style="margin-bottom:15px;display:inline-block;">&laquo; Volver al listado</a>

    <?php
        $catalogosVacios = $catalogosForm['catalogosVacios'] ?? [];
    ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($catalogosVacios)): ?>
        <div style="background-color: #fff3cd; color: #856404; padding: 10px; margin: 12px 0; border: 1px solid #ffeeba; border-radius: 4px; font-size: 11px;">
            <b>Catálogos sin datos en repositorio:</b> <?php echo e(implode(', ', $catalogosVacios)); ?>.
            Un administrador debe cargarlos antes de poder guardar el expediente.
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <form method="POST" action="<?php echo e(route('proyectos.gestion.update', $proyecto->id)); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        
        <input type="hidden" name="equipo_seccion_clave" value="<?php echo e($clave); ?>">
        <input type="hidden" name="filterLapsoEquipo" value="<?php echo e($datosForm['filterLapsoEquipo'] ?? ''); ?>">
        <input type="hidden" name="filterProgramaEquipo" value="<?php echo e($datosForm['filterProgramaEquipo'] ?? ''); ?>">
        <input type="hidden" name="filterSeccionEquipo" value="<?php echo e($datosForm['filterSeccionEquipo'] ?? ''); ?>">
        <input type="hidden" name="programa_id_derived" value="<?php echo e($datosForm['programa_id_derived'] ?? ''); ?>">
        <input type="hidden" name="trayecto_derived" value="<?php echo e($datosForm['trayecto_derived'] ?? ''); ?>">
        <input type="hidden" name="trayecto_derived_codigo" value="<?php echo e($datosForm['trayecto_derived_codigo'] ?? ''); ?>">
        <input type="hidden" name="comunidad_id" value="<?php echo e($datosForm['comunidad_id'] ?? ''); ?>">

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; padding: 0 5px;">&nbsp;</legend>

            
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px;">Datos del proyecto</legend>
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                    
                    <tr>
                        <td width="20%"><b>Título:</b></td>
                        <td colspan="3">
                            <div style="padding:4px 0;font-weight:bold;<?php echo e($esProfesor ? 'font-size:20px;color:#000;' : 'font-size:14px;'); ?>">
                                <?php echo e($datosForm['titulo'] ?? '(seleccione un equipo para auto-llenar el título)'); ?>

                            </div>
                            <input type="hidden" name="titulo" value="<?php echo e($datosForm['titulo'] ?? ''); ?>">
                        </td>
                    </tr>
                    <tr>
                        <td width="20%"><b>Comunidad:</b></td>
                        <td colspan="3">
                            <?php
                                $comNombre = optional($catalogosForm['comunidades'] ?? collect())->firstWhere('id', $datosForm['comunidad_id'] ?? 0);
                            ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comNombre): ?>
                                <span style="background:#f9f2f2;border:1px solid #8b0000;padding:4px 10px;border-radius:4px;font-weight:bold;color:#8b0000;"><?php echo e($comNombre->nombre); ?></span>
                            <?php else: ?>
                                <span style="color:#999;">(asignada automáticamente del grupo)</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><b>Resumen:</b></td>
                        <td colspan="3">
                            <textarea name="resumen" rows="3" style="width:95%;font-size:12px;"><?php echo e(old('resumen', $datosForm['resumen'] ?? '')); ?></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['resumen'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><br><span class="validation-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>

                    
                </table>
            </fieldset>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$modoActualizacion): ?>
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px;">Clasificación del proyecto</legend>
                <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                    <tr>
                        <td width="20%"><b>Línea de Investigación:</b></td>
                        <td width="30%">
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select name="linea_investigacion_id" style="flex:1;font-size:11px;">
                                    <option value="">Seleccione...</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($catalogosForm['lineas'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <option value="<?php echo e($l->id); ?>" <?php echo e(old('linea_investigacion_id', $datosForm['linea_investigacion_id'] ?? '') == $l->id ? 'selected' : ''); ?>><?php echo e($l->nombre_investigacion); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <button type="button" onclick="abrirModalCatalogo('linea')" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space:nowrap;padding:4px 8px;font-size:11px;" title="Nueva línea de investigación">+</button>
                            </div>
                        </td>
                        <td width="20%"><b>Metodología:</b></td>
                        <td width="30%">
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select name="metodologia_id" style="flex:1;font-size:11px;">
                                    <option value="">Seleccione...</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($catalogosForm['metodologias'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <option value="<?php echo e($m->id); ?>" <?php echo e(old('metodologia_id', $datosForm['metodologia_id'] ?? '') == $m->id ? 'selected' : ''); ?>><?php echo e($m->nombre); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <button type="button" onclick="abrirModalCatalogo('metodologia')" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space:nowrap;padding:4px 8px;font-size:11px;" title="Nueva metodología">+</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Tipo de Investigación:</b></td>
                        <td>
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select name="tipo_investigacion_id" style="flex:1;font-size:11px;">
                                    <option value="">Seleccione...</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($catalogosForm['tipos_investigacion'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <option value="<?php echo e($ti->id); ?>" <?php echo e(old('tipo_investigacion_id', $datosForm['tipo_investigacion_id'] ?? '') == $ti->id ? 'selected' : ''); ?>><?php echo e($ti->nombre); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <button type="button" onclick="abrirModalCatalogo('tipo_investigacion')" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space:nowrap;padding:4px 8px;font-size:11px;" title="Nuevo tipo de investigación">+</button>
                            </div>
                        </td>
                        <td><b>Objetivo de Investigación:</b></td>
                        <td>
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select name="objetivo_investigacion_id" style="flex:1;font-size:11px;">
                                    <option value="">Seleccione...</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($catalogosForm['objetivos_investigacion'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <option value="<?php echo e($oi->id); ?>" <?php echo e(old('objetivo_investigacion_id', $datosForm['objetivo_investigacion_id'] ?? '') == $oi->id ? 'selected' : ''); ?>><?php echo e($oi->nombre); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <button type="button" onclick="abrirModalCatalogo('objetivo_investigacion')" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space:nowrap;padding:4px 8px;font-size:11px;" title="Nuevo objetivo de investigación">+</button>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


            <?php
                $puedeGestionarInvolucrados = $esProfesor || $esGestionador;
                $puedeGestionarInvolucrados = $puedeGestionarInvolucrados || !empty($canValidate);
                $proyectoId = $proyecto->id;
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedeGestionarInvolucrados): ?>
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px; background: #fafafa;">
                <legend style="font-weight: bold; font-size: 12px; color: #8b0000;">Involucrados del proyecto</legend>

                
                <div id="involucrados-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($involucradosProyecto)): ?>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size:12px;margin-bottom:10px;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f0f0f0;font-weight:bold;">
                                <th style="padding:5px 8px;text-align:left;">Cédula</th>
                                <th style="padding:5px 8px;text-align:left;">Nombre</th>
                                <th style="padding:5px 8px;text-align:left;">Roles</th>
                                <th style="padding:5px 8px;text-align:center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="involucrados-tbody">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $involucradosProyecto; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <tr id="inv-row-<?php echo e($inv['id']); ?>" style="border-bottom:1px solid #ddd;">
                                <td style="padding:5px 8px;font-weight:bold;"><?php echo e($inv['cedula']); ?></td>
                                <td style="padding:5px 8px;"><?php echo e($inv['nombre']); ?> <?php echo e($inv['apellido']); ?></td>
                                <td id="inv-roles-<?php echo e($inv['id']); ?>" style="padding:5px 8px;">
                                    <div class="inv-roles" style="display:flex;flex-wrap:wrap;gap:3px;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($inv['roles'])): ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $inv['roles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                                <span id="rol-badge-<?php echo e($inv['id']); ?>-<?php echo e($rol['id']); ?>" style="background:#8b0000;color:#fff;padding:1px 6px;font-size:10px;white-space:nowrap;">
                                                    <?php echo e($rol['nombre']); ?>

                                                    <button type="button" onclick="quitarRol(<?php echo e($proyectoId); ?>, <?php echo e($inv['pivot_id']); ?>, <?php echo e($rol['id']); ?>, <?php echo e($inv['id']); ?>)" style="background:none;border:none;color:#ffcccc;cursor:pointer;font-size:12px;padding:0 0 0 3px;line-height:1;">&times;</button>
                                                </span>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <?php else: ?>
                                            <span id="inv-no-roles-<?php echo e($inv['id']); ?>" style="color:#999;font-size:11px;">Sin roles</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                                <td style="padding:5px 8px;text-align:center;">
                                    <button type="button" onclick="abrirRolesModal(<?php echo e($proyectoId); ?>, <?php echo e($inv['id']); ?>, '<?php echo e(addslashes($inv['nombre'])); ?> <?php echo e(addslashes($inv['apellido'])); ?>')" style="background:#8b0000;color:#fff;border:none;border-radius:3px;padding:3px 10px;font-size:11px;cursor:pointer;">+ Roles</button>
                                    <button type="button" onclick="quitarInvolucrado(<?php echo e($proyectoId); ?>, <?php echo e($inv['id']); ?>)" style="background:#dc3545;color:#fff;border:none;border-radius:3px;padding:3px 10px;font-size:11px;cursor:pointer;">Quitar</button>
                                </td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div id="inv-empty" style="font-size:11px;color:#999;margin-bottom:10px;">No hay involucrados registrados en este proyecto.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <hr style="border:none;border-top:1px solid #ddd;margin:8px 0;">
                <div style="text-align:center;">
                    <button type="button" onclick="abrirModalInvolucrado()" style="background:#8b0000;color:#fff;border:none;border-radius:3px;padding:6px 16px;font-size:12px;cursor:pointer;">+ Insertar involucrado</button>
                </div>
            </fieldset>

            
            <div id="modal-involucrado" onclick="if(event.target===this)cerrarModalInvolucrado()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;justify-content:center;align-items:center;">
                <div style="background:#fff;border-radius:6px;padding:16px;width:440px;max-width:90%;box-shadow:0 4px 20px rgba(0,0,0,0.2);">
                    <h3 style="margin:0 0 12px;font-size:14px;color:#333;">Insertar involucrado</h3>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size:12px;">
                        <tr>
                            <td width="22%"><b>Cédula:</b></td>
                            <td width="78%" colspan="3">
                                <input type="text" id="inv-cedula" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');buscarPersonaPorCedula()" style="width:95%;padding:5px 6px;border:1px solid #ccc;border-radius:3px;font-size:12px;" placeholder="12345678" autocomplete="off">
                                <div id="inv-cedula-msg" style="font-size:10px;color:#666;margin-top:2px;"></div>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Nombre:</b></td>
                            <td><input type="text" id="inv-nombre" style="width:95%;padding:5px 6px;border:1px solid #ccc;border-radius:3px;font-size:12px;" placeholder="Se auto-completa"></td>
                            <td width="15%"><b>Apellido:</b></td>
                            <td width="23%"><input type="text" id="inv-apellido" style="width:95%;padding:5px 6px;border:1px solid #ccc;border-radius:3px;font-size:12px;" placeholder="Se auto-completa"></td>
                        </tr>
                    </table>

                    
                    <hr style="border:none;border-top:1px solid #ddd;margin:8px 0;">
                    <div style="font-weight:bold;font-size:12px;color:#8b0000;margin-bottom:6px;">Roles del involucrado <span style="color:red;">*</span></div>
                    <div id="inv-roles-msg" style="font-size:10px;color:#c62828;margin-bottom:4px;display:none;">Debe seleccionar al menos un rol.</div>

                    <div style="display:flex;gap:6px;align-items:center;">
                        <input type="text" id="inv-buscar-rol" onkeyup="buscarRolesModal()" style="flex:1;padding:5px 6px;border:1px solid #ccc;border-radius:3px;font-size:12px;" placeholder="Buscar rol..." autocomplete="off">
                        <button type="button" onclick="toggleFormNuevoRolModal()" style="background:none;border:1px dashed #198754;color:#198754;border-radius:3px;font-size:11px;cursor:pointer;padding:4px 10px;white-space:nowrap;">+ Nuevo</button>
                    </div>
                    <div id="inv-resultados-roles" style="margin-top:3px;border:1px solid #e0e0e0;border-radius:3px;max-height:120px;overflow-y:auto;background:#fff;display:none;"></div>

                    <div id="inv-form-nuevo-rol" style="display:none;margin-top:6px;padding:8px;background:#f9fff9;border:1px solid #c3e6cb;border-radius:3px;">
                        <div style="display:flex;gap:6px;">
                            <input type="text" id="inv-nuevo-rol-nombre" style="flex:1;padding:5px 6px;border:1px solid #ccc;border-radius:3px;font-size:12px;" placeholder="Nombre del nuevo rol">
                            <button type="button" onclick="crearRolModal()" style="background:#198754;color:#fff;border:none;border-radius:3px;padding:5px 12px;font-size:11px;cursor:pointer;">Crear</button>
                        </div>
                    </div>

                    <div id="inv-roles-seleccionados" style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px;min-height:24px;"></div>

                    <div style="margin-top:10px;text-align:right;display:flex;gap:6px;justify-content:flex-end;">
                        <span id="inv-source" style="font-size:10px;color:#999;margin-right:auto;align-self:center;"></span>
                        <button type="button" onclick="cerrarModalInvolucrado()" style="background:#6c757d;color:#fff;border:none;border-radius:3px;padding:5px 14px;font-size:12px;cursor:pointer;">Cancelar</button>
                        <button type="button" id="btn-agregar-inv" onclick="agregarInvolucradoAlProyecto(<?php echo e($proyectoId); ?>)" style="background:#8b0000;color:#fff;border:none;border-radius:3px;padding:5px 14px;font-size:12px;cursor:pointer;">Agregar</button>
                    </div>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($miembrosGrupo)): ?>
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px;">Integrantes del equipo</legend>
                <table width="100%" border="1" cellpadding="4" cellspacing="0" style="font-size: 11px; border-collapse: collapse;">
                    <thead>
                        <tr style="background:#ddd;">
                            <th style="padding:4px 8px;">#</th>
                            <th style="padding:4px 8px;">Cédula</th>
                            <th style="padding:4px 8px;">Nombre</th>
                            <th style="padding:4px 8px;">Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $miembrosGrupo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $miembro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <tr style="background: <?php echo e($idx % 2 == 0 ? '#fafafa' : '#fff'); ?>;">
                                <td align="center" style="padding:4px 8px;"><?php echo e($idx + 1); ?></td>
                                <td style="padding:4px 8px;"><?php echo e($miembro['cedula']); ?></td>
                                <td style="padding:4px 8px;"><?php echo e($miembro['nombre']); ?> <?php echo e($miembro['apellido']); ?></td>
                                <td style="padding:4px 8px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($miembro['rol_id'] ?? 0) == 1): ?>
                                        <span style="color:#8b0000;font-weight:bold;">Líder</span>
                                    <?php else: ?>
                                        <span style="color:#666;">Autor</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </fieldset>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php
                $componentesDisp = $catalogosForm['componentes_disp'] ?? collect();
                $docsExistentes = $datosForm['archivos_actuales'] ?? [];
                $tieneDocumentosSubidos = !empty($docsExistentes);
            ?>

            
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$esProfesor || ($esProfesor && $tieneDocumentosSubidos)): ?>
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px;">
                    Documentos del proyecto por componente
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($componentesDisp->isNotEmpty()): ?>
                        <span style="font-weight:normal;font-size:10px;color:#666;"> (<?php echo e($componentesDisp->count()); ?> componente(s))</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </legend>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($componentesDisp->isNotEmpty()): ?>
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $componentesDisp; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <?php
                            $docActual = $docsExistentes[$comp->id] ?? null;
                            $acceptStr = $comp->accept ?? '.pdf,.doc,.docx';
                            $maxMb = $comp->tamano_maximo_mb ?? 10;
                            $acceptTypes = $comp->tipo_archivo ?? 'PDF';
                        ?>
                        <tr>
                            <td width="25%" valign="middle">
                                <b><?php echo e($comp->nombre); ?></b>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comp->es_obligatorio): ?><span class="obligatorio">*</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <br><span style="font-size:9px;color:#666;"><?php echo e(strtoupper($acceptTypes)); ?> &middot; Máx <?php echo e($maxMb); ?>MB</span>
                            </td>
                            <td width="45%">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$esProfesor): ?>
                                    
                                    <input type="file" name="documentos[<?php echo e($comp->id); ?>]" accept="<?php echo e($acceptStr); ?>" style="width:100%;font-size:11px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['documentos.' . $comp->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><br><span class="validation-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($docActual): ?>
                                        <?php
                                            $estado = $docActual['estado'] ?? 0;
                                            $obs = $docActual['observacion'] ?? '';
                                        ?>
                                        <div style="font-size:11px;">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($estado == 1): ?>
                                                <span style="color: #28a745; font-weight: bold;">✓ Aceptado</span>
                                            <?php elseif($estado == 2): ?>
                                                <span style="color: #dc3545; font-weight: bold;">✗ Rechazado</span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($obs): ?>
                                                    <div style="font-size:10px; color:#666; margin-top:2px;"><i>Motivo:</i> <?php echo e($obs); ?></div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php else: ?>
                                                <span style="color: #666;">Pendiente de revisión</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size:11px;color:#999;">Pendiente de carga</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td width="30%">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($docActual): ?>
                                    <a href="<?php echo e(route('documentos.serve', ['path' => $docActual['path']])); ?>" target="_blank" class="simple-link">Ver <?php echo e($comp->nombre); ?></a>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($esProfesor && ($docActual['estado'] ?? 0) == 0): ?>
                                        <div style="margin-top:5px;display:flex;gap:5px;">
                                            <button type="button" onclick="actualizarDoc(<?php echo e($docActual['id'] ?? 0); ?>, 1)" class="cm-btn cm-btn-success cm-btn-sm" style="padding:2px 6px;">Aceptar</button>
                                            <button type="button" onclick="abrirModalRechazo(<?php echo e($docActual['id'] ?? 0); ?>)" class="cm-btn cm-btn-danger cm-btn-sm" style="padding:2px 6px;">Rechazar</button>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#999;font-size:10px;">Sin documento</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </table>
                <?php else: ?>
                    <div style="padding:12px;background:#fff8e1;border:1px solid #ffe082;border-radius:4px;font-size:11px;color:#6d4c00;">
                        <b>⚠ No hay componentes configurados para este programa.</b><br>
                        Un administrador debe ir a <b>Configuración &gt; Componentes</b> y crear los
                        componentes documentales asociados al programa correspondiente.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </fieldset>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>



            
            <div id="modal-rechazo-doc" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)cerrarModalRechazo()">
                <div style="background:#fff;border-radius:10px;padding:24px;max-width:480px;width:92%;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #dc3545;">
                        <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Rechazar Documento</h3>
                    </div>
                    <input type="hidden" id="modal-rechazo-doc-id" value="">
                    <div style="margin-bottom:12px;">
                        <label style="font-weight:bold;font-size:12px;display:block;margin-bottom:4px;">Motivo del rechazo: <span style="color:red;">*</span></label>
                        <textarea id="modal-rechazo-doc-obs" rows="4" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:13px;" placeholder="Explique por qué el documento no es aceptable..."></textarea>
                    </div>
                    <div id="modal-rechazo-doc-error" style="color:#dc3545;font-size:11px;margin-bottom:8px;display:none;"></div>
                    <div style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center;">
                        <button type="button" onclick="confirmarRechazoDoc()" class="cm-btn cm-btn-danger" style="padding:8px 20px;font-size:13px;">Confirmar Rechazo</button>
                        <button type="button" onclick="cerrarModalRechazo()" class="cm-btn cm-btn-secondary" style="padding:8px 20px;font-size:13px;">Cancelar</button>
                    </div>
                </div>
            </div>

            
            <div id="modal-catalogo" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)cerrarModalCatalogo()">
                <div style="background:#fff;border-radius:10px;padding:24px;max-width:480px;width:92%;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                        <h3 id="modal-catalogo-titulo" style="margin:0;font-size:16px;font-weight:bold;color:#333;"></h3>
                    </div>
                    <input type="hidden" id="modal-catalogo-tipo" value="">
                    <input type="hidden" id="modal-catalogo-ruta" value="">

                    
                    <div style="margin-bottom:12px;">
                        <label style="font-weight:bold;font-size:12px;display:block;margin-bottom:4px;">Nombre: <span style="color:red;">*</span></label>
                        <input type="text" id="modal-catalogo-nombre" oninput="validarNombre(this)" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:13px;" placeholder="Nombre...">
                        <span id="nombreStatus" style="font-size:11px;display:none;"></span>
                    </div>

                    
                    <div style="margin-bottom:12px;">
                        <label style="font-weight:bold;font-size:12px;display:block;margin-bottom:4px;">Descripción:</label>
                        <textarea id="modal-catalogo-descripcion" rows="2" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;"></textarea>
                    </div>

                    <div id="modal-catalogo-mencion" style="display:none;margin-bottom:12px;">
                        <label style="font-weight:bold;font-size:12px;display:block;margin-bottom:4px;">
                            <input type="checkbox" id="modal-catalogo-mencion-check" style="margin-right:6px;"> Mención Honorífica
                        </label>
                    </div>

                    <div id="modal-catalogo-error" style="color:#dc3545;font-size:11px;margin-bottom:8px;display:none;"></div>

                    <div style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center;">
                        <button type="button" onclick="guardarCatalogo()" class="cm-btn cm-btn-success" style="padding:8px 20px;font-size:13px;">Guardar</button>
                        <button type="button" onclick="cerrarModalCatalogo()" class="cm-btn cm-btn-danger" style="padding:8px 20px;font-size:13px;">Cancelar</button>
                    </div>
                </div>
            </div>

            
            <div id="modal-roles" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)cerrarRolesModal()">
                <div style="background:#fff;border-radius:6px;padding:16px;max-width:400px;width:90%;box-shadow:0 4px 20px rgba(0,0,0,0.2);">
                    <h3 style="margin:0 0 10px;font-size:14px;color:#8b0000;">Roles para: <span id="rol-modal-nombre" style="color:#333;"></span></h3>
                    <input type="hidden" id="rol-modal-proyecto-id" value="">
                    <input type="hidden" id="rol-modal-inv-id" value="">

                    <input type="text" id="buscar-rol" onkeyup="buscarRoles()" style="width:100%;padding:6px 8px;border:1px solid #ccc;border-radius:3px;font-size:12px;box-sizing:border-box;" placeholder="Buscar rol..." autocomplete="off">
                    <div id="resultados-roles" style="margin-top:3px;border:1px solid #e0e0e0;border-radius:3px;max-height:150px;overflow-y:auto;background:#fff;display:none;"></div>

                    <div style="margin-top:6px;">
                        <button type="button" onclick="toggleFormNuevoRol()" style="background:none;border:1px dashed #198754;color:#198754;border-radius:3px;font-size:11px;cursor:pointer;padding:3px 10px;width:100%;">+ Nuevo rol</button>
                    </div>

                    <div id="form-nuevo-rol" style="display:none;margin-top:6px;padding:8px;background:#f9fff9;border:1px solid #c3e6cb;border-radius:3px;">
                        <div style="display:flex;gap:6px;">
                            <input type="text" id="nuevo-rol-nombre" style="flex:1;padding:5px 6px;border:1px solid #ccc;border-radius:3px;font-size:12px;" placeholder="Nombre">
                            <button type="button" onclick="crearRol()" style="background:#198754;color:#fff;border:none;border-radius:3px;padding:5px 12px;font-size:11px;cursor:pointer;">Crear</button>
                        </div>
                    </div>

                    <div style="margin-top:6px;font-size:10px;color:#999;text-align:center;" id="rol-asignado-msg"></div>
                </div>
            </div>

            
            <div style="text-align:center;margin-top:20px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyecto->estado_validacion === 'completado'): ?>
                    <a href="<?php echo e(route('proyectos.gestion')); ?>" class="pgm-btn-cancel" style="margin-right:10px;">Cerrar</a>
                <?php else: ?>
                    <a href="<?php echo e(route('proyectos.gestion')); ?>" class="pgm-btn-cancel" style="margin-right:10px;">Cancelar</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button type="submit" class="pgm-btn-save"><?php echo e($modoActualizacion ? 'Subir documentos' : 'Guardar cambios'); ?></button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($canValidate) && $proyecto->estado_validacion === 'completado'): ?>
                    <button type="button" class="cm-btn cm-btn-success cm-btn-sm" style="margin-left:10px;" onclick="mostrarModalAccion({icon:'\u2705',title:'Aprobar proyecto',message:'\u00bfAprueba este proyecto?',confirmText:'S\u00ed, aprobar',confirmClass:'cm-btn-success',onConfirm:function(){window.location='<?php echo e(route('proyectos.gestion.approve', $proyecto->id)); ?>'}})">Aprobar</button>
                    <button type="button" class="cm-btn cm-btn-warning cm-btn-sm" style="margin-left:5px;" onclick="abrirRechazar(<?php echo e($proyecto->id); ?>)">Rechazar</button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </fieldset>
    </form>

    
    <div id="rejectModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)cerrarRechazar()">
        <div style="background:#fff;border-radius:8px;padding:20px;max-width:520px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
            <h3 style="margin:0 0 15px;font-size:16px;color:#8b0000;">Motivo de rechazo</h3>
            <form id="rejectForm" method="POST" action="">
                <?php echo csrf_field(); ?>
                <textarea name="motivo" rows="4" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;font-size:12px;" placeholder="Indique la justificación del rechazo..."></textarea>
                <div style="margin-top:15px;text-align:center;display:flex;gap:10px;justify-content:center;">
                    <button type="submit" class="cm-btn cm-btn-danger">Confirmar rechazo</button>
                    <button type="button" class="cm-btn cm-btn-secondary" onclick="cerrarRechazar()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function abrirRechazar(id) {
        document.getElementById('rejectForm').action = '<?php echo e(route("proyectos.gestion.reject", "PLACEHOLDER")); ?>'.replace('PLACEHOLDER', id);
        document.getElementById('rejectModal').style.display = 'flex';
    }
    function cerrarRechazar() {
        document.getElementById('rejectModal').style.display = 'none';
    }
function actualizarDoc(id, estado, observacion = '') {
    fetch('/proyectos/documentos/' + id + '/estado', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'},
        body: JSON.stringify({ estado, observacion })
    }).then(r => r.json()).then(data => {
        if (data.success) location.reload();
        else alert(data.message);
    });
}

function abrirModalRechazo(id) {
    document.getElementById('modal-rechazo-doc-id').value = id;
    document.getElementById('modal-rechazo-doc-obs').value = '';
    document.getElementById('modal-rechazo-doc-error').style.display = 'none';
    document.getElementById('modal-rechazo-doc').style.display = 'flex';
}

function cerrarModalRechazo() {
    document.getElementById('modal-rechazo-doc').style.display = 'none';
}

function confirmarRechazoDoc() {
    const id = document.getElementById('modal-rechazo-doc-id').value;
    const obs = document.getElementById('modal-rechazo-doc-obs').value.trim();
    if (!obs) {
        document.getElementById('modal-rechazo-doc-error').textContent = 'El motivo es obligatorio.';
        document.getElementById('modal-rechazo-doc-error').style.display = 'block';
        return;
    }
    actualizarDoc(id, 2, obs);
}
</script>
<?php $__env->startPush('scripts'); ?>
<script>
// ─── Variables globales ───────────────────────────────────────────
let personaTimer = null;
let rolesTimer = null;

// ─── Modal involucrado ──────────────────────────────────────────
function abrirModalInvolucrado() {
    document.getElementById('inv-cedula').value = '';
    document.getElementById('inv-nombre').value = '';
    document.getElementById('inv-apellido').value = '';
    document.getElementById('inv-cedula-msg').textContent = '';
    document.getElementById('inv-source').textContent = '';
    document.getElementById('inv-buscar-rol').value = '';
    document.getElementById('inv-resultados-roles').style.display = 'none';
    document.getElementById('inv-roles-msg').style.display = 'none';
    document.getElementById('inv-form-nuevo-rol').style.display = 'none';
    invRolesPendientes = {};
    renderizarRolesSeleccionados();
    document.getElementById('modal-involucrado').style.display = 'flex';
    document.getElementById('inv-cedula').focus();
}
function cerrarModalInvolucrado() {
    invRolesPendientes = {};
    document.getElementById('modal-involucrado').style.display = 'none';
}

// ─── Buscar persona por cédula (intranet -> involucrados -> nuevo) ─
function buscarPersonaPorCedula() {
    clearTimeout(personaTimer);
    const cedula = document.getElementById('inv-cedula').value.trim();
    const msgDiv = document.getElementById('inv-cedula-msg');
    const nombreInput = document.getElementById('inv-nombre');
    const apellidoInput = document.getElementById('inv-apellido');
    const sourceSpan = document.getElementById('inv-source');

    if (cedula.length < 3) {
        msgDiv.textContent = '';
        document.getElementById('btn-agregar-inv').disabled = false;
        return;
    }

    personaTimer = setTimeout(() => {
        msgDiv.textContent = 'Buscando...';
        msgDiv.style.color = '#666';

        fetch('<?php echo e(route("proyectos.gestion.involucrados.buscar-persona")); ?>?cedula=' + encodeURIComponent(cedula))
            .then(r => r.json())
            .then(data => {
                if (!data) {
                    msgDiv.textContent = 'Cédula muy corta';
                    return;
                }
                if (data.found) {
                    nombreInput.value = data.data.nombre || '';
                    apellidoInput.value = data.data.apellido || '';
                    var yaAgregado = false;
                    if (data.data.id) {
                        yaAgregado = !!document.getElementById('inv-row-' + data.data.id);
                    }
                    if (!yaAgregado) {
                        var rows = document.querySelectorAll('#involucrados-tbody tr');
                        rows.forEach(function(r) {
                            if (r.cells && r.cells[0] && r.cells[0].textContent.trim() === cedula) {
                                yaAgregado = true;
                            }
                        });
                    }
                    if (yaAgregado) {
                        msgDiv.innerHTML = '⚠ Ya agregado a este proyecto';
                        msgDiv.style.color = '#856404';
                        document.getElementById('btn-agregar-inv').disabled = true;
                    } else if (data.source === 'intranet') {
                        msgDiv.innerHTML = '✅ Encontrado en <b>persona</b> (intranet)';
                        msgDiv.style.color = '#198754';
                        document.getElementById('btn-agregar-inv').disabled = false;
                    } else {
                        msgDiv.innerHTML = '✅ Ya registrado en <b>involucrados</b>';
                        msgDiv.style.color = '#198754';
                        document.getElementById('btn-agregar-inv').disabled = false;
                    }
                } else {
                    nombreInput.value = '';
                    apellidoInput.value = '';
                    msgDiv.innerHTML = '⚠ No encontrado. Complete los datos para crear uno nuevo.';
                    msgDiv.style.color = '#856404';
                    document.getElementById('btn-agregar-inv').disabled = false;
                }
            })
            .catch(() => {
                msgDiv.textContent = 'Error al buscar';
                msgDiv.style.color = '#dc3545';
            });
    }, 400);
}

// ─── Roles pendientes (global para el modal involucrado) ──────────
let invRolesPendientes = {};

function renderizarRolesSeleccionados() {
    const container = document.getElementById('inv-roles-seleccionados');
    const ids = Object.keys(invRolesPendientes);
    if (ids.length === 0) {
        container.innerHTML = '<span style="color:#999;font-size:11px;">Ningún rol seleccionado</span>';
        return;
    }
    container.innerHTML = ids.map(id =>
        '<span style="background:#8b0000;color:#fff;padding:1px 6px;font-size:10px;white-space:nowrap;">' +
            invRolesPendientes[id] +
            '<button type="button" onclick="quitarRolSeleccionado(' + id + ')" style="background:none;border:none;color:#ffcccc;cursor:pointer;font-size:12px;padding:0 0 0 3px;line-height:1;">&times;</button>' +
        '</span>'
    ).join('');
}

function quitarRolSeleccionado(rolId) {
    delete invRolesPendientes[rolId];
    renderizarRolesSeleccionados();
}

function buscarRolesModal() {
    clearTimeout(rolesTimer);
    const q = document.getElementById('inv-buscar-rol').value.trim();
    const container = document.getElementById('inv-resultados-roles');
    if (q.length < 1) {
        container.style.display = 'none';
        return;
    }
    const proyectoId = <?php echo e($proyectoId); ?>;
    rolesTimer = setTimeout(() => {
        fetch('<?php echo e(route("proyectos.gestion.involucrados.roles", "PLACEHOLDER")); ?>'.replace('PLACEHOLDER', proyectoId) + '?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                if (data.length === 0) {
                    container.innerHTML = '<div style="padding:6px 8px;font-size:10px;color:#999;">No se encontraron roles</div>';
                } else {
                    container.innerHTML = data.map(rol => {
                        if (invRolesPendientes[rol.id]) return '';
                        return '<div onclick="seleccionarRolModal(' + rol.id + ',\'' + rol.nombre.replace(/'/g, "\\'") + '\')" style="padding:6px 8px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:11px;" onmouseover="this.style.background=\'#f5f0f0\'" onmouseout="this.style.background=\'\'">' +
                            '<b>' + rol.nombre + '</b>' +
                        '</div>';
                    }).filter(Boolean).join('') || '<div style="padding:6px 8px;font-size:10px;color:#999;">Ya seleccionó todos los roles disponibles</div>';
                }
                container.style.display = 'block';
            });
    }, 300);
}

function seleccionarRolModal(rolId, rolNombre) {
    invRolesPendientes[rolId] = rolNombre;
    renderizarRolesSeleccionados();
    document.getElementById('inv-buscar-rol').value = '';
    document.getElementById('inv-resultados-roles').style.display = 'none';
    document.getElementById('inv-roles-msg').style.display = 'none';
}

function toggleFormNuevoRolModal() {
    const f = document.getElementById('inv-form-nuevo-rol');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
    if (f.style.display === 'block') document.getElementById('inv-nuevo-rol-nombre').focus();
}

function crearRolModal() {
    const nombre = document.getElementById('inv-nuevo-rol-nombre').value.trim();
    if (!nombre) { showNotifyToast('warning', 'Escriba un nombre para el rol'); return; }
    fetch('<?php echo e(route("proyectos.gestion.involucrados.roles.crear")); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json'},
        body: JSON.stringify({ nombre })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            invRolesPendientes[data.id] = nombre;
            renderizarRolesSeleccionados();
            document.getElementById('inv-nuevo-rol-nombre').value = '';
            document.getElementById('inv-form-nuevo-rol').style.display = 'none';
            document.getElementById('inv-roles-msg').style.display = 'none';
        }
    });
}

// ─── Agregar involucrado al proyecto (buscaOCrear + agrega) ───────
function agregarInvolucradoAlProyecto(proyectoId) {
    const cedula = document.getElementById('inv-cedula').value.trim();
    const nombre = document.getElementById('inv-nombre').value.trim();
    const apellido = document.getElementById('inv-apellido').value.trim();

    if (!cedula || cedula.length < 3) {
        showNotifyToast('warning', 'Ingrese una c\u00e9dula v\u00e1lida');
        return;
    }

    const roles = Object.keys(invRolesPendientes).map(Number);
    if (roles.length === 0) {
        document.getElementById('inv-roles-msg').style.display = 'block';
        return;
    }
    document.getElementById('inv-roles-msg').style.display = 'none';

    const btn = document.getElementById('btn-agregar-inv');
    btn.disabled = true;
    btn.textContent = 'Agregando...';

    fetch('<?php echo e(route("proyectos.gestion.involucrados.crear", "PLACEHOLDER")); ?>'.replace('PLACEHOLDER', proyectoId), {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json'},
        body: JSON.stringify({ nombre, apellido, cedula, roles })
    }).then(r => r.json()).then(() => {
        location.reload();
    }).catch(() => {
        showNotifyToast('error', 'Error al agregar el involucrado');
        btn.disabled = false;
        btn.textContent = 'Agregar';
    });
}

// ─── Quitar involucrado ──────────────────────────────────────────
function quitarInvolucrado(proyectoId, invId) {
    mostrarModalAccion({
        icon:'\u26A0\uFE0F',title:'Eliminar involucrado',
        message:'\u00bfEliminar este involucrado del proyecto?',
        confirmText:'S\u00ed, eliminar',
        confirmClass:'cm-btn-danger',
        onConfirm:function(){
            fetch('<?php echo e(route("proyectos.gestion.involucrados.quitar", ["PLACEHOLDER_PROY", "PLACEHOLDER_INV"])); ?>'.replace('PLACEHOLDER_PROY', proyectoId).replace('PLACEHOLDER_INV', invId), {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json'}
            }).then(r => r.json()).then(() => location.reload());
        }
    });
}

// ─── Modal roles ─────────────────────────────────────────────────
function abrirRolesModal(proyectoId, invId, nombre) {
    document.getElementById('rol-modal-proyecto-id').value = proyectoId;
    document.getElementById('rol-modal-inv-id').value = invId;
    document.getElementById('rol-modal-nombre').textContent = nombre;
    document.getElementById('resultados-roles').style.display = 'none';
    document.getElementById('buscar-rol').value = '';
    document.getElementById('form-nuevo-rol').style.display = 'none';
    document.getElementById('modal-roles').style.display = 'flex';
}

function cerrarRolesModal() {
    document.getElementById('modal-roles').style.display = 'none';
}

function buscarRoles() {
    clearTimeout(rolesTimer);
    const q = document.getElementById('buscar-rol').value.trim();
    const proyectoId = document.getElementById('rol-modal-proyecto-id').value;
    const invId = document.getElementById('rol-modal-inv-id').value;
    const container = document.getElementById('resultados-roles');
    if (q.length < 1) {
        container.style.display = 'none';
        return;
    }
    rolesTimer = setTimeout(() => {
        fetch('<?php echo e(route("proyectos.gestion.involucrados.roles", "PLACEHOLDER")); ?>'.replace('PLACEHOLDER', proyectoId) + '?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                if (data.length === 0) {
                    container.innerHTML = '<div style="padding:6px 8px;font-size:10px;color:#999;">No se encontraron roles</div>';
                } else {
                    container.innerHTML = data.map(rol =>
                        '<div onclick="asignarRolConNombre(' + proyectoId + ',' + invId + ',' + rol.id + ',\'' + rol.nombre.replace(/'/g, "\\'") + '\')" style="padding:6px 8px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:11px;" onmouseover="this.style.background=\'#f5f0f0\'" onmouseout="this.style.background=\'\'">' +
                            '<b>' + rol.nombre + '</b>' +
                        '</div>'
                    ).join('');
                }
                container.style.display = 'block';
            });
    }, 300);
}

function asignarRolConNombre(proyectoId, invId, rolId, rolNombre) {
    fetch('<?php echo e(route("proyectos.gestion.involucrados.roles.asignar", ["PLACEHOLDER_PROY", "PLACEHOLDER_INV"])); ?>'.replace('PLACEHOLDER_PROY', proyectoId).replace('PLACEHOLDER_INV', invId), {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json'},
        body: JSON.stringify({ rol_id: rolId })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            var noRoles = document.getElementById('inv-no-roles-' + invId);
            if (noRoles) noRoles.remove();
            var container = document.querySelector('#inv-roles-' + invId + ' .inv-roles');
            var badge = document.createElement('span');
            badge.id = 'rol-badge-' + invId + '-' + rolId;
            badge.style.cssText = 'background:#8b0000;color:#fff;padding:1px 6px;font-size:10px;white-space:nowrap;';
            badge.innerHTML = rolNombre +
                '<button type="button" onclick="quitarRol(' + proyectoId + ',' + data.pivot_id + ',' + rolId + ',' + invId + ')" style="background:none;border:none;color:#ffcccc;cursor:pointer;font-size:12px;padding:0 0 0 3px;line-height:1;">&times;</button>';
            container.appendChild(badge);
            document.getElementById('rol-asignado-msg').textContent = 'Rol asignado correctamente';
            document.getElementById('rol-asignado-msg').style.color = '#198754';
        }
    }).catch(() => {
        showNotifyToast('error', 'Error al asignar rol');
    });
}

function quitarRol(proyectoId, pivotId, rolId, invId) {
    mostrarModalAccion({
        icon:'\u26A0\uFE0F',title:'Quitar rol',
        message:'\u00bfQuitar este rol del involucrado?',
        confirmText:'S\u00ed, quitar',
        confirmClass:'cm-btn-danger',
        onConfirm:function(){
            fetch('<?php echo e(route("proyectos.gestion.involucrados.roles.quitar", ["PLACEHOLDER_PROY", "PLACEHOLDER_PIVOT", "PLACEHOLDER_ROL"])); ?>'.replace('PLACEHOLDER_PROY', proyectoId).replace('PLACEHOLDER_PIVOT', pivotId).replace('PLACEHOLDER_ROL', rolId), {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json'}
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    var badge = document.getElementById('rol-badge-' + invId + '-' + rolId);
                    if (badge) badge.remove();
                    var container = document.querySelector('#inv-roles-' + invId + ' .inv-roles');
                    if (container && container.children.length === 0) {
                        container.innerHTML = '<span id="inv-no-roles-' + invId + '" style="color:#999;font-size:11px;">Sin roles</span>';
                    }
                }
            }).catch(() => {
                showNotifyToast('error', 'Error al quitar rol');
            });
        }
    });
}

// ─── Catálogos (modal genérico) ──────────────────────────────────
const catalogoConfig = {
    linea: {
        titulo: 'Nueva Línea de Investigación',
        ruta: '<?php echo e(route("lineas-investigacion.store")); ?>',
        checkRuta: '<?php echo e(route("lineas-investigacion.check-nombre")); ?>',
        campoNombre: 'nombre_investigacion',
        mostrarMencion: false
    },
    metodologia: {
        titulo: 'Nueva Metodología de Investigación',
        ruta: '<?php echo e(route("metodologia-investigacion.store")); ?>',
        checkRuta: '<?php echo e(route("metodologia-investigacion.check-nombre")); ?>',
        campoNombre: 'nombre',
        mostrarMencion: false
    },
    tipo_investigacion: {
        titulo: 'Nuevo Tipo de Investigación',
        ruta: '<?php echo e(route("tipos-investigacion.store")); ?>',
        checkRuta: '<?php echo e(route("tipos-investigacion.check-nombre")); ?>',
        campoNombre: 'nombre',
        mostrarMencion: false
    },
    objetivo_investigacion: {
        titulo: 'Nuevo Objetivo de Investigación',
        ruta: '<?php echo e(route("objetivos-investigacion.store")); ?>',
        checkRuta: '<?php echo e(route("objetivos-investigacion.check-nombre")); ?>',
        campoNombre: 'nombre',
        mostrarMencion: false
    }
};

function abrirModalCatalogo(tipo) {
    const cfg = catalogoConfig[tipo];
    if (!cfg) return;
    document.getElementById('modal-catalogo-titulo').innerHTML = cfg.titulo;
    document.getElementById('modal-catalogo-tipo').value = tipo;
    document.getElementById('modal-catalogo-ruta').value = cfg.ruta;
    const input = document.getElementById('modal-catalogo-nombre');
    input.value = '';
    input.dataset.checkUrl = cfg.checkRuta;
    input.dataset.nombreOk = '';
    document.getElementById('modal-catalogo-descripcion').value = '';
    const menc = document.getElementById('modal-catalogo-mencion');
    if (menc) menc.style.display = cfg.mostrarMencion ? 'block' : 'none';
    const mencChk = document.getElementById('modal-catalogo-mencion-check');
    if (mencChk) mencChk.checked = false;
    document.getElementById('modal-catalogo-error').style.display = 'none';
    document.getElementById('nombreStatus').style.display = 'none';
    document.getElementById('modal-catalogo').style.display = 'flex';
    input.focus();
}

function cerrarModalCatalogo() {
    document.getElementById('modal-catalogo').style.display = 'none';
    const input = document.getElementById('modal-catalogo-nombre');
    if (input) input.dataset.nombreOk = '';
    const status = document.getElementById('nombreStatus');
    if (status) status.style.display = 'none';
}

function guardarCatalogo() {
    const tipo = document.getElementById('modal-catalogo-tipo').value;
    const cfg = catalogoConfig[tipo];
    if (!cfg) return;

    const input = document.getElementById('modal-catalogo-nombre');
    const nombre = input.value.trim();
    if (!nombre) {
        document.getElementById('modal-catalogo-error').textContent = 'El nombre es obligatorio.';
        document.getElementById('modal-catalogo-error').style.display = 'block';
        return;
    }
    if (input.dataset.nombreOk === 'false') {
        document.getElementById('modal-catalogo-error').textContent = 'Este nombre ya está en uso.';
        document.getElementById('modal-catalogo-error').style.display = 'block';
        return;
    }
    if (input.dataset.nombreOk === 'checking') {
        document.getElementById('modal-catalogo-error').textContent = 'Verificando disponibilidad, espere...';
        document.getElementById('modal-catalogo-error').style.display = 'block';
        return;
    }

    document.getElementById('modal-catalogo-error').style.display = 'none';

    const data = { _token: '<?php echo e(csrf_token()); ?>' };
    data[cfg.campoNombre] = nombre;
    const desc = document.getElementById('modal-catalogo-descripcion').value.trim();
    if (desc) data.descripcion = desc;

    // Línea requiere area_de_investigacion y programa_id
    if (tipo === 'linea') {
        data.area_de_investigacion = nombre.length > 100 ? nombre.substring(0, 100) : nombre;
        const progInput = document.querySelector('[name="programa_id_derived"]');
        if (progInput && progInput.value) data.programa_id = progInput.value;
    }
    if (cfg.mostrarMencion) {
        const mencChk = document.getElementById('modal-catalogo-mencion-check');
        data.mencion_honorifica = (mencChk && mencChk.checked) ? '1' : '0';
    }

    const btn = document.querySelector('#modal-catalogo .cm-btn-success');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch(cfg.ruta, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
        body: new URLSearchParams(data)
    }).then(r => {
        if (r.redirected || r.ok) {
            location.reload();
        } else {
            throw new Error('Error al guardar');
        }
    }).catch(e => {
        document.getElementById('modal-catalogo-error').textContent = 'Error al guardar. Intente de nuevo.';
        document.getElementById('modal-catalogo-error').style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Guardar';
    });
}

function toggleFormNuevoRol() {
    const f = document.getElementById('form-nuevo-rol');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
}

function crearRol() {
    const nombre = document.getElementById('nuevo-rol-nombre').value.trim();
    if (!nombre) { showNotifyToast('warning', 'Escriba un nombre para el rol'); return; }
    fetch('<?php echo e(route("proyectos.gestion.involucrados.roles.crear")); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json'},
        body: JSON.stringify({ nombre })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const proyectoId = document.getElementById('rol-modal-proyecto-id').value;
            const invId = document.getElementById('rol-modal-inv-id').value;
            asignarRolConNombre(proyectoId, invId, data.id, nombre);
            document.getElementById('nuevo-rol-nombre').value = '';
            document.getElementById('form-nuevo-rol').style.display = 'none';
        }
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Emanuel\Desktop\Sistemax\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/proyectos/registro.blade.php ENDPATH**/ ?>