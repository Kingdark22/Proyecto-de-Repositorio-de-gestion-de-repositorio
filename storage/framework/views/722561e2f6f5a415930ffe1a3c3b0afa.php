<?php $__env->startSection('title', 'Gestión de Componentes'); ?>
<?php $__env->startSection('header', 'Gestión de Componentes'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .cm-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        padding: 0.55rem 0.95rem;
        min-width: 110px;
        font-size: 0.92rem;
        font-weight: 600;
        border: 1px solid transparent;
        cursor: pointer;
        transition: background-color 0.2s ease, transform 0.2s ease;
        text-decoration: none;
    }
    .cm-btn:hover { transform: translateY(-1px); }
    .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
    .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
    .cm-btn-warning { background: #f0b606; border-color: #d99e00; color: #212529; }
    .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
    .cm-btn-secondary { background: #f4f4f4; border-color: #c2c2c2; color: #222; }
    .cm-btn-sm { padding: 0.35rem 0.7rem; min-width: auto; font-size: 0.85rem; }
    .cm-btn-group button { margin-right: 0.35rem; margin-bottom: 0.25rem; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div id="flashContainer">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div data-flash-msg style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div style="margin-bottom: 15px; display: flex; align-items: center; gap: 12px;">
        <form method="GET" action="<?php echo e(route('componentes.index')); ?>" style="display: flex; align-items: center; gap: 8px; margin: 0;" id="searchForm">
            <b>Buscar:</b>
            <input name="search" type="text" value="<?php echo e($search); ?>" style="width: 350px; padding: 4px 6px; border-radius: 4px; border: 1px solid #999;" placeholder="Componente..." oninput="buscarConDebounce(this)">
        </form>
        <span style="margin-left: auto;"></span>
        <button type="button" onclick="window.location='<?php echo e(route('componentes.create')); ?>'" class="cm-btn cm-btn-success" style="font-size: 13px; padding: 6px 14px;">
            Nuevo Componente
        </button>
    </div>

    <div id="searchResults">
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Sistema de Componentes de Proyecto</legend>
            <div style="text-align: right; margin-bottom: 6px; font-size: 11px;">
                <a href="<?php echo e(route('componentes.vinculacion')); ?>" style="color: #19692e; text-decoration: none; border: 1px solid #19692e; padding: 3px 10px; border-radius: 4px; font-weight: bold; display: inline-block;">
                    + Vincular Componentes
                </a>
            </div>
            <table width="100%" border="1" cellpadding="5" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="5%">N&deg;</th>
                        <th width="20%">Nombre del Componente</th>
                        <th width="22%">Asignaciones (PNF &rarr; Trayecto)</th>
                        <th width="10%">Tipo Archivo</th>
                        <th width="8%">Tamaño</th>
                        <th width="8%">Obligatorio</th>
                        <th width="8%">Estatus</th>
                        <th width="14%">Configurar</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $listaRegistros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <?php $asignaciones = $item->programas; ?>
                        <tr style="background-color: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF'); ?>; <?php echo e(!$item->estado_logico ? 'color: #888;' : 'color: #000;'); ?>" valign="top">
                            <td align="center"><?php echo e($loop->iteration); ?></td>
                            <td align="center" style="font-weight: bold; padding: 8px;"><?php echo e($item->nombre); ?></td>
                            <td style="padding: 6px; font-size: 10px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asignaciones->isNotEmpty()): ?>
                                    <?php
                                        $pnfs = $asignaciones->groupBy('pro_codigo');
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pnfs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proCodigo => $asigsPnf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <?php
                                            $trayectos = $asigsPnf->pluck('tra_codigo')->filter()->map(function($t) { return 'T.'.$t; })->implode(', ');
                                        ?>
                                        <span style="display:inline-block; background:#e8f0fe; border:1px solid #b3d4fc; border-radius:3px; padding:2px 6px; margin:1px; white-space:nowrap; font-size:10px;">
                                            <b><?php echo e($asigsPnf->first()->programa_nombre); ?></b>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trayectos): ?> &rarr; <?php echo e($trayectos); ?><?php else: ?> <i>(todos)</i><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#999; font-style:italic;">Global</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td align="center" style="padding: 8px;">
                                <span style="font-weight:bold;text-transform:uppercase;"><?php echo e($item->tipo_archivo ?? 'pdf'); ?></span>
                            </td>
                            <td align="center" style="padding: 8px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->tamano_maximo_mb): ?>
                                    <span style="font-weight:bold;"><?php echo e($item->tamano_maximo_mb); ?> MB</span>
                                <?php else: ?>
                                    <span style="color:#999;">10 MB</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td align="center">
                                <?php echo $item->es_obligatorio
                                    ? '<span style="color: #FF0000; font-weight:bold;">S&Iacute;</span>'
                                    : '<span style="color: #008000; font-weight:bold;">NO</span>'; ?>

                            </td>
                            <td align="center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->estado_logico): ?>
                                    <span style="color: #008000; font-weight: bold;">Activo</span>
                                <?php else: ?>
                                    <span style="color: #FF0000; font-weight: bold;">Suspendido</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td align="center">
                                <div class="cm-btn-group" style="display: inline-flex; flex-wrap: wrap; justify-content: center;">
                                    <button type="button" onclick="window.location='<?php echo e(route('componentes.edit', $item->id)); ?>'" title="Editar" class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                    <button type="button" data-ajax-toggle="<?php echo e(route('componentes.toggle', $item->id)); ?>" data-toggle-name="<?php echo e($item->nombre); ?>" title="<?php echo e($item->estado_logico ? 'Suspender' : 'Activar'); ?>" class="cm-btn cm-btn-warning cm-btn-sm"><?php echo e($item->estado_logico ? 'Suspender' : 'Activar'); ?></button>
                                    <form method="POST" action="<?php echo e(route('componentes.destroy', $item->id)); ?>" style="display:inline;" >
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" title="Eliminar" class="cm-btn cm-btn-danger cm-btn-sm" data-ajax-delete data-delete-name="<?php echo e($item->nombre); ?>">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listaRegistros->isEmpty()): ?>
                        <tr>
                            <td colspan="8" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No hay componentes configurados en la Base de Datos.
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top: 10px;"><?php echo e($listaRegistros->links()); ?></div>
        </fieldset>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views\componentes\index.blade.php ENDPATH**/ ?>