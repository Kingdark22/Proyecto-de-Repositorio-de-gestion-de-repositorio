<div>
    <style>
        .cm-btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; padding: 0.55rem 0.95rem; font-size: 0.92rem; font-weight: 600; border: 1px solid transparent; cursor: pointer; transition: background-color 0.2s ease, transform 0.2s ease; text-decoration: none; }
        .cm-btn:hover { transform: translateY(-1px); }
        .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
        .cm-btn-secondary { background: #f4f4f4; border: 1px solid #c2c2c2; color: #222; }
        .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
        .cm-btn-sm { padding: 0.35rem 0.75rem; font-size: 0.85rem; }
        .cm-tag { display: inline-block; background: #0d6efd; color: #fff; border-radius: 4px; padding: 2px 8px; font-size: 11px; font-weight: 600; }
    </style>



    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedProyecto): ?>
        <fieldset style="border: 2px solid #8b0000; border-radius: 8px; padding: 16px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 10px; font-size:15px;">
                Vincular: <?php echo e($selectedProyecto->titulo ?? 'Proyecto'); ?>

            </legend>

            <div style="margin-bottom: 16px; display:flex; align-items:center; gap:10px;">
                <button type="button" wire:click="cerrar" class="cm-btn cm-btn-secondary" style="font-size:13px;">&larr; Volver al listado</button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vinculacionExistente): ?>
                    <span class="cm-tag" style="background: #198754; font-size:12px; padding:3px 12px;">Ya vinculado</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <fieldset style="border: 1px solid #CCC; padding: 16px; margin-bottom: 16px; background:#fafafa;">
                <legend style="font-weight: bold; font-size: 14px; color:#333; padding: 0 8px;">Datos del proyecto vinculado</legend>
                <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 14px; border-collapse: separate; border-spacing: 0 6px;">
                    <tr>
                        <td width="130" style="font-weight:bold; vertical-align:top; color:#555; white-space:nowrap;">T&iacute;tulo:</td>
                        <td style="color:#222;"><?php echo e($selectedProyecto->titulo ?? '(sin t&iacute;tulo)'); ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; vertical-align:top; color:#555; white-space:nowrap;">Resumen:</td>
                        <td style="text-align:justify; color:#444; line-height:1.5;"><?php echo e($selectedProyecto->resumen ?? '(sin resumen)'); ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; vertical-align:top; color:#555; white-space:nowrap;">Comunidad:</td>
                        <td><?php echo e($selectedProyecto->comunidad?->nombre ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; vertical-align:top; color:#555; white-space:nowrap;">Equipo:</td>
                        <td>
                            <?php $integrantes = $integrantesProyecto ?? collect(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($integrantes->isNotEmpty()): ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $integrantes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <div style="display:inline-flex;align-items:center;background:#e8e8e8;border-radius:14px;padding:2px 10px 2px 4px;gap:5px;font-size:11px;">
                                            <span style="width:20px;height:20px;border-radius:50%;background:#8b0000;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:bold;flex-shrink:0;">
                                                <?php echo e(strtoupper(substr($i->nombre, 0, 1))); ?><?php echo e(strtoupper(substr($i->apellido, 0, 1))); ?>

                                            </span>
                                            <span style="font-weight:500;color:#333;"><?php echo e($i->nombre); ?> <?php echo e($i->apellido); ?></span>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i->rol): ?>
                                                <span style="background:#8b0000;color:#fff;border-radius:10px;padding:1px 7px;font-size:9px;font-weight:600;"><?php echo e($i->rol); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span style="color:#999;font-style:italic;"><?php echo e($selectedProyecto->equipo_resumen); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; vertical-align:top; color:#555; white-space:nowrap;">Clasificaci&oacute;n:</td>
                        <td>
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedProyecto->linea_investigacion): ?><span style="background:#e8f0fe;padding:3px 8px;border-radius:4px;font-size:12px;border:1px solid #c4d7f5;">L&iacute;nea: <?php echo e($selectedProyecto->linea_investigacion->nombre_investigacion); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedProyecto->metodologia): ?> <span style="background:#e8f0fe;padding:3px 8px;border-radius:4px;font-size:12px;border:1px solid #c4d7f5;">Metodolog&iacute;a: <?php echo e($selectedProyecto->metodologia->nombre); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedProyecto->tipo_publicacion): ?> <span style="background:#e8f0fe;padding:3px 8px;border-radius:4px;font-size:12px;border:1px solid #c4d7f5;">T. Publicaci&oacute;n: <?php echo e($selectedProyecto->tipo_publicacion->nombre); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedProyecto->tipo_investigacion): ?> <span style="background:#e8f0fe;padding:3px 8px;border-radius:4px;font-size:12px;border:1px solid #c4d7f5;">T. Investigaci&oacute;n: <?php echo e($selectedProyecto->tipo_investigacion->nombre); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; vertical-align:top; color:#555; white-space:nowrap;">Fecha aprobaci&oacute;n:</td>
                        <td style="color:#222;"><?php echo e($selectedProyecto->fecha_aprobacion ? $selectedProyecto->fecha_aprobacion->format('d/m/Y') : '-'); ?></td>
                    </tr>
                </table>

                
                <?php $docs = $selectedProyecto->documentos ?? collect(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($docs->isNotEmpty()): ?>
                    <div style="margin-top:12px; padding-top:12px; border-top:1px dashed #ddd;">
                        <b style="font-size:13px; color:#333;">Documentos del proyecto:</b>
                        <div style="margin-top:6px; display:flex; flex-wrap:wrap; gap:4px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $docs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <a href="<?php echo e(route('documentos.serve', ['path' => $doc->pd_archivo_path])); ?>" target="_blank"
                                    style="display:inline-flex;align-items:center;gap:4px; background:#f0f7ff; border:1px solid #b3d4fc; border-radius:5px; padding:5px 12px; font-size:12px; color:#004080; text-decoration:none;">
                                    <span style="font-size:14px;">&#128196;</span> <?php echo e($doc->componente?->nombre ?? 'Documento'); ?>

                                </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="margin-top:12px; padding-top:12px; border-top:1px dashed #ddd; font-size:13px; color:#999;">
                        <i>Este proyecto no tiene documentos asociados.</i>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </fieldset>

            <hr style="border:none; border-top:1px solid #ccc; margin:15px 0;">

            
            <fieldset style="border: 1px solid #CCC; padding: 16px; margin-bottom: 12px; background:#fafafa;">
                <legend style="font-weight: bold; font-size: 14px; color:#333; padding: 0 8px;">Datos de la vinculaci&oacute;n</legend>
                <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 14px; border-collapse: separate; border-spacing: 0 8px;">
                    <tr>
                        <td width="160" style="font-weight:bold; vertical-align:middle; color:#555; white-space:nowrap;">T&iacute;tulo de Vinculaci&oacute;n:
                            <span style="color:red;">*</span></td>
                        <td>
                            <input type="text" wire:model="vinculacionTitulo" style="width: 100%; padding: 8px 10px; border: 1px solid #bbb; border-radius: 5px; font-size: 14px; box-sizing:border-box;" placeholder="Ej: Proyecto de desarrollo comunitario...">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['vinculacionTitulo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div style="font-size:12px;color:#c62828;margin-top:3px;"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; vertical-align:middle; color:#555; white-space:nowrap;">Asociar Comunidad:</td>
                        <td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comunidadSeleccionada): ?>
                                <div style="background:#e8f5e9;border:1px solid #c8e6c9;border-radius:6px;padding:12px;">
                                    <div style="display:flex;align-items:center;gap:12px;">
                                        <div style="width:36px;height:36px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">&#10003;</div>
                                        <div style="flex:1;">
                                            <div style="font-weight:bold;font-size:14px;"><?php echo e($comunidadSeleccionada->nombre); ?></div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comunidadSeleccionada->rif): ?>
                                                <div style="font-size:12px;color:#555;">RIF: <?php echo e($comunidadSeleccionada->rif); ?></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php $dir = $comunidadSeleccionada->direccion; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dir && $dir->municipio): ?>
                                                <div style="font-size:12px;color:#555;">
                                                    Direcci&oacute;n: <?php echo e($dir->dir_calle ?? ''); ?>,
                                                    <?php echo e($dir->municipio->mun_nombre ?? ''); ?>,
                                                    <?php echo e($dir->municipio?->estado?->est_nombre ?? ''); ?>

                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <button type="button" wire:click="quitarComunidad" class="cm-btn cm-btn-secondary" style="font-size:12px;padding:6px 14px;">Cambiar</button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <select wire:model="vinculacionComunidadId" style="flex:1;padding:8px 10px;border:1px solid #bbb;border-radius:5px;font-size:14px;background:#fff;">
                                        <option value="">Seleccione comunidad...</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $comunidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $com): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <option value="<?php echo e($com->id); ?>"><?php echo e($com->nombre); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($com->rif): ?>(<?php echo e($com->rif); ?>)<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                    <button type="button" wire:click="abrirModalComunidad" class="cm-btn cm-btn-primary" style="white-space:nowrap;padding:8px 14px;font-size:13px;" title="Crear nueva comunidad">+ Nueva</button>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <div style="text-align: right; margin-top: 16px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" wire:click="cerrar" class="cm-btn cm-btn-secondary" style="font-size:14px; padding:8px 20px;">Cancelar</button>
                <button type="button" wire:click="guardarVinculacion" class="cm-btn cm-btn-success" style="font-size:14px; padding:8px 24px;">
                    <?php echo e($vinculacionExistente ? 'Actualizar' : 'Guardar'); ?> Vinculaci&oacute;n
                </button>
            </div>
        </fieldset>
    <?php else: ?>
        <fieldset style="border: 2px solid #8b0000; border-radius: 8px; padding: 16px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 10px; font-size:15px;">Vinculaci&oacute;n de Proyectos</legend>

            <div style="margin-bottom: 14px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por t&iacute;tulo..." style="padding:6px 10px; border:1px solid #ccc; border-radius:5px; font-size:13px; min-width:250px; flex:1;">
                <span style="font-size: 13px; color: #555;">
                    <b><?php echo e($proyectos->total()); ?></b> proyecto(s)
                </span>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectos->isEmpty()): ?>
                <p style="color:#666; font-style:italic; padding: 10px;">No hay proyectos aprobados.</p>
            <?php else: ?>
                <table width="100%" border="1" cellpadding="6" cellspacing="0"
                    style="border-collapse: collapse; border-color: #ccc; font-size: 12px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                            <th width="5%" style="padding:8px 4px;">N&deg;</th>
                            <th width="30%" style="padding:8px 4px;">T&iacute;tulo</th>
                            <th width="15%" style="padding:8px 4px;">Comunidad</th>
                            <th width="10%" style="padding:8px 4px;">Fecha Aprob.</th>
                            <th width="20%" style="padding:8px 4px;">Vinculaci&oacute;n</th>
                            <th width="20%" style="padding:8px 4px;">Acci&oacute;n</th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $proyectos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <?php
                                $vin = $vinculaciones[$proy->id] ?? null;
                                $rowNum = ($proyectos->currentPage() - 1) * $proyectos->perPage() + $loop->iteration;
                            ?>
                            <tr style="background-color: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF'); ?>;" valign="top">
                                <td align="center" style="padding:6px 4px;"><?php echo e($rowNum); ?></td>
                                <td style="font-weight:bold; padding:6px 4px;"><?php echo e($proy->titulo ?? 'N/A'); ?></td>
                                <td style="padding:6px 4px;"><?php echo e($proy->comunidad->nombre ?? '-'); ?></td>
                                <td align="center" style="padding:6px 4px;"><?php echo e($proy->fecha_aprobacion ? $proy->fecha_aprobacion->format('d/m/Y') : '-'); ?></td>
                                <td align="center" style="padding:6px 4px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vin): ?>
                                        <span class="cm-tag" style="background: #198754; font-size:11px;">Vinculado</span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vin->comunidad): ?>
                                            <div style="font-size:11px;color:#555;margin-top:3px;"><?php echo e($vin->comunidad->nombre); ?></div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        <span style="color:#999;">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td align="center" style="padding:6px 4px;">
                                    <button type="button" wire:click="vincular(<?php echo e($proy->id); ?>)" class="cm-btn cm-btn-primary cm-btn-sm" style="font-size:12px;">
                                        <?php echo e($vin ? 'Editar' : 'Vincular'); ?>

                                    </button>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
                <div style="margin-top: 12px;">
                    <?php echo e($proyectos->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </fieldset>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarModalComunidad): ?>
        <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:10px;padding:24px;max-width:480px;width:92%;max-height:90vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#8b0000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:bold;">C</div>
                    <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Comunidad</h3>
                </div>

                <div style="margin-bottom: 14px;">
                    <b style="font-size:12px;color:#555;">Buscar comunidad existente:</b>
                    <input wire:model.live="buscarComunidad" type="text" style="width:100%;padding:8px 10px;border:1px solid #ccc;border-radius:6px;box-sizing:border-box;margin-top:4px;font-size:13px;" placeholder="Escriba nombre o RIF...">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comunidadesEncontradas->isNotEmpty()): ?>
                        <div style="margin-top:6px;border:1px solid #e0e0e0;border-radius:6px;max-height:180px;overflow-y:auto;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $comunidadesEncontradas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $com): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <div wire:click="seleccionarComunidadModal(<?php echo e($com->id); ?>)" style="padding:8px 10px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:12px;transition:background 0.15s;"
                                     onmouseover="this.style.background='#f5f0f0';this.style.borderLeft='3px solid #8b0000'" onmouseout="this.style.background='';this.style.borderLeft=''">
                                    <b style="color:#8b0000;"><?php echo e($com->nombre); ?></b>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($com->rif): ?><br><small style="color:#888;">RIF: <?php echo e($com->rif); ?></small><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($buscarComunidad && $comunidadesEncontradas->isEmpty()): ?>
                        <div style="margin-top:4px;font-size:11px;color:#999;padding:4px 0;">No se encontraron comunidades. Cree una nueva abajo.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <hr style="border:none;border-top:1px solid #e8e8e8;margin:14px 0;">

                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                    <div style="width:24px;height:24px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:bold;">+</div>
                    <b style="font-size:13px;color:#333;">O crear nueva comunidad</b>
                </div>
                <table width="100%" style="font-size:12px;margin-top:4px;border-collapse:separate;border-spacing:0 6px;">
                    <tr>
                        <td width="30%"><b>Nombre:</b> <span style="color:red;">*</span></td>
                        <td><input wire:model="modalComunidadNombre" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;" placeholder="Nombre de la comunidad"></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['modalComunidadNombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <tr><td></td><td class="validation-error" style="font-size:11px;color:#c62828;"><?php echo e($message); ?></td></tr> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <tr>
                        <td><b>RIF:</b></td>
                        <td><input wire:model="modalComunidadRif" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;" placeholder="Opcional"></td>
                    </tr>
                </table>

                <div style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center;">
                    <button type="button" class="cm-btn cm-btn-success" wire:click="guardarComunidadModal" style="padding:8px 20px;font-size:13px;">Guardar comunidad</button>
                    <button type="button" class="cm-btn cm-btn-secondary" wire:click="cerrarModalComunidad" style="padding:8px 20px;font-size:13px;">Cancelar</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/livewire/vinculacion-manager.blade.php ENDPATH**/ ?>