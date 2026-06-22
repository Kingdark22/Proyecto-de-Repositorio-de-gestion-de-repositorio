<div>
    <style>
        .cm-btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; padding: 0.55rem 0.95rem; font-size: 0.92rem; font-weight: 600; border: 1px solid transparent; cursor: pointer; transition: background-color 0.2s ease, transform 0.2s ease; text-decoration: none; }
        .cm-btn:hover { transform: translateY(-1px); }
        .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
        .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
        .cm-btn-secondary { background: #f4f4f4; border: 1px solid #c2c2c2; color: #222; }
        .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
        .cm-btn-info { background: #0d6efd; border-color: #0a58ca; color: #fff; }
        .cm-btn-sm { padding: 0.35rem 0.75rem; font-size: 0.85rem; }
        .email-option { padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 4px; cursor: pointer; transition: background 0.15s; }
        .email-option:hover { background: #f0f8ff; }
        .email-option.selected { background: #d4edda; border-color: #c3e6cb; }
    </style>

    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Proyectos Aprobados</h2>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mensaje): ?>
        <div style="background-color: <?php echo e($tipoMensaje === 'error' ? '#f8d7da' : '#d4edda'); ?>; color: <?php echo e($tipoMensaje === 'error' ? '#721c24' : '#155724'); ?>; border: 1px solid <?php echo e($tipoMensaje === 'error' ? '#f5c6cb' : '#c3e6cb'); ?>; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px; display: flex; justify-content: space-between; align-items: center;">
            <span><?php echo e($mensaje); ?></span>
            <a href="#" wire:click.prevent="limpiarMensaje" style="font-size:16px; font-weight:bold; text-decoration:none; color:inherit;">&times;</a>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPubId): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedProyecto): ?>
            <?php $proyecto = $selectedProyecto; ?>
            <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
                <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                    <?php echo e($proyecto->titulo ?? 'Proyecto no disponible'); ?>

                </legend>

                <div style="margin-bottom: 12px;">
                    <button type="button" wire:click="cerrar" class="cm-btn cm-btn-secondary cm-btn-sm">&larr; Volver</button>
                </div>

                <p><b>Resumen:</b> <?php echo e($proyecto->resumen ?? '(sin resumen)'); ?></p>
                <p><b>Fecha de aprobaci&oacute;n:</b> <?php echo e($proyecto->fecha_aprobacion ? $proyecto->fecha_aprobacion->format('d/m/Y') : '-'); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyecto->comunidad): ?>
                    <p><b>Comunidad:</b> <?php echo e($proyecto->comunidad->nombre); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php $docs = $proyecto->documentos; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($docs->isNotEmpty()): ?>
                    <hr style="border:none; border-top:1px solid #ccc; margin:15px 0;">
                    <h4 style="margin:0 0 10px 0;">Documentos del proyecto</h4>
                    <table width="100%" border="1" cellpadding="5" cellspacing="0"
                        style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
                        <thead>
                            <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                                <th width="5%">N&deg;</th>
                                <th width="65%">Documento</th>
                                <th width="30%">Archivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $docs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dIdx => $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <tr style="background-color: <?php echo e($dIdx % 2 == 0 ? '#FFFFFF' : '#E0E0E0'); ?>;" valign="top">
                                    <td align="center"><?php echo e($dIdx + 1); ?></td>
                                    <td><?php echo e($doc->componente?->nombre ?? 'Documento'); ?></td>
                                    <td align="center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doc->pd_archivo_path): ?>
                                            <a href="<?php echo e(route('documentos.serve', ['path' => $doc->pd_archivo_path])); ?>" target="_blank" style="color:#0000EE;font-weight:bold;">Ver PDF</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <hr style="border:none; border-top:1px solid #ccc; margin:15px 0;">

                <h4 style="margin:0 0 10px 0;">Comentarios</h4>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comentarios->isEmpty()): ?>
                    <p style="color:#777; font-size:12px; font-style:italic;">No hay comentarios todav&iacute;a.</p>
                <?php else: ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $comentarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <div style="background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; padding: 8px; margin-bottom: 6px;">
                            <div style="font-size: 10px; color: #888; margin-bottom: 3px;">
                                <?php echo e($c->fecha_creacion ? $c->fecha_creacion->format('d/m/Y h:i A') : ''); ?>

                                &middot;
                                <?php echo e($c->nombre_contacto ?? ($c->usuarioExterno?->nombre ?? 'Anónimo')); ?>

                            </div>
                            <div style="font-size: 13px;"><?php echo e($c->descripcion); ?></div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div style="margin-top: 12px;">
                    <textarea wire:model="nuevoComentario" rows="3"
                        style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px;"
                        placeholder="Escribe un comentario..."></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nuevoComentario'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="validation-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div style="text-align: right; margin-top: 6px;">
                        <button type="button" wire:click="comentar" class="cm-btn cm-btn-primary cm-btn-sm">Comentar</button>
                    </div>
                </div>
            </fieldset>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Proyectos Aprobados</legend>

            <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por t&iacute;tulo o resumen..." style="padding:4px 8px; border:1px solid #ccc; border-radius:4px; font-size:12px; min-width:200px; flex:1;">
                <select wire:model.live="filterComunidadId" style="padding:4px 8px; border:1px solid #ccc; border-radius:4px; font-size:12px; min-width:160px;">
                    <option value="">Todas las comunidades</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $comunidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <option value="<?php echo e($c->id); ?>"><?php echo e($c->nombre); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>

            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectos->isEmpty()): ?>
                <p style="color:#666; font-style:italic; padding: 10px;">No hay proyectos aprobados.</p>
            <?php else: ?>
                <table width="100%" border="1" cellpadding="5" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
                        <thead>
                        <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                            <th width="4%">N&deg;</th>
                            <th width="30%">T&iacute;tulo / Equipo</th>
                            <th width="20%">Resumen</th>
                            <th width="12%">Comunidad</th>
                            <th width="8%">Fecha</th>
                            <th width="26%">Acci&oacute;n</th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $proyectos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <tr style="background-color: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF'); ?>;" valign="top">
                                <td align="center"><?php echo e($loop->iteration); ?></td>
                                <td style="font-weight:bold;"><?php echo e($proy->titulo ?? 'N/A'); ?></td>
                                <td style="font-size:10px;"><?php echo e(\Illuminate\Support\Str::limit($proy->resumen ?? '', 60)); ?></td>
                                <td><?php echo e($proy->comunidad->nombre ?? '-'); ?></td>
                                <td align="center"><?php echo e($proy->fecha_aprobacion ? $proy->fecha_aprobacion->format('d/m/Y') : '-'); ?></td>
                                <td align="center">
                                    <button type="button" wire:click.prevent="seleccionar(<?php echo e($proy->id); ?>)"
                                        class="cm-btn cm-btn-secondary cm-btn-sm">Ver detalle</button>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </fieldset>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/livewire/proyectos-publicados-manager.blade.php ENDPATH**/ ?>