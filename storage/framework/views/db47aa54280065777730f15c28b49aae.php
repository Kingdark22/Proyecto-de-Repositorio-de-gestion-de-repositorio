<div>
    <style>
        .cm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            padding: 0.55rem 0.95rem;
            font-size: 0.92rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
            text-decoration: none;
        }

        .cm-btn:hover {
            transform: translateY(-1px);
        }

        .cm-btn-primary {
            background: #19692e;
            border-color: #154f26;
            color: #fff;
        }

        .cm-btn-success {
            background: #198754;
            border-color: #166f43;
            color: #fff;
        }

        .cm-btn-warning {
            background: #f0b606;
            border-color: #d99e00;
            color: #212529;
        }

        .cm-btn-danger {
            background: #c82333;
            border-color: #a71d2a;
            color: #fff;
        }

        .cm-btn-secondary {
            background: #f4f4f4;
            border-color: #c2c2c2;
            color: #222;
        }

        .cm-btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }
    </style>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Consulta de Proyectos
        Institucionales</h2>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$intranetDisponible): ?>
        <div
            style="background-color: #fff3cd; color: #856404; padding: 8px; margin-bottom: 12px; border: 1px solid #ffeeba; font-size: 11px;">
            Filtros académicos (programa, trayecto, sección) requieren conexión con intranet. Los demás criterios siguen
            disponibles.
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Criterios de búsqueda
        </legend>
        <table width="100%" border="0" cellpadding="8" cellspacing="0" style="font-size: 11px;">
            <tr>
                <td width="50%" colspan="2">
                    <b>Término (título o resumen):</b><br>
                    <input wire:model.live.debounce.300ms="search" type="text" style="width: 98%;"
                        placeholder="Palabras clave...">
                </td>
                <td width="25%">
                    <b>Lapso académico:</b><br>
                    <select wire:model.live="lapsoFilter" style="width: 95%;">
                        <option value="">- Todos los lapsos -</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $lapsos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($l->lap_codigo); ?>"><?php echo e($l->nombre); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </td>
                <td width="25%">
                    <b>Programa:</b><br>
                    <select wire:model.live="programaFilter" style="width: 95%;" <?php if(!$lapsoFilter || !$intranetDisponible): echo 'disabled'; endif; ?>>
                        <option value="">- Todos -</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $programas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($pro->pro_codigo); ?>"><?php echo e(trim($pro->pro_siglas)); ?> -
                                <?php echo e(trim($pro->pro_nombre)); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="25%">
                    <b>Trayecto:</b><br>
                    <select wire:model.live="trayectoFilter" style="width: 95%;" <?php if(!$lapsoFilter || !$intranetDisponible): echo 'disabled'; endif; ?>>
                        <option value="">- Todos -</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $trayectosCatalogo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($tra->tra_codigo); ?>"><?php echo e(trim($tra->tra_nombre)); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </td>
                <td width="25%">
                    <b>Sección:</b><br>
                    <select wire:model.live="seccionFilter" style="width: 95%;" <?php if(!$lapsoFilter || !$intranetDisponible): echo 'disabled'; endif; ?>>
                        <option value="">- Todas -</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $secciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <?php $secLabel = trim($sec->sec_nombre) . ($sec->pro_siglas ? ' (' . trim($sec->pro_siglas) . ')' : ''); ?>
                            <option value="<?php echo e($sec->sec_codigo); ?>"><?php echo e($secLabel); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </td>
                <td width="25%">
                    <b>Comunidad:</b><br>
                    <select wire:model.live="comunidadFilter" style="width: 95%;">
                        <option value="">- Todas -</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $comunidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $com): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($com->id); ?>"><?php echo e($com->nombre); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </td>
                <td width="25%">
                    <b>Línea de investigación:</b><br>
                    <select wire:model.live="lineaFilter" style="width: 95%;">
                        <option value="">- Todas -</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $lineas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($lin->id); ?>"><?php echo e(Str::limit($lin->nombre_investigacion, 35)); ?>

                            </option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="33%">
                    <b>Tipo de publicación:</b><br>
                    <select wire:model.live="tipoPublicacionFilter" style="width: 95%;">
                        <option value="">- Todos -</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tipos_publicacion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($tp->id); ?>"><?php echo e($tp->nombre); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </td>
                <td width="33%">
                    <b>Tipo de investigación:</b><br>
                    <select wire:model.live="tipoInvestigacionFilter" style="width: 95%;">
                        <option value="">- Todos -</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tipos_investigacion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($ti->id); ?>"><?php echo e($ti->nombre); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </td>
                <td width="34%" colspan="2">
                    <b>Metodología:</b><br>
                    <select wire:model.live="metodologiaFilter" style="width: 95%;">
                        <option value="">- Todas -</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $metodologias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mei): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($mei->id); ?>"><?php echo e($mei->nombre); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="4" align="right" style="padding-top: 6px;">
                    <button type="button" wire:click="limpiarFiltros" class="cm-btn cm-btn-secondary cm-btn-sm">
                        Limpiar filtros
                    </button>
                </td>
            </tr>
        </table>
    </fieldset>

    <fieldset
        style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px; min-height: 220px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Resultados de la búsqueda
        </legend>

        <table width="100%" border="1" cellpadding="4" cellspacing="0"
            style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px; min-height: 160px;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                    <th width="4%">N&deg;</th>
                    <th width="48%">Título / equipo / comunidad</th>
                    <th width="24%">Resumen</th>
                    <th width="24%">Acciones</th>
                </tr>
            </thead>
            <tbody class="Texto">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $proyectos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <?php $rowNum = ($proyectos->currentPage() - 1) * $proyectos->perPage() + $loop->iteration; ?>
                    <tr style="background-color: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF'); ?>;"
                        valign="top">
                        <td align="center" style="padding: 5px; font-weight: bold; font-size: 11px;"><?php echo e($rowNum); ?></td>
                        <td style="padding: 5px;">
                            <div style="font-weight: bold; font-size: 12px; color: #8b0000;"><?php echo e($p->titulo); ?></div>
                            <div style="font-size: 10px; color: #333;">
                                <b>Equipo:</b> <?php echo e($p->equipo_resumen); ?>

                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->comunidad): ?>
                                <div style="font-size: 10px;"><b>Comunidad:</b>
                                    <?php echo e($p->comunidad->nombre); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td style="padding: 5px; font-size: 10px;"><?php echo e(Str::limit($p->resumen, 100)); ?></td>
                        <td align="center" style="padding: 5px;">
                            <a href="#" wire:click.prevent="openDetails(<?php echo e($p->id); ?>)"
                                style="color:#0000EE; font-weight:bold;">Ver detalles</a>
                            <?php $srchDocs = $p->documentos; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($srchDocs->isNotEmpty()): ?>
                                <br><span style="font-size:9px; color:#666;">Docs:</span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $srchDocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <a href="<?php echo e(route('documentos.view', $doc->pd_codigo)); ?>"
                                        style="color:#0000EE; font-size:10px;"><?php echo e($doc->componente?->nombre ?? 'Doc'); ?></a><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>, <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectos->isEmpty()): ?>
                    <tr>
                        <td colspan="4" align="center" style="padding: 20px; font-weight: bold;">
                            No se encontraron proyectos con los criterios seleccionados
                        </td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top: 10px;"><?php echo e($proyectos->links()); ?></div>
    </fieldset>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isDetailsModalOpen && $selectedProject): ?>
        <div
            style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; display: flex; justify-content: center; align-items: center; overflow-y: auto;">
            <div
                style="background-color: #FFF; border: 2px solid #8b0000; border-radius: 6px; padding: 20px; width: 850px; max-height: 90vh; overflow-y: auto;">
                <div
                    style="display: flex; justify-content: space-between; border-bottom: 2px solid #8b0000; padding-bottom: 10px; margin-bottom: 15px;">
                    <div style="width: 90%;">
                        <h3 style="margin: 0; font-size: 16px; font-weight: bold; color: #8b0000;"><?php echo e($selectedProject->titulo); ?>

                        </h3>
                        <span style="font-size: 11px;"><b>Equipo:</b> <?php echo e($selectedProject->equipo_resumen); ?></span>
                    </div>
                    <button type="button" wire:click="closeDetails"
                        style="background: #8b0000; border: none; font-size: 14px; color: #FFF; cursor: pointer; font-weight: bold; padding: 2px 8px; border-radius: 3px;">X</button>
                </div>

                <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 11px; margin-bottom: 10px;">
                    <tr>
                        <td width="50%" valign="top">
                            <fieldset style="border: 1px solid #CCC; padding: 8px;">
                                <legend style="font-weight: bold; font-size: 11px;">Informaci&oacute;n del equipo</legend>
                                <b>Equipo:</b> <?php echo e($selectedProject->equipo_resumen); ?><br>
                                <b>T&iacute;tulo:</b> <?php echo e($selectedProject->titulo); ?>

                            </fieldset>
                        </td>
                        <td width="50%" valign="top">
                            <fieldset style="border: 1px solid #CCC; padding: 8px;">
                                <legend style="font-weight: bold; font-size: 11px;">Comunidad</legend>
                                <b>Nombre:</b> <?php echo e($selectedProject->comunidad->nombre ?? 'N/A'); ?><br>
                                <b>RIF:</b> <?php echo e($selectedProject->comunidad->rif ?? 'N/A'); ?><br>
                                <b>Direcci&oacute;n:</b> <?php echo e($selectedProject->comunidad->direccion?->dir_calle ?? 'N/A'); ?>

                            </fieldset>
                        </td>
                    </tr>
                </table>

                <fieldset style="border: 1px solid #CCC; padding: 8px; margin-bottom: 10px;">
                    <legend style="font-weight: bold; font-size: 11px;">Resumen</legend>
                    <div style="font-size: 11px; text-align: justify; line-height: 1.5;"><?php echo e($selectedProject->resumen ?: 'Sin resumen disponible.'); ?></div>
                </fieldset>

                <fieldset style="border: 1px solid #CCC; padding: 8px; margin-bottom: 10px;">
                    <legend style="font-weight: bold; font-size: 11px;">Ficha t&eacute;cnica</legend>
                    <table width="100%" cellpadding="3" cellspacing="0" style="font-size: 11px;">
                        <tr>
                            <td width="25%"><b>Publicaci&oacute;n:</b></td>
                            <td width="25%"><?php echo e($selectedProject->tipo_publicacion?->nombre ?? 'N/D'); ?></td>
                            <td width="25%"><b>Investigaci&oacute;n:</b></td>
                            <td width="25%"><?php echo e($selectedProject->tipo_investigacion?->nombre ?? 'N/D'); ?></td>
                        </tr>
                        <tr>
                            <td><b>Metodolog&iacute;a:</b></td>
                            <td><?php echo e($selectedProject->metodologia?->nombre ?? 'N/D'); ?></td>
                            <td><b>L&iacute;nea de investigaci&oacute;n:</b></td>
                            <td><?php echo e($selectedProject->linea_investigacion?->nombre_investigacion ?? 'N/D'); ?></td>
                        </tr>
                        <tr>
                            <td><b>Objetivo de investigaci&oacute;n:</b></td>
                            <td colspan="3"><?php echo e($selectedProject->objetivo_investigacion?->nombre ?? 'N/D'); ?></td>
                        </tr>
                    </table>
                </fieldset>

                        <?php $detSrchDocs = $selectedProject->documentos; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detSrchDocs->isNotEmpty()): ?>
                    <fieldset style="border: 1px solid #CCC; padding: 8px; margin-bottom: 10px;">
                        <legend style="font-weight: bold; font-size: 11px;">Documentos</legend>
                        <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 11px; border-collapse: collapse;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detSrchDocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <tr style="border-bottom: 1px solid #EEE;">
                                    <td width="60%" style="padding: 4px;"><?php echo e($doc->componente?->nombre ?? 'Documento'); ?></td>
                                    <td width="20%" align="center" style="padding: 4px;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doc->pd_estado === 1): ?>
                                            <span style="color: green; font-weight: bold;">Aprobado</span>
                                        <?php elseif($doc->pd_estado === 2): ?>
                                            <span style="color: red; font-weight: bold;">Rechazado</span>
                                        <?php else: ?>
                                            <span style="color: #888;">Pendiente</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td width="20%" align="center" style="padding: 4px;">
                                        <a href="<?php echo e(route('documentos.view', $doc->pd_codigo)); ?>"
                                            style="color: #0000EE;">Ver</a>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </table>
                    </fieldset>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div style="text-align: center; margin-top: 15px;">
                    <button type="button" wire:click="closeDetails" style="background: #8b0000; border: none; color: #FFF; font-weight: bold; padding: 6px 20px; border-radius: 4px; cursor: pointer; font-size: 12px;">Cerrar detalles</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/livewire/project-search.blade.php ENDPATH**/ ?>