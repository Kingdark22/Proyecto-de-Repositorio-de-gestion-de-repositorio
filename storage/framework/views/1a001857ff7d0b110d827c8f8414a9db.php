<div class="ppm-manager">
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $intranetDisponible && $docentes->isEmpty() && $search === ''): ?>
        <div style="background-color: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; border-radius: 4px; margin-bottom: 15px; font-size: 13px; text-align: center;">
            El sistema está operando con la base de datos de respaldo.
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px; box-sizing: border-box;">
        <legend style="font-weight: bold; font-size: 12px;">Filtros</legend>
        <table class="ppm-filters-table" width="100%" border="0" cellpadding="8" cellspacing="0">
            <tr>
                <td width="30%"><b>Lapso académico:</b><br>
                    <select wire:model.live="lapsoFilter" style="width:100%;padding:6px 8px;border:1px solid #ccc;border-radius:4px;font-size:12px;">
                        <option value="">- Lapso -</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $lapsos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($lap->lap_codigo); ?>"><?php echo e($lap->lap_nombre); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </td>
                <td width="70%"><b>Buscar docente:</b><br>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cédula o nombre del docente..." style="width: 100%; padding:6px 8px;border:1px solid #ccc;border-radius:4px;font-size:12px;box-sizing:border-box;">
                </td>
            </tr>
        </table>
    </fieldset>

    <style>
        @keyframes ppmProgress {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(350%); }
        }
    </style>
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0; position: relative; box-sizing: border-box;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Docentes asignados al lapso vigente</legend>

        <div wire:loading.flex wire:target="search, lapsoFilter" 
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 10; justify-content: center; align-items: center; flex-direction: column; gap: 8px;">
            <div style="width: 200px; height: 4px; background: #e0e0e0; border-radius: 2px; overflow: hidden;">
                <div style="width: 40%; height: 100%; background: #8b0000; border-radius: 2px; animation: ppmProgress 1.2s ease-in-out infinite;"></div>
            </div>
            <span style="font-weight: bold; color: #8b0000; font-size: 12px;">Consultando docentes...</span>
        </div>

        <table width="100%" border="1" cellpadding="6" cellspacing="0" class="ppm-table" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px; position: relative; table-layout: fixed;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                    <th width="25%">Docente / cédula</th>
                    <th width="12%">PNF</th>
                    <th width="30%">Asignación intranet</th>
                    <th width="18%">Módulo</th>
                    <th width="15%">Estatus</th>
                </tr>
            </thead>
            <tbody class="Texto">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $docentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <?php
                        $cedula = $doc->cedula;
                        $habilitado = $doc->habilitado_modulo ?? false;
                    ?>
                    <tr style="background-color: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF'); ?>;" valign="top">
                        <td style="padding: 5px;">
                            <b><?php echo e($doc->nombre); ?> <?php echo e($doc->apellido); ?></b><br>
                            <span style="font-size: 10px;"><?php echo e($cedula); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim((string) auth()->user()->usu_cedula) === $cedula): ?>
                                <span style="color: #0000EE; font-size: 10px;"> (Tú)</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td align="center" style="padding: 5px; font-weight: bold; font-size: 11px;">
                            <?php echo e($doc->programa_siglas ?: '-'); ?>

                        </td>
                        <td style="padding: 5px; font-size: 10px;">
                            <strong>Lapso:</strong> <?php echo e($doc->lapso_nombre); ?><br>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $doc->asignaciones->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asig): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                &bull; <?php echo e($asig->unidad_siglas); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asig->programa_siglas): ?> (<?php echo e($asig->programa_siglas); ?>) <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                - Sec. <?php echo e($asig->seccion); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asig->trayecto_nombre): ?> / <?php echo e($asig->trayecto_nombre); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <br>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doc->asignaciones->count() > 3): ?>
                                <span style="color: #666;">+ <?php echo e($doc->asignaciones->count() - 3); ?> más</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td align="center" style="padding: 5px; font-size: 10px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($habilitado): ?>
                                <span style="display:inline-block;background:#e8f5e9;border:1px solid #198754;border-radius:4px;padding:2px 8px;font-weight:bold;color:#198754;font-size:10px;">HABILITADO</span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doc->ppm_anio): ?>
                                    <br><span style="font-size:9px;color:#555;"><?php echo e($doc->ppm_anio); ?> - Sec. <?php echo e($doc->ppm_seccion ?? '-'); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <span style="display:inline-block;background:#f5f5f5;border:1px solid #ccc;border-radius:4px;padding:2px 8px;color:#888;font-size:10px;">Solo intranet</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td align="center" style="padding: 5px;">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?php echo e($habilitado ? '#198754' : '#ccc'); ?>;"></span>
                            <span style="font-size:9px;color:#555;display:block;margin-top:2px;"><?php echo e($habilitado ? 'Activo' : 'Inactivo'); ?></span>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($docentes->isEmpty()): ?>
                    <tr>
                        <td colspan="5" align="center" style="padding: 20px;">
                            No hay docentes asignados para el lapso seleccionado.
                        </td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top: 10px;"><?php echo e($docentes->links()); ?></div>
    </fieldset>
</div>
<?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/livewire/project-professor-manager.blade.php ENDPATH**/ ?>