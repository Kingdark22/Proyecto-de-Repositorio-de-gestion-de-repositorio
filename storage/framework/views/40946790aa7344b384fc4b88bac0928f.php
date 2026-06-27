<?php $__env->startSection('title', 'Gestión de Proyectos'); ?>
<?php $__env->startSection('header', 'Gestión de Proyectos'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .cm-btn {
        display: inline-flex;
        align-items: center; justify-content: center; border-radius: 6px;
        padding: 0.45rem 0.85rem; font-size: 0.85rem; font-weight: 600;
        border: 1px solid transparent; cursor: pointer;
        transition: background-color 0.2s ease, transform 0.2s ease;
        text-decoration: none;
    }
    .cm-btn { color: #fff; }
    td a.cm-btn:visited { color: #fff; }
    .cm-btn:hover { transform: translateY(-1px); }
    .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
    .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
    .cm-btn-warning { background: #f0b606; border-color: #d99e00; color: #212529; }
    .cm-btn-secondary { background: #f4f4f4; border-color: #c2c2c2; color: #222; }
    .cm-btn-sm { padding: 0.3rem 0.6rem; font-size: 0.8rem; }
    .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
    .filter-select, .filter-input { height: 30px; padding: 3px 6px; font-size: 11px; border: 1px solid #ccc; border-radius: 4px; }
    .filter-select { min-width: 130px; }
    .filter-input { width: 150px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;"><?php echo e(session('error')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($gruposDocente)): ?>
        <fieldset style="border: 2px solid #2e7d32; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
            <legend style="color: #2e7d32; font-weight: bold; font-style: italic; padding: 0 5px;">Equipos disponibles para registrar proyecto</legend>
            <table width="100%" border="1" cellpadding="4" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #a5d6a7; color: #000; text-align: center; font-weight: bold;">
                        <th width="25%">Nombre del equipo</th>
                        <th width="15%">PNF / Sección</th>
                        <th width="10%">Integrantes</th>
                        <th width="25%">Proyecto</th>
                        <th width="25%">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $gruposDocente; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <?php $g = (object) $g; ?>
                        <tr style="background: <?php echo e($loop->iteration % 2 == 0 ? '#E8F5E9' : '#FFF'); ?>;" valign="top">
                            <td style="padding:5px;font-weight:bold;"><?php echo e($g->nombre); ?></td>
                            <td style="padding:5px;font-size:10px;">
                                <?php echo e($g->pro_siglas ?? ''); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($g->sec_nombre): ?> · Secc. <?php echo e($g->sec_nombre); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td align="center" style="padding:5px;"><?php echo e($g->integrantes); ?></td>
                            <td align="center" style="padding:5px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($g->tiene_proyecto): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($g->proyecto_estado_validacion === 'aprobado'): ?>
                                        <span style="color:#008000;font-weight:bold;">Aprobado</span>
                                    <?php elseif($g->proyecto_estado_validacion === 'rechazado'): ?>
                                        <span style="color:#FF0000;font-weight:bold;">Rechazado</span>
                                    <?php else: ?>
                                        <span style="color:#d4a017;font-weight:bold;">En proceso</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#999;">Sin proyecto</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td align="center" style="padding:5px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($g->tiene_proyecto): ?>
                                    <a href="<?php echo e(route('proyectos.gestion.edit', $g->proyecto_id)); ?>" class="cm-btn cm-btn-success cm-btn-sm">Actualizar</a>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($g->proyecto_estado_validacion ?? '') === 'aprobado'): ?>
                                        <a href="<?php echo e(route('proyectos.gestion.solvencia', $g->proyecto_id)); ?>" class="cm-btn cm-btn-primary cm-btn-sm">Solvencia</a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <a href="<?php echo e(route('proyectos.gestion.desde-grupo', $g->grp_codigo)); ?>" class="cm-btn cm-btn-success cm-btn-sm">Actualizar</a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </fieldset>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($esEstudianteLider): ?>
        <fieldset style="border: 2px solid #2e7d32; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
            <legend style="color: #2e7d32; font-weight: bold; font-style: italic; padding: 0 5px;">Mis proyectos</legend>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectosLider->isNotEmpty()): ?>
                <table width="100%" border="1" cellpadding="4" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                    <thead>
                        <tr style="background-color: #a5d6a7; color: #000; text-align: center; font-weight: bold;">
                            <th width="30%">Proyecto</th>
                            <th width="20%">Comunidad</th>
                            <th width="20%">Validación</th>
                            <th width="30%">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $proyectosLider; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <tr style="background: <?php echo e($loop->iteration % 2 == 0 ? '#E8F5E9' : '#FFF'); ?>;" valign="top">
                                <td style="padding:5px;font-weight:bold;">
                                    <?php echo e($p->titulo); ?>

                                </td>
                                <td style="padding:5px;font-size:10px;"><?php echo e($p->comunidad->nombre ?? 'N/A'); ?></td>
                                <td align="center" style="padding:5px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->estado_validacion === 'completado'): ?>
                                        <span style="color: #2e7d32; font-weight: bold;">Completado</span>
                                    <?php elseif($p->estado_validacion === 'aprobado'): ?>
                                        <span style="color: #008000; font-weight: bold;">Aprobado</span>
                                    <?php elseif($p->estado_validacion === 'rechazado'): ?>
                                        <span style="color: #FF0000; font-weight: bold;" title="<?php echo e($p->motivo_rechazo); ?>">Rechazado</span>
                                    <?php else: ?>
                                        <span style="color: #d4a017; font-weight: bold;">En proceso</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td align="center" style="padding:5px;">
                                    <a href="<?php echo e(route('proyectos.gestion.edit', $p->id)); ?>" class="cm-btn cm-btn-success cm-btn-sm">Actualizar</a>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->estado_validacion === 'aprobado'): ?>
                                        <a href="<?php echo e(route('proyectos.gestion.solvencia', $p->id)); ?>" class="cm-btn cm-btn-primary cm-btn-sm">Solvencia</a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="font-size:11px;color:#666;padding:10px;">No tienes proyectos asignados como líder.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </fieldset>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarListado): ?>
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de proyectos institucionales</legend>
            <form method="GET" action="<?php echo e(route('proyectos.gestion')); ?>" style="margin-bottom:8px;">
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size:11px;">
                    <tr>
                        <td width="33%"><b>Título:</b><br>
                            <input name="search" type="text" value="<?php echo e($search); ?>" class="filter-input" style="width:95%;" placeholder="Buscar...">
                        </td>
                        <td width="33%"><b>Estado:</b><br>
                                <select name="estado" class="filter-select" style="width:95%;" onchange="this.form.submit()">
                                    <option value="">- Todos -</option>
                                    <option value="pendiente" <?php echo e($filterEstado == 'pendiente' ? 'selected' : ''); ?>>En proceso</option>
                                    <option value="completado" <?php echo e($filterEstado == 'completado' ? 'selected' : ''); ?>>Completado</option>
                                    <option value="aprobado" <?php echo e($filterEstado == 'aprobado' ? 'selected' : ''); ?>>Aprobado</option>
                                    <option value="rechazado" <?php echo e($filterEstado == 'rechazado' ? 'selected' : ''); ?>>Rechazado</option>
                                </select>
                        </td>
                        <td width="34%"><b>Comunidad:</b><br>
                            <select name="comunidad" class="filter-select" style="width:95%;" onchange="this.form.submit()">
                                <option value="">- Todas -</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($datosListado['comunidades'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $com): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($com->id); ?>" <?php echo e($filterComunidad == $com->id ? 'selected' : ''); ?>><?php echo e($com->nombre); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <noscript><button type="submit" class="cm-btn cm-btn-sm">Buscar</button></noscript>
            </form>

            <?php $proyectos = $datosListado['proyectos'] ?? collect(); ?>
            <table width="100%" border="1" cellpadding="4" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="25%">Título</th>
                        <th width="20%">Comunidad / equipo</th>
                        <th width="15%">Validación</th>
                        <th width="35%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $proyectos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <tr style="background: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFF'); ?>; color: #000;" valign="top">
                            <td style="padding:5px;font-weight:bold;">
                                <?php echo e($p->titulo); ?>

                            </td>
                            <td style="padding:5px;font-size:10px;">
                                Equipo: <?php echo e($p->equipo_resumen); ?><br>
                                Comunidad: <?php echo e($p->comunidad->nombre ?? 'N/A'); ?>

                            </td>
                            <td align="center" style="padding:5px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->estado_validacion === 'pendiente'): ?>
                                    <span style="color:#d4a017;font-weight:bold;">En proceso</span>
                                <?php elseif($p->estado_validacion === 'completado'): ?>
                                    <span style="color:#2e7d32;font-weight:bold;">Completado</span>
                                <?php elseif($p->estado_validacion === 'rechazado'): ?>
                                    <span style="color:#FF0000;font-weight:bold;" title="<?php echo e($p->motivo_rechazo); ?>">Rechazado</span>
                                <?php else: ?>
                                    <span style="color:#008000;font-weight:bold;">Aprobado</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td align="center" style="padding:5px;">
                                <div style="display:inline-flex;gap:4px;flex-wrap:wrap;justify-content:center;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($canValidate) && $p->estado_validacion === 'completado'): ?>
                                        <a href="<?php echo e(route('proyectos.gestion.approve', $p->id)); ?>" class="cm-btn cm-btn-success cm-btn-sm" onclick="return confirm('¿Aprueba este proyecto?')">Aprobar</a>
                                        <button type="button" class="cm-btn cm-btn-warning cm-btn-sm" onclick="abrirRechazar(<?php echo e($p->id); ?>)">Rechazar</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <a href="<?php echo e(route('proyectos.gestion.edit', $p->id)); ?>" class="cm-btn cm-btn-primary cm-btn-sm">Actualizar</a>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->estado_validacion === 'aprobado'): ?>
                                        <a href="<?php echo e(route('proyectos.gestion.solvencia', $p->id)); ?>" class="cm-btn cm-btn-primary cm-btn-sm">Solvencia</a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectos->isEmpty()): ?>
                        <tr><td colspan="4" align="center" style="padding:20px;font-weight:bold;">No hay expedientes registrados</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top:10px;"><?php echo e($proyectos->links()); ?></div>
        </fieldset>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div id="rejectModal" class="modal-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)cerrarRechazar()">
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
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/proyectos/index.blade.php ENDPATH**/ ?>