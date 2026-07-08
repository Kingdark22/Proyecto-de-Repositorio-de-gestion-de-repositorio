<div>
    <style>
        .cm-btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; padding: 0.55rem 0.95rem; font-size: 0.92rem; font-weight: 600; border: 1px solid transparent; cursor: pointer; transition: background-color 0.2s ease, transform 0.2s ease; text-decoration: none; }
        .cm-btn:hover { transform: translateY(-1px); }
        .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
        .cm-btn-secondary { background: #f4f4f4; border: 1px solid #c2c2c2; color: #222; }
        .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
        .cm-btn-danger { background: #c62828; border-color: #a02121; color: #fff; }
        .cm-btn-sm { padding: 0.35rem 0.75rem; font-size: 0.85rem; }
        .cm-tag { display: inline-block; background: #0d6efd; color: #fff; border-radius: 4px; padding: 2px 8px; font-size: 11px; font-weight: 600; }
        .sel-checkbox { width: 16px; height: 16px; accent-color: #8b0000; cursor: pointer; vertical-align: middle; }
        tr.sel-row { cursor: pointer; }
        tr.sel-row:hover td { background: #f5f0f0 !important; }
        tr.selected { background-color: #fce4e4 !important; }
        .grupo-card { border: 1px solid #c8e6c9; border-radius: 8px; padding: 14px; margin-bottom: 12px; background: #f1faf1; }
        .grupo-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .paso-indicator { display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 20px; }
        .paso-step { display: flex; align-items: center; }
        .paso-circle { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; border: 2px solid #ccc; color: #999; background: #fff; }
        .paso-circle.active { border-color: #8b0000; color: #fff; background: #8b0000; }
        .paso-circle.done { border-color: #198754; color: #fff; background: #198754; }
        .paso-label { font-size: 11px; color: #999; margin-top: 4px; text-align: center; }
        .paso-label.active { color: #8b0000; font-weight: bold; }
        .paso-label.done { color: #198754; }
        .paso-line { width: 50px; height: 2px; background: #ccc; margin: 0 4px; }
        .paso-line.active { background: #8b0000; }
        .paso-line.done { background: #198754; }
        .uppercase { text-transform: uppercase; }
        .contenido-uppercase td, .contenido-uppercase th, .contenido-uppercase div, .contenido-uppercase span { text-transform: uppercase; }
        .wizard-titulo { text-transform: uppercase; }
    </style>


    
    <fieldset style="border: 2px solid #8b0000; border-radius: 8px; padding: 16px;" class="contenido-uppercase">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 10px; font-size:15px;">
            Adjuntar los Proyectos
        </legend>

        <div style="display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap;">
            <button type="button" wire:click="abrirWizard" class="cm-btn cm-btn-success" style="font-size:13px;">
                + Vincular Proyectos
            </button>
            <button type="button" wire:click="abrirModalReporte" class="cm-btn cm-btn-primary" style="font-size:13px;">
                &darr; Reporte de Proyectos
            </button>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vinculacionesAgrupadas->isEmpty()): ?>
            <p style="color:#666; font-style:italic; padding: 10px;">No hay vinculaciones registradas.</p>
        <?php else: ?>
            <div style="margin-top:10px;">
                <table width="100%" style="font-size:12px;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#8bb2b7;color:#000;font-weight:bold;">
                            <th width="4%" style="padding:8px 4px;text-align:center;">N&deg;</th>
                            <th width="25%" style="padding:8px 4px;">Proyecto</th>
                            <th width="15%" style="padding:8px 4px;">Título vinculación</th>
                            <th width="12%" style="padding:8px 4px;">Sede</th>
                            <th width="7%" style="padding:8px 4px;">Lapso</th>
                            <th width="15%" style="padding:8px 4px;">Comunidad</th>
                            <th width="10%" style="padding:8px 4px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $vinculacionesAgrupadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupoKey => $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $grupo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <?php
                                    $vSede = $v->sede ?? '';
                                    $vLapso = $v->lapso_nombre ?? '';
                                ?>
                                <tr style="background:<?php echo e(($loop->parent->iteration + $idx) % 2 == 0 ? '#E0E0E0' : '#FFF'); ?>;" valign="top">
                                    <td align="center" style="padding:6px 4px;"><?php echo e($loop->parent->iteration); ?></td>
                                    <td style="font-weight:bold;padding:6px 4px;"><?php echo e($v->proyecto->titulo ?? 'N/A'); ?></td>
                                    <td style="padding:6px 4px;"><?php echo e($v->titulo); ?></td>
                                    <td style="padding:6px 4px;font-size:11px;"><?php echo e($vSede); ?></td>
                                    <td style="padding:6px 4px;font-size:11px;"><?php echo e($vLapso); ?></td>
                                    <td style="padding:6px 4px;"><?php echo e($v->comunidad?->nombre ?? '-'); ?></td>
                                    <td align="center" style="padding:6px 4px;">
                                        <button type="button" wire:click="quitarVinculacion(<?php echo e($v->proyecto_id); ?>)" style="background:none;border:1px solid #c62828;color:#c62828;border-radius:4px;padding:3px 10px;font-size:11px;cursor:pointer;font-weight:600;" onmouseover="this.style.background='#c62828';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='#c62828'">Quitar</button>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </fieldset>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarWizard): ?>
        <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:10px;padding:24px;max-width:750px;width:94%;max-height:92vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">

                
                <div class="paso-indicator">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [1 => 'Título', 2 => 'Comunidad', 3 => 'Proyectos', 4 => 'Confirmar']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <div class="paso-step" style="flex-direction:column;">
                            <div class="paso-circle <?php echo e($pasoActual == $num ? 'active' : ''); ?> <?php echo e($pasoActual > $num ? 'done' : ''); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pasoActual > $num): ?> &#10003; <?php else: ?> <?php echo e($num); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="paso-label <?php echo e($pasoActual == $num ? 'active' : ''); ?> <?php echo e($pasoActual > $num ? 'done' : ''); ?>"><?php echo e($label); ?></div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($num < 4): ?>
                            <div class="paso-line <?php echo e($pasoActual > $num ? 'done' : ($pasoActual == $num ? 'active' : '')); ?>"></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>

                <hr style="border:none;border-top:1px solid #e0e0e0;margin:0 0 16px 0;">

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pasoActual === 1): ?>
                    <h4 style="margin:0 0 16px 0;font-size:15px;color:#333;">Paso 1: Seleccionar Título de Vinculación</h4>
                    <div style="margin-bottom:4px;font-size:11px;color:#888;">
                        Títulos cargados: <?php echo e(count($titulosDisponibles)); ?>

                    </div>
                    <div style="margin-bottom:12px;">
                        <select wire:model.live="tituloSeleccionado" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('titulo-vinculacion-select', get_defined_vars()); ?>wire:key="titulo-vinculacion-select" style="width:100%;padding:10px 12px;border:2px solid #8b0000;border-radius:6px;font-size:15px;box-sizing:border-box;">
                            <option value="">Seleccionar un título para la vinculación</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $titulosDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tid => $ttitulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <option value="<?php echo e($tid); ?>"><?php echo e($ttitulo); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <option value="" disabled>— No hay títulos disponibles —</option>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <option value="nuevo">[ + Crear nuevo título ]</option>
                        </select>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tituloSeleccionado === 'nuevo'): ?>
                        <div style="margin-bottom:12px;">
                            <input type="text" wire:model.live="nuevoTitulo" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('nuevo-titulo-input', get_defined_vars()); ?>wire:key="nuevo-titulo-input" style="width:100%;padding:10px 12px;border:2px solid #8b0000;border-radius:6px;font-size:15px;box-sizing:border-box;" placeholder="Escriba el nombre del nuevo título...">
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pasoActual === 2): ?>
                    <h4 style="margin:0 0 16px 0;font-size:15px;color:#333;">Paso 2: Seleccionar Comunidad</h4>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tituloSeleccionado): ?>
                        <div style="background:#fff3e0;border:1px solid #ffe0b2;border-radius:6px;padding:8px 12px;margin-bottom:12px;font-size:13px;">
                            <span style="font-weight:bold;color:#e65100;">Título seleccionado:</span>
                            <span style="color:#333;" class="wizard-titulo">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tituloSeleccionado === 'nuevo'): ?>
                                    <?php echo e($nuevoTitulo); ?>

                                <?php else: ?>
                                    <?php echo e($titulosDisponibles[(int)$tituloSeleccionado] ?? 'N/A'); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                            <button type="button" wire:click="pasoEspecifico(1)" style="background:none;border:none;color:#8b0000;text-decoration:underline;cursor:pointer;font-size:12px;margin-left:8px;">Cambiar</button>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comunidadSeleccionada): ?>
                        <div style="background:#e8f5e9;border:2px solid #198754;border-radius:8px;padding:16px;margin-bottom:12px;">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div style="width:40px;height:40px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">&#10003;</div>
                                <div style="flex:1;">
                                    <div style="font-weight:bold;font-size:16px;"><?php echo e($comunidadSeleccionada->nombre); ?></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comunidadSeleccionada->rif): ?>
                                        <div style="font-size:13px;color:#555;">RIF: <?php echo e($comunidadSeleccionada->rif); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <button type="button" wire:click="quitarComunidad" class="cm-btn cm-btn-secondary" style="font-size:13px;padding:8px 16px;">Cambiar</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="margin-bottom:12px;">
                            <select wire:model.live="comunidadId" style="width:100%;padding:10px 12px;border:2px solid #8b0000;border-radius:6px;font-size:15px;box-sizing:border-box;background:#fff;">
                                <option value="">Seleccione una comunidad...</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($comunidades ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $com): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($com->id); ?>"><?php echo e($com->nombre); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($com->rif): ?>(<?php echo e($com->rif); ?>)<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <div style="text-align:center;margin:8px 0;">
                            <button type="button" wire:click="abrirModalComunidad" class="cm-btn cm-btn-primary" style="font-size:13px;padding:8px 20px;">+ Crear nueva comunidad</button>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pasoActual === 3): ?>
                    <h4 style="margin:0 0 16px 0;font-size:15px;color:#333;">Paso 3: Seleccionar Proyectos</h4>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tituloSeleccionado): ?>
                        <div style="background:#fff3e0;border:1px solid #ffe0b2;border-radius:6px;padding:8px 12px;margin-bottom:8px;font-size:13px;">
                            <div style="display:flex;align-items:center;gap:4px;">
                                <span style="font-weight:bold;color:#e65100;">Título:</span>
                                <span style="color:#333;" class="wizard-titulo">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tituloSeleccionado === 'nuevo'): ?>
                                        <?php echo e($nuevoTitulo); ?>

                                    <?php else: ?>
                                        <?php echo e($titulosDisponibles[(int)$tituloSeleccionado] ?? 'N/A'); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                                <button type="button" wire:click="pasoEspecifico(1)" style="background:none;border:none;color:#8b0000;text-decoration:underline;cursor:pointer;font-size:12px;margin-left:4px;">Cambiar</button>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comunidadSeleccionada): ?>
                                <div style="display:flex;align-items:center;gap:4px;margin-top:2px;">
                                    <span style="font-weight:bold;color:#198754;">Comunidad:</span>
                                    <span style="color:#333;"><?php echo e($comunidadSeleccionada->nombre); ?></span>
                                    <button type="button" wire:click="pasoEspecifico(2)" style="background:none;border:none;color:#8b0000;text-decoration:underline;cursor:pointer;font-size:12px;margin-left:4px;">Cambiar</button>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php
                        $yaVinculados = $proyectos ? $proyectos->filter(fn($p) => $p->vinculaciones->isNotEmpty()) : collect();
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($yaVinculados && $yaVinculados->isNotEmpty()): ?>
                        <div style="background:#fff8e1;border:1px solid #ffe0b2;border-radius:6px;padding:10px 12px;margin-bottom:12px;">
                            <div style="font-size:12px;font-weight:bold;color:#e65100;margin-bottom:4px;">Proyectos ya vinculados</div>
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $yaVinculados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <?php $vinc = $yv->vinculaciones->first(); ?>
                                    <span style="display:inline-flex;align-items:center;gap:4px;background:#fff;border:1px solid #ffe0b2;border-radius:4px;padding:2px 8px;font-size:11px;">
                                        <?php echo e($yv->titulo ?? 'N/A'); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vinc): ?>
                                            <span style="color:#888;">(<?php echo e($vinc->titulo); ?> &rarr; <?php echo e($vinc->comunidad?->nombre ?? '?'); ?>)</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div style="margin-bottom:12px;display:flex;gap:10px;align-items:center;">
                        <input wire:model.live.debounce.50ms="search" type="text" placeholder="Buscar proyecto por título, comunidad o cédula..." style="padding:8px 10px;border:2px solid #8b0000;border-radius:6px;font-size:14px;min-width:200px;flex:1;">
                        <span style="font-size:13px;color:#555;">
                            <b><?php echo e($proyectos->total() ?? 0); ?></b> proyecto(s)
                        </span>
                        <button type="button" wire:click="toggleSelectAll" style="background:none;border:1px solid #8b0000;color:#8b0000;border-radius:4px;padding:5px 12px;font-size:12px;cursor:pointer;font-weight:600;white-space:nowrap;" onmouseover="this.style.background='#8b0000';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='#8b0000'">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedProjects) > 0 && count($selectedProjects) >= ($proyectos->total() ?? 0)): ?>
                                Deseleccionar todo
                            <?php else: ?>
                                Seleccionar todo
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedProjects) > 0): ?>
                            <span style="background:#8b0000;color:#fff;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:bold;">
                                <?php echo e(count($selectedProjects)); ?> seleccionado(s)
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectos && $proyectos->isNotEmpty()): ?>
                        <table width="100%" border="1" cellpadding="6" cellspacing="0"
                            style="border-collapse:collapse;border-color:#ccc;font-size:12px;">
                            <thead>
                                <tr style="background:#8bb2b7;color:#000;font-weight:bold;">
                                    <th width="4%" style="padding:8px 4px;text-align:center;">&nbsp;</th>
                                    <th width="5%" style="padding:8px 4px;">N&deg;</th>
                                    <th width="35%" style="padding:8px 4px;">Proyecto</th>
                                    <th width="18%" style="padding:8px 4px;">Comunidad</th>
                                    <th width="22%" style="padding:8px 4px;">Vinculación</th>
                                    <th width="16%" style="padding:8px 4px;">Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $proyectos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <?php
                                        $vin = $proy->vinculaciones->first();
                                        $rowNum = ($proyectos->currentPage() - 1) * $proyectos->perPage() + $loop->iteration;
                                        $isSelected = in_array($proy->id, $selectedProjects);
                                    ?>
                                    <tr style="background:<?php echo e($isSelected ? '#fce4e4' : ($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFF')); ?>;<?php echo e($isSelected ? 'outline:2px solid #8b0000;outline-offset:-2px;' : ''); ?>" valign="top" class="sel-row" wire:click="toggleProject(<?php echo e($proy->id); ?>)">
                                        <td align="center" style="padding:6px 4px;" onclick="event.stopPropagation()">
                                            <input type="checkbox" wire:model.live="selectedProjects" value="<?php echo e($proy->id); ?>" class="sel-checkbox">
                                        </td>
                                        <td align="center" style="padding:6px 4px;"><?php echo e($rowNum); ?></td>
                                        <td style="font-weight:bold;padding:6px 4px;">
                                            <div><?php echo e($proy->titulo ?? 'N/A'); ?></div>
                                            <div style="font-size:10px;color:#888;margin-top:1px;"><?php echo e($proy->equipo_ref ?? ''); ?></div>
                                        </td>
                                        <td style="padding:6px 4px;"><?php echo e($proy->comunidad->nombre ?? '-'); ?></td>
                                        <td align="center" style="padding:6px 4px;">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vin): ?>
                                                <span class="cm-tag" style="background:#198754;font-size:11px;">Vinculado</span>
                                                <div style="font-size:10px;color:#333;margin-top:2px;"><?php echo e($vin->titulo); ?></div>
                                            <?php else: ?>
                                                <span style="color:#999;">-</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td align="center" style="padding:6px 4px;">
                                            <button type="button" wire:click="verDetalle(<?php echo e($proy->id); ?>)" class="cm-btn cm-btn-secondary cm-btn-sm" style="font-size:11px;">Ver</button>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                        <div style="margin-top:10px;"><?php echo e($proyectos->links()); ?></div>
                    <?php else: ?>
                        <p style="color:#666;font-style:italic;padding:10px;">No hay proyectos aprobados.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pasoActual === 4): ?>
                    <h4 style="margin:0 0 16px 0;font-size:15px;color:#333;">Paso 4: Confirmar Vinculación</h4>
                    <div style="background:#f9f9f9;border:1px solid #e0e0e0;border-radius:8px;padding:16px;margin-bottom:16px;">
                        <table style="font-size:14px;border-collapse:separate;border-spacing:0 10px;">
                            <tr>
                                <td style="font-weight:bold;color:#555;padding-right:20px;">Título:</td>
                                <td>
                                    <span style="font-weight:bold;color:#19692e;" class="wizard-titulo">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tituloSeleccionado === 'nuevo'): ?>
                                            <?php echo e($nuevoTitulo); ?>

                                        <?php else: ?>
                                            <?php echo e($titulosDisponibles[(int)$tituloSeleccionado] ?? 'N/A'); ?>

                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;color:#555;padding-right:20px;">Comunidad:</td>
                                <td><span style="font-weight:bold;"><?php echo e($comunidadSeleccionada?->nombre ?? 'N/A'); ?></span></td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;color:#555;padding-right:20px;">Proyectos a vincular:</td>
                                <td><span style="font-weight:bold;color:#8b0000;"><?php echo e(count($selectedProjects)); ?> proyecto(s)</span></td>
                            </tr>
                        </table>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedProjects) > 0): ?>
                            <div style="margin-top:10px;font-size:12px;color:#555;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $selectedProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <?php
                                        $p = \App\Models\Proyecto::find($pid);
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p): ?>
                                        <span style="display:inline-block;background:#fff;border:1px solid #c8e6c9;border-radius:4px;padding:3px 8px;margin:2px;"><?php echo e($p->titulo ?? 'ID:'.$pid); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <hr style="border:none;border-top:1px solid #e0e0e0;margin:16px 0;">

                
                <div style="display:flex;gap:10px;justify-content:space-between;">
                    <div>
                        <button type="button" wire:click="cerrarWizard" class="cm-btn cm-btn-secondary" style="font-size:14px;padding:8px 20px;">Cancelar</button>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pasoActual > 1): ?>
                            <button type="button" wire:click="pasoAnterior" class="cm-btn cm-btn-secondary" style="font-size:14px;padding:8px 20px;">&larr; Anterior</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pasoActual < 4): ?>
                            <button type="button" wire:click="siguientePaso" class="cm-btn cm-btn-success" style="font-size:14px;padding:8px 24px;">Siguiente &rarr;</button>
                        <?php else: ?>
                            <button type="button" wire:click="guardarVinculacion" class="cm-btn cm-btn-success" style="font-size:14px;padding:8px 24px;">Guardar Vinculación</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarModalReporte): ?>
        <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:10px;padding:24px;max-width:520px;width:92%;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#8b0000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:bold;">R</div>
                    <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Reporte de Vinculaciones</h3>
                </div>

                <div style="margin-bottom:16px;">
                    <b style="font-size:13px;color:#555;display:block;margin-bottom:8px;">Tipo de reporte:</b>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:8px 14px;border:2px solid <?php echo e($tipoReporte === 'titulo' ? '#8b0000' : '#ddd'); ?>;border-radius:8px;background:<?php echo e($tipoReporte === 'titulo' ? '#fff0f0' : '#fafafa'); ?>;flex:1;min-width:120px;transition:all 0.2s;">
                            <input type="radio" wire:model.live="tipoReporte" value="titulo" style="accent-color:#8b0000;">
                            Por título
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:8px 14px;border:2px solid <?php echo e($tipoReporte === 'lapso' ? '#8b0000' : '#ddd'); ?>;border-radius:8px;background:<?php echo e($tipoReporte === 'lapso' ? '#fff0f0' : '#fafafa'); ?>;flex:1;min-width:120px;transition:all 0.2s;">
                            <input type="radio" wire:model.live="tipoReporte" value="lapso" style="accent-color:#8b0000;">
                            Por lapso académico
                        </label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedProjects) > 0): ?>
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:8px 14px;border:2px solid <?php echo e($tipoReporte === 'wizard' ? '#8b0000' : '#ddd'); ?>;border-radius:8px;background:<?php echo e($tipoReporte === 'wizard' ? '#fff0f0' : '#fafafa'); ?>;flex:1;min-width:120px;transition:all 0.2s;">
                                <input type="radio" wire:model.live="tipoReporte" value="wizard" style="accent-color:#8b0000;">
                                Selección actual (<?php echo e(count($selectedProjects)); ?> proyecto(s))
                            </label>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tipoReporte === 'titulo'): ?>
                    <div style="margin-bottom:16px;">
                        <b style="font-size:13px;color:#555;display:block;margin-bottom:6px;">Seleccione el título:</b>
                        <select wire:model="reporteTituloId" style="width:100%;padding:10px 12px;border:2px solid #8b0000;border-radius:6px;font-size:13px;background:#fff;cursor:pointer;outline:none;font-family:inherit;color:#222;">
                            <option value="">— Seleccione título —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $titulosReporte; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tid => $ttitulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <option value="<?php echo e($tid); ?>"><?php echo e($ttitulo); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                <?php elseif($tipoReporte === 'lapso'): ?>
                    <div style="margin-bottom:16px;">
                        <b style="font-size:13px;color:#555;display:block;margin-bottom:6px;">Seleccione el lapso académico:</b>
                        <select wire:model="reporteLapsoId" style="width:100%;padding:10px 12px;border:2px solid #8b0000;border-radius:6px;font-size:13px;background:#fff;cursor:pointer;outline:none;font-family:inherit;color:#222;">
                            <option value="">— Seleccione lapso —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $lapsosReporte; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lid => $lnombre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <option value="<?php echo e($lid); ?>"><?php echo e($lnombre); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($lapsosReporte)): ?>
                            <div style="margin-top:6px;font-size:12px;color:#888;font-style:italic;">No hay lapsos disponibles con vinculaciones registradas.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php elseif($tipoReporte === 'wizard'): ?>
                    <div style="margin-bottom:16px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:6px;padding:12px;">
                        <div style="font-size:12px;color:#555;margin-bottom:6px;">
                            Se generará un reporte con los <b><?php echo e(count($selectedProjects)); ?></b> proyecto(s) seleccionados actualmente en el wizard.
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $selectedProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <?php
                                    $p = \App\Models\Proyecto::find($pid);
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p): ?>
                                    <span style="display:inline-block;background:#fff;border:1px solid #c8e6c9;border-radius:3px;padding:2px 6px;font-size:10px;"><?php echo e($p->titulo ?? 'ID:'.$pid); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div style="margin-top:20px;text-align:right;display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="cm-btn cm-btn-secondary" wire:click="cerrarModalReporte" style="padding:10px 24px;font-size:13px;border-radius:6px;">Cancelar</button>
                    <button type="button" class="cm-btn cm-btn-primary" wire:click="generarReporte" style="padding:10px 24px;font-size:13px;border-radius:6px;">Generar PDF</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarModalComunidad): ?>
        <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:10000;display:flex;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:10px;padding:24px;max-width:600px;width:94%;max-height:90vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#8b0000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:bold;">C</div>
                    <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Nueva Comunidad</h3>
                </div>

                <div style="margin-bottom:14px;">
                    <b style="font-size:12px;color:#555;">Buscar comunidad existente:</b>
                    <input wire:model.live="buscarComunidad" type="text" style="width:100%;padding:8px 10px;border:1px solid #ccc;border-radius:6px;box-sizing:border-box;margin-top:4px;font-size:13px;" placeholder="Escriba nombre o RIF...">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comunidadesEncontradas->isNotEmpty()): ?>
                        <div style="margin-top:6px;border:1px solid #e0e0e0;border-radius:6px;max-height:180px;overflow-y:auto;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($comunidadesEncontradas ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $com): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <div wire:click="seleccionarComunidadModal(<?php echo e($com->id); ?>)" style="padding:8px 10px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:12px;" onmouseover="this.style.background='#f5f0f0';this.style.borderLeft='3px solid #8b0000'" onmouseout="this.style.background='';this.style.borderLeft=''">
                                    <b style="color:#8b0000;"><?php echo e($com->nombre); ?></b>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($com->rif): ?><br><small style="color:#888;">RIF: <?php echo e($com->rif); ?></small><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <hr style="border:none;border-top:1px solid #e8e8e8;margin:14px 0;">

                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                    <div style="width:24px;height:24px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:bold;">+</div>
                    <b style="font-size:13px;color:#333;">O crear nueva comunidad</b>
                </div>

                <table width="100%" style="font-size:12px;border-collapse:separate;border-spacing:0 8px;">
                    <tr>
                        <td width="30%" style="vertical-align:middle;"><b>Nombre:</b> <span style="color:red;">*</span></td>
                        <td>
                            <input wire:model.live.debounce.500ms="modalComunidadNombre" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;font-size:12px;" placeholder="Nombre de la comunidad">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modalComunidadNombreStatus === 'disponible'): ?>
                                <br><span style="color:#28a745;font-size:11px;">✓ Nombre disponible</span>
                            <?php elseif($modalComunidadNombreStatus === 'no_disponible'): ?>
                                <br><span style="color:#dc3545;font-size:11px;">✗ Este nombre ya está en uso</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['modalComunidadNombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <br><span style="font-size:11px;color:#c62828;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:middle;"><b>RIF:</b></td>
                        <td>
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select wire:model.live="modalComunidadRifLetra" style="padding:4px 6px;border:1px solid #ccc;border-radius:4px;background:#fff;font-size:11px;width:48px;">
                                    <option value="V">V</option>
                                    <option value="C">C</option>
                                    <option value="J">J</option>
                                    <option value="G">G</option>
                                    <option value="P">P</option>
                                </select>
                                <input wire:model.live.debounce.500ms="modalComunidadRifNumero" type="text" inputmode="numeric" maxlength="9" style="flex:1;padding:7px 8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;font-size:12px;" oninput="this.value=this.value.replace(/[^0-9]/g,'')" placeholder="Número (máx. 9 dígitos)">
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modalComunidadRifStatus === 'valido'): ?>
                                <br><span style="color:#28a745;font-size:11px;">✓ RIF válido</span>
                            <?php elseif($modalComunidadRifStatus === 'invalido'): ?>
                                <br><span style="color:#dc3545;font-size:11px;">✗ <?php echo e($modalComunidadRifError ?? 'RIF inválido'); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div style="font-size:10px;color:#888;margin-top:2px;">(opcional)</div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['modalComunidadRifNumero'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <br><span style="font-size:11px;color:#c62828;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:middle;"><b>Correo:</b></td>
                        <td>
                            <input wire:model="modalComunidadCorreo" type="email" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;font-size:12px;" placeholder="correo@ejemplo.com">
                            <div style="font-size:10px;color:#888;margin-top:2px;">(opcional)</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:middle;"><b>Teléfono:</b></td>
                        <td>
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select wire:model="modalComunidadPrefijo" style="padding:5px 6px;border:1px solid #ccc;border-radius:4px;background:#fff;font-size:11px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['0424','0414','0412','0422','0416','0426']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <option value="<?php echo e($p); ?>"><?php echo e($p); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <input wire:model="modalComunidadTelefono" type="text" style="flex:1;padding:7px 8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;font-size:12px;" placeholder="XXX-XXXX" maxlength="7" oninput="this.value=this.value.replace(/\D/g,'').slice(0,7)">
                            </div>
                            <div style="font-size:10px;color:#888;margin-top:2px;">(opcional)</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:middle;"><b>Estado:</b> <span style="color:red;">*</span></td>
                        <td>
                            <select wire:model.live="modalComunidadEstadoId" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:4px;font-size:12px;background:#fff;box-sizing:border-box;">
                                <option value="">-- Seleccione estado --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($estados ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($e->est_codigo); ?>"><?php echo e($e->est_nombre); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['modalComunidadEstadoId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <br><span style="font-size:11px;color:#c62828;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:middle;"><b>Municipio:</b> <span style="color:red;">*</span></td>
                        <td>
                            <select wire:model="modalComunidadMunicipioId" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:4px;font-size:12px;background:#fff;box-sizing:border-box;">
                                <option value="">-- Seleccione municipio --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($municipiosFiltrados ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($m->mun_codigo); ?>"><?php echo e($m->mun_nombre); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['modalComunidadMunicipioId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <br><span style="font-size:11px;color:#c62828;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:middle;"><b>Dirección exacta:</b> <span style="color:red;">*</span></td>
                        <td>
                            <input wire:model="modalComunidadDirNombre" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;font-size:12px;" placeholder="Av./Calle/Casa Nro., sector, referencia...">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['modalComunidadDirNombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <br><span style="font-size:11px;color:#c62828;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                </table>

                <div style="margin-top:8px;font-size:11px;color:#888;">
                    Los campos con <span style="color:red;">*</span> son obligatorios
                </div>

                <div style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center;">
                    <button type="button" class="cm-btn cm-btn-success" wire:click="guardarComunidadModal" style="padding:8px 20px;font-size:13px;border-radius:6px;">Guardar comunidad</button>
                    <button type="button" class="cm-btn cm-btn-secondary" wire:click="cerrarModalComunidad" style="padding:8px 20px;font-size:13px;border-radius:6px;">Cancelar</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarModalDetalle && $proyectoDetalle): ?>
        <?php
            $estadoColor = match($proyectoDetalle->estado_validacion) {
                'aprobado' => '#198754',
                'rechazado' => '#c62828',
                'completado' => '#0d6efd',
                default => '#f9a825'
            };
        ?>
        <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:10000;display:flex;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:10px;padding:24px;max-width:750px;width:94%;max-height:92vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">

                
                <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:16px;padding-bottom:14px;border-bottom:2px solid #8b0000;">
                    <div style="flex:1;">
                        <h3 style="margin:0;font-size:17px;font-weight:bold;color:#333;"><?php echo e($proyectoDetalle->titulo ?? 'Proyecto'); ?></h3>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectoDetalle->equipo_resumen): ?>
                            <div style="font-size:12px;color:#666;margin-top:4px;"><?php echo e($proyectoDetalle->equipo_resumen); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <span style="display:inline-block;padding:4px 14px;border-radius:12px;font-size:12px;font-weight:bold;color:#fff;background:<?php echo e($estadoColor); ?>;white-space:nowrap;">
                        <?php echo e(ucfirst($proyectoDetalle->estado_validacion ?? 'N/A')); ?>

                    </span>
                    <button type="button" wire:click="cerrarDetalle" style="background:none;border:none;font-size:24px;cursor:pointer;color:#888;padding:0 6px;">&times;</button>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectoDetalle->resumen): ?>
                    <fieldset style="border:1px solid #e0e0e0;border-radius:6px;padding:12px;margin-bottom:14px;">
                        <legend style="font-weight:bold;font-size:13px;color:#555;padding:0 8px;">Resumen</legend>
                        <p style="margin:0;font-size:12px;color:#333;line-height:1.5;"><?php echo e($proyectoDetalle->resumen); ?></p>
                    </fieldset>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <fieldset style="border:1px solid #e0e0e0;border-radius:6px;padding:12px;margin-bottom:14px;">
                    <legend style="font-weight:bold;font-size:13px;color:#555;padding:0 8px;">Clasificación</legend>
                    <table width="100%" style="font-size:12px;border-collapse:separate;border-spacing:0 5px;">
                        <tr>
                            <td width="28%" style="font-weight:bold;color:#555;padding:2px 4px;">Línea de investigación:</td>
                            <td style="padding:2px 4px;"><?php echo e($proyectoDetalle->linea_investigacion->nombre_investigacion ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;color:#555;padding:2px 4px;">Metodología:</td>
                            <td style="padding:2px 4px;"><?php echo e($proyectoDetalle->metodologia->nombre ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;color:#555;padding:2px 4px;">Tipo de investigación:</td>
                            <td style="padding:2px 4px;"><?php echo e($proyectoDetalle->tipo_investigacion->nombre ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;color:#555;padding:2px 4px;">Objetivo de investigación:</td>
                            <td style="padding:2px 4px;"><?php echo e($proyectoDetalle->objetivo_investigacion->nombre ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;color:#555;padding:2px 4px;">Tipo de publicación:</td>
                            <td style="padding:2px 4px;"><?php echo e($proyectoDetalle->tipo_publicacion->nombre ?? 'N/A'); ?></td>
                        </tr>
                    </table>
                </fieldset>

                
                <fieldset style="border:1px solid #e0e0e0;border-radius:6px;padding:12px;margin-bottom:14px;">
                    <legend style="font-weight:bold;font-size:13px;color:#555;padding:0 8px;">Comunidad asociada</legend>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectoDetalle->comunidad): ?>
                        <table width="100%" style="font-size:12px;border-collapse:separate;border-spacing:0 5px;">
                            <tr>
                                <td width="28%" style="font-weight:bold;color:#555;padding:2px 4px;">Nombre:</td>
                                <td style="padding:2px 4px;"><?php echo e($proyectoDetalle->comunidad->nombre); ?></td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectoDetalle->comunidad->rif): ?>
                            <tr>
                                <td style="font-weight:bold;color:#555;padding:2px 4px;">RIF:</td>
                                <td style="padding:2px 4px;"><?php echo e($proyectoDetalle->comunidad->rif); ?></td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectoDetalle->comunidad->correo): ?>
                            <tr>
                                <td style="font-weight:bold;color:#555;padding:2px 4px;">Correo:</td>
                                <td style="padding:2px 4px;"><?php echo e($proyectoDetalle->comunidad->correo); ?></td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectoDetalle->comunidad->numero_telefono): ?>
                            <tr>
                                <td style="font-weight:bold;color:#555;padding:2px 4px;">Teléfono:</td>
                                <td style="padding:2px 4px;"><?php echo e($proyectoDetalle->comunidad->numero_telefono); ?></td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectoDetalle->comunidad->direccion): ?>
                            <tr>
                                <td style="font-weight:bold;color:#555;padding:2px 4px;">Dirección:</td>
                                <td style="padding:2px 4px;">
                                    <?php echo e($proyectoDetalle->comunidad->direccion->dir_calle ?? ''); ?>

                                    <?php echo e($proyectoDetalle->comunidad->direccion->municipio->mun_nombre ?? '' ? ', ' . $proyectoDetalle->comunidad->direccion->municipio->mun_nombre : ''); ?>

                                    <?php echo e($proyectoDetalle->comunidad->direccion->municipio->estado->est_nombre ?? '' ? ', ' . $proyectoDetalle->comunidad->direccion->municipio->estado->est_nombre : ''); ?>

                                </td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </table>
                    <?php else: ?>
                        <p style="margin:0;font-size:12px;color:#999;font-style:italic;">Sin comunidad asignada</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </fieldset>

                
                <fieldset style="border:1px solid #e0e0e0;border-radius:6px;padding:12px;margin-bottom:14px;">
                    <legend style="font-weight:bold;font-size:13px;color:#555;padding:0 8px;">Datos del equipo</legend>
                    <table width="100%" style="font-size:12px;border-collapse:separate;border-spacing:0 5px;">
                        <tr>
                            <td width="28%" style="font-weight:bold;color:#555;padding:2px 4px;">Equipo / Sección:</td>
                            <td style="padding:2px 4px;"><?php echo e($proyectoDetalle->equipo_ref ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;color:#555;padding:2px 4px;">Creador (cédula):</td>
                            <td style="padding:2px 4px;"><?php echo e($proyectoDetalle->creador_cedula ?? 'N/A'); ?></td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectoDetalle->fecha_actualizacion_estudiante): ?>
                        <tr>
                            <td style="font-weight:bold;color:#555;padding:2px 4px;">Últ. actualización:</td>
                            <td style="padding:2px 4px;"><?php echo e($proyectoDetalle->fecha_actualizacion_estudiante ? \Carbon\Carbon::parse($proyectoDetalle->fecha_actualizacion_estudiante)->format('d/m/Y h:i A') : 'N/A'); ?></td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectoDetalle->motivo_rechazo): ?>
                        <tr>
                            <td style="font-weight:bold;color:#c62828;padding:2px 4px;">Motivo de rechazo:</td>
                            <td style="padding:2px 4px;color:#c62828;"><?php echo e($proyectoDetalle->motivo_rechazo); ?></td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </table>
                </fieldset>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectoDetalle->vinculaciones->isNotEmpty()): ?>
                    <fieldset style="border:1px solid #c8e6c9;border-radius:6px;padding:12px;margin-bottom:14px;background:#f1faf1;">
                        <legend style="font-weight:bold;font-size:13px;color:#19692e;padding:0 8px;">Vinculaciones</legend>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $proyectoDetalle->vinculaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vinc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <div style="font-size:12px;margin:2px 0;">
                                <strong><?php echo e($vinc->titulo); ?></strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vinc->comunidad): ?>
                                    &rarr; <?php echo e($vinc->comunidad->nombre); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </fieldset>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <fieldset style="border:1px solid #e0e0e0;border-radius:6px;padding:12px;">
                    <legend style="font-weight:bold;font-size:13px;color:#555;padding:0 8px;">Documentos</legend>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectoDetalle->documentos->isNotEmpty()): ?>
                        <table width="100%" style="font-size:11px;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#8bb2b7;color:#000;">
                                    <th style="padding:6px 8px;text-align:left;">#</th>
                                    <th style="padding:6px 8px;text-align:left;">Componente</th>
                                    <th style="padding:6px 8px;text-align:left;">Archivo</th>
                                    <th style="padding:6px 8px;text-align:center;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $proyectoDetalle->documentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <tr style="border-top:1px solid #e0e0e0;">
                                        <td style="padding:5px 8px;"><?php echo e($doc->pd_orden ?? $loop->iteration); ?></td>
                                        <td style="padding:5px 8px;font-weight:bold;"><?php echo e($doc->componente->nombre ?? 'Documento'); ?></td>
                                        <td style="padding:5px 8px;">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doc->pd_archivo_path): ?>
                                                <span style="font-size:10px;color:#555;"><?php echo e(basename($doc->pd_archivo_path)); ?></span>
                                            <?php else: ?>
                                                <span style="color:#999;">Sin archivo</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td style="padding:5px 8px;text-align:center;">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doc->pd_estado === 1): ?>
                                                <span style="color:#198754;font-weight:bold;">Aceptado</span>
                                            <?php elseif($doc->pd_estado === 2): ?>
                                                <span style="color:#c62828;font-weight:bold;">Rechazado</span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doc->pd_observacion): ?>
                                                    <br><span style="font-size:10px;color:#888;"><?php echo e($doc->pd_observacion); ?></span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php else: ?>
                                                <span style="color:#f9a825;">Pendiente</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="margin:0;font-size:12px;color:#999;font-style:italic;">Sin documentos cargados</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </fieldset>

                <div style="margin-top:18px;text-align:right;">
                    <button type="button" class="cm-btn cm-btn-secondary" wire:click="cerrarDetalle" style="padding:8px 24px;font-size:13px;">Cerrar</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <script>

        window.addEventListener('descargar-pdf', event => {
            window.open(event.detail.url, '_blank');
        });
    </script>
</div>
<?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views\livewire\vinculacion-manager.blade.php ENDPATH**/ ?>