<div>
    <style>
        .grp-btn {
            border: 1px solid #777;
            background: #fff;
            color: #222;
            padding: 0.65rem 1rem;
            border-radius: 0.45rem;
            font-size: 0.92rem;
            cursor: pointer;
            transition: all 0.18s ease;
            min-width: 120px;
        }

        .grp-btn:hover {
            background: #f3f3f3;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .grp-btn-primary {
            background: #198754;
            color: #fff;
            border-color: #166f43;
        }

        .grp-btn-primary:hover {
            background: #146c43;
        }

        .grp-btn-secondary {
            background: #fafafa;
            color: #1f2937;
            border-color: #d1d5db;
        }

        .grp-btn-danger {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fca5a5;
        }

        .grp-btn-small {
            font-size: 0.82rem;
            padding: 0.45rem 0.75rem;
            min-width: auto;
        }

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

        .grp-filter-select, .grp-filter-input {
            height: 32px;
            padding: 4px 8px;
            font-size: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #fff;
            box-sizing: border-box;
        }
        .grp-filter-select {
            min-width: 140px;
        }
        .grp-filter-input {
            width: 160px;
        }
        .comunidad-search-container {
            position: relative;
            flex: 1;
        }
        .comunidad-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #fff;
            border: 1px solid #ccc;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 0 0 4px 4px;
        }
        .comunidad-option {
            padding: 6px 8px;
            font-size: 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .comunidad-option:hover {
            background-color: #f0f7f0;
        }
        .comunidad-option:last-child {
            border-bottom: none;
        }
    </style>
    <h2 class="titulo" style="margin-bottom: 10px; font-weight: bolder;">Equipos de proyecto</h2>

    <p style="font-size: 11px; color: #444; margin-bottom: 12px;">
        Registre el <strong>grupo de proyecto</strong> eligiendo estudiantes de la <strong>secci&oacute;n del PNF</strong>.
        Queda identificado con la clave <code>EQGRP:&hellip;</code> para usarlo al registrar el expediente.
    </p>


    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$tablaLista): ?>
        <div style="background: #fff3cd; padding: 10px; font-size: 11px; margin-bottom: 12px;">
            Falta la tabla <code>grupo_proyecto_modulo</code> en MySQL repositorio (solo del m&oacute;dulo, no es intranet).
            Ejecute:
            <code>php artisan migrate
                --path=database/migrations/2026_05_26_100000_create_grupo_proyecto_modulo_table.php</code>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewMode === 'list'): ?>
        <div style="margin-bottom: 10px; display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
            <select wire:model.live="filterLapso" class="grp-filter-select" wire:loading.attr="disabled">
                <option value="">Lapso</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $lapsos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <option value="<?php echo e($l->lap_codigo); ?>"><?php echo e($l->lap_nombre); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
            <select wire:model.live="filterPrograma" class="grp-filter-select" <?php if(!$filterLapso || $isProfessor): ?> disabled <?php endif; ?> wire:loading.attr="disabled">
                <option value="">PNF / Programa</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $programas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <option value="<?php echo e($p->pro_codigo); ?>"><?php echo e($p->pro_siglas); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
            <select wire:model.live="filterSeccion" class="grp-filter-select" <?php if(!$filterLapso || !$filterPrograma): ?> disabled <?php endif; ?> wire:loading.attr="disabled">
                <option value="">Secci&oacute;n</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $secciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <option value="<?php echo e($s->sec_codigo); ?>"><?php echo e($s->sec_nombre); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar nombre&hellip;" class="grp-filter-input" style="flex: 1; min-width: 200px;">
            <button type="button" class="cm-btn cm-btn-success" wire:click="crearGrupo" style="margin-left: auto;">Registrar nuevo
                grupo</button>
        </div>

        <fieldset style="border: 2px solid #8b0000; padding: 8px;">
            <legend style="font-weight: bold;">Grupos de proyecto registrados</legend>
            <table width="100%" border="1" cellpadding="4" style="font-size: 11px; border-collapse: collapse;">
                <thead>
                    <tr style="background: #8bb2b7;">
                        <th>Nombre</th>
                        <th>PNF</th>
                        <th>Secci&oacute;n</th>
                        <th>Lapso</th>
                        <th>Integrantes</th>
                        <th>Clave</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $gruposList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <tr>
                            <td><b><?php echo e($g->nombre); ?></b></td>
                            <td><?php echo e($g->pro_siglas ?: ($g->pro_nombre ?: '—')); ?></td>
                            <td><?php echo e($g->sec_nombre ?: 'Sec. ' . $g->sec_codigo); ?></td>
                            <td><?php echo e($g->lap_nombre ?: '—'); ?></td>
                            <td align="center"><?php echo e($g->integrantes); ?></td>
                            <td><code style="font-size:9px;"><?php echo e($g->clave); ?></code></td>
                            <td align="center" nowrap>
                                <button type="button" class="cm-btn cm-btn-secondary cm-btn-sm"
                                    wire:click="editarGrupo(<?php echo e($g->grp_codigo); ?>)">Editar</button>
                                <button type="button" class="cm-btn cm-btn-danger cm-btn-sm"
                                    wire:click="eliminarGrupo(<?php echo e($g->grp_codigo); ?>)"
                                    wire:confirm="&iquest;Eliminar este grupo?">Eliminar</button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="7" align="center">No hay grupos registrados. Cree uno con integrantes de la
                                secci&oacute;n.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
            <?php echo e($gruposList->links()); ?>

        </fieldset>
    <?php else: ?>
        <fieldset style="border: 2px solid #8b0000; padding: 10px;">
            <legend style="font-weight: bold;"><?php echo e($editingGrpCodigo ? 'Editar grupo' : 'Registrar grupo de proyecto'); ?>

            </legend>
            <table width="100%" style="font-size: 11px;">
                <tr>
                    <td width="50%"><b>Nombre del proyecto:</b><br><input wire:model.live.debounce.500ms="nombreGrupo" type="text"
                            class="grp-filter-input" style="width:90%;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nombreGrupoStatus === 'disponible'): ?>
                                <br><span style="color: #28a745; font-size: 11px;">✓ Nombre disponible</span>
                            <?php elseif($nombreGrupoStatus === 'no_disponible'): ?>
                                <br><span style="color: #dc3545; font-size: 11px;">✗ Este nombre ya está en uso</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                     <td><b>Comunidad:</b><br>
                         <div style="display: flex; gap: 4px; align-items: center;">
                              <div class="comunidad-search-container" id="comunidad-search-container">
                                  <input wire:model.live.debounce.300ms="searchComunidad" 
                                         type="text" 
                                         class="grp-filter-input" 
                                         style="width: 100%;" 
                                         placeholder="Buscar comunidad..."
                                         autocomplete="off"
                                         wire:focus="$set('mostrarDropdownComunidad', true)">
                                 
                                 <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarDropdownComunidad): ?>
                                     <div class="comunidad-dropdown">
                                         <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->comunidadesFiltradas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                             <div class="comunidad-option" 
                                                  wire:click="selectComunidad('<?php echo e($c->com_codigo); ?>')">
                                                 <?php echo e($c->com_nombre); ?> 
                                                 <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($c->com_rif): ?>
                                                     <span style="color:#888; font-size:10px; margin-left:5px;">(<?php echo e($c->com_rif); ?>)</span>
                                                 <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                             </div>
                                         <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                             <div class="comunidad-option" style="color:#888; cursor:default;">
                                                 No se encontraron comunidades.
                                             </div>
                                         <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                     </div>
                                 <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                             </div>
                             <button type="button" wire:click="abrirModalComunidad" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space: nowrap;" title="Crear nueva comunidad">+</button>
                         </div>
                     </td>

                </tr>
                <tr>
                    <td colspan="2" style="padding-top:8px;">
                        <b>Contexto acad&eacute;mico:</b>
                        <div style="display: flex; gap: 16px; margin-top: 4px;">
                            <select wire:model.live="filterLapso" class="grp-filter-select" wire:loading.attr="disabled">
                                <option value="">Lapso</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $lapsos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($l->lap_codigo); ?>"><?php echo e($l->lap_nombre); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                            <select wire:model.live="filterPrograma" class="grp-filter-select"
                                <?php if(!$filterLapso || ($isProfessor && $viewMode === 'form')): ?> disabled <?php endif; ?> wire:loading.attr="disabled">
                                <option value="">PNF</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $programas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($p->pro_codigo); ?>"><?php echo e($p->pro_siglas); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                            <select wire:model.live="filterSeccion" class="grp-filter-select"
                                <?php if(!$filterLapso || !$filterPrograma): ?> disabled <?php endif; ?> wire:loading.attr="disabled">
                                <option value="">Secci&oacute;n</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $secciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($s->sec_codigo); ?>"><?php echo e($s->sec_nombre); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($filterLapso && $filterSeccion): ?>
                            <?php
                                $lapLabel = $lapsos->firstWhere('lap_codigo', (int)$filterLapso)?->lap_nombre ?? 'Lapso #'.$filterLapso;
                                $proLabel = $programas->firstWhere('pro_codigo', (int)$filterPrograma)?->pro_siglas ?? '—';
                                $secLabel = $secciones->firstWhere('sec_codigo', (int)$filterSeccion)?->sec_nombre ?? 'Sección #'.$filterSeccion;
                            ?>
                            <div style="margin-top:6px; background:#f0f7f0; border:1px solid #b8d4b8; border-radius:4px; padding:6px 10px; font-size:12px;">
                                <b>Sección seleccionada:</b>
                                <?php echo html_entity_decode($proLabel); ?> &middot; <?php echo html_entity_decode($secLabel); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lapLabel): ?> <span style="color:#666;">(<?php echo html_entity_decode($lapLabel); ?>)</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p style="font-size: 11px; color: #856404; margin-top:4px;">
                                Seleccione lapso, PNF y sección para ver los estudiantes candidatos (todas las secciones del PNF).
                            </p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
            </table>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($filterSeccion !== ''): ?>
                <div style="margin-top: 12px; padding: 8px; background: #f5f5f5; border: 1px solid #ccc;">
                    <b>Agregar integrante (del PNF):</b><br>
                    <div style="display: flex; gap: 16px; align-items: center; margin-top: 4px;">
                        <div style="position: relative; flex: 1;">
                            <!-- Select + Búsqueda unificados en un solo componente dinámico -->
                            <div style="position: relative;">
                                <!-- Input de búsqueda (actúa como select-search) -->
                                <input wire:model.live.debounce.150ms="buscarEstudiante"
                                       type="text"
                                       class="grp-filter-input"
                                       style="width: 100%; padding: 8px 10px; font-size: 12px; height: 34px;"
                                       placeholder="🔍 Escriba nombre, apellido o cédula para buscar..."
                                       autocomplete="off"
                                       wire:focus="abrirDropdownEstudiantes"
                                       wire:blur="cerrarDropdownEstudiantes">

                                <!-- Dropdown de resultados (se muestra al buscar O al hacer focus con el campo vacío) -->
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarDropdownEstudiantes): ?>
                                    <div style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; background: #fff; border: 1px solid #ccc; max-height: 280px; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.12); border-radius: 0 0 6px 6px;">
                                        <?php $items = $this->estudiantesFiltrados; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <div wire:mousedown="seleccionarEstudiante('<?php echo e($c->cedula); ?>')"
                                                 style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; cursor: pointer; border-bottom: 1px solid #f0f0f0; font-size: 12px; transition: background 0.12s;"
                                                 onmouseover="this.style.background='#f0f7f0'"
                                                 onmouseout="this.style.background=''">
                                                <span style="font-weight: 600;"><?php echo e($c->apellido); ?>, <?php echo e($c->nombre); ?></span>
                                                <span style="color: #888; font-size: 10px; margin-left: auto;"><?php echo e($c->cedula); ?></span>
                                            </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            <div style="padding: 12px; color: #999; font-size: 11px; text-align: center;">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim($buscarEstudiante) !== ''): ?>
                                                    😕 No se encontraron estudiantes con "<?php echo e($buscarEstudiante); ?>"
                                                <?php else: ?>
                                                    👆 Escriba para buscar estudiantes...
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($items->count() >= 30 && trim($buscarEstudiante) !== ''): ?>
                                            <div style="padding: 6px; color: #888; font-size: 10px; text-align: center; border-top: 1px solid #eee;">
                                                Mostrando 30 resultados. Sea más específico.
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedCedula !== '' && $estudianteSeleccionadoLabel !== ''): ?>
                                <div style="margin-top: 4px; padding: 4px 8px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; font-size: 11px; color: #166534; display: flex; align-items: center; gap: 6px;">
                                    <span>✓</span>
                                    <span><?php echo e($estudianteSeleccionadoLabel); ?></span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <select wire:model="selectedRolId" class="grp-filter-select" style="width: 130px;">
                            <option value="1">Autor-L&iacute;der</option>
                            <option value="2">Autor</option>
                        </select>
                        <button type="button" class="cm-btn cm-btn-success cm-btn-sm"
                            wire:click="agregarIntegrante">Agregar</button>
                    </div>
                </div>

                <table width="100%" border="1" cellpadding="4"
                    style="font-size: 11px; margin-top: 10px; border-collapse: collapse;">
                    <thead>
                        <tr style="background:#ddd;">
                            <th>C&eacute;dula</th>
                            <th>Nombre</th>
                            <th>Rol</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $miembrosSeleccionados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <tr>
                                <td><?php echo e($m['cedula']); ?></td>
                                <td><?php echo e($m['apellido']); ?>, <?php echo e($m['nombre']); ?></td>
                                <td><?php echo e($m['rol_name']); ?></td>
                                <td><a href="#"
                                        wire:click.prevent="quitarIntegrante(<?php echo e(json_encode($m['cedula'])); ?>)">Quitar</a>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="4" align="center">Agregue al menos un l&iacute;der y los autores del grupo.
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="font-size: 11px; color: #856404;">Seleccione lapso y secci&oacute;n para ver estudiantes candidatos (todas las secciones del PNF).
                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div style="margin-top: 14px;">
                <button type="button" class="cm-btn cm-btn-success" wire:click="registrarGrupo">Registrar
                    grupo</button>
                <button type="button" class="cm-btn cm-btn-danger" wire:click="volver">Cancelar</button>
            </div>
            <p style="font-size: 10px; color: #555; margin-top: 8px;">El registro del expediente del proyecto es un
                paso aparte; luego elija este grupo al crear el expediente.</p>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarModalComunidad): ?>
                <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;">
                    <div style="background:#fff;border-radius:8px;padding:20px;max-width:600px;width:90%;max-height:90vh;overflow-y:auto;">
                        <h3 style="margin-top:0;font-size:16px;">Registrar nueva comunidad</h3>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['modalNombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="validation-error"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <table width="100%" style="font-size:11px;">
                            <tr>
                                <td width="30%"><b>Nombre:</b> <span style="color:red;">*</span></td>
                                 <td><input wire:model.live.debounce.500ms="modalNombre" type="text" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;">
                                 <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modalNombreStatus === 'disponible'): ?>
                                     <br><span style="color: #28a745; font-size: 11px;">✓ Nombre disponible</span>
                                 <?php elseif($modalNombreStatus === 'no_disponible'): ?>
                                     <br><span style="color: #dc3545; font-size: 11px;">✗ Este nombre ya está en uso</span>
                                 <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                 </td>
                             </tr>
                              <tr>
                                  <td style="vertical-align:top;"><b>RIF:</b></td>
                                  <td>
                                      <div style="display:flex;gap:4px;align-items:center;">
                                          <select wire:model.live="modalRifLetra" style="padding:4px 6px;border:1px solid #ccc;border-radius:4px;background:#fff;font-size:11px;width:48px;">
                                              <option value="V">V</option>
                                              <option value="E">E</option>
                                              <option value="J">J</option>
                                              <option value="G">G</option>
                                              <option value="P">P</option>
                                          </select>
                                          <input wire:model.live.debounce.500ms="modalRifNumero" type="text" inputmode="numeric" maxlength="9" style="flex:1;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;" oninput="this.value=this.value.replace(/[^0-9]/g,'')" placeholder="Número (máx. 9 dígitos)">
                                      </div>
                                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modalRifStatus === 'valido'): ?>
                                          <span style="color: #28a745; font-size: 11px;">✓ RIF válido</span>
                                      <?php elseif($modalRifStatus === 'invalido'): ?>
                                          <span style="color: #dc3545; font-size: 11px;">✗ RIF inválido</span>
                                      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['modalRifNumero'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                          <span class="validation-error" style="font-size:11px;color:#c62828;"><?php echo e($message); ?></span>
                                      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                  </td>
                             </tr>
                            <tr>
                                <td><b>Correo:</b></td>
                                 <td>
                                     <input wire:model.live.debounce.500ms="modalCorreo" type="email" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;" placeholder="ejemplo@gmail.com">
                                     <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modalCorreoStatus === 'valido'): ?>
                                         <span style="color: #28a745; font-size: 11px;">✓ Correo válido</span>
                                     <?php elseif($modalCorreoStatus === 'invalido'): ?>
                                         <span style="color: #dc3545; font-size: 11px;">✗ <?php echo e($modalCorreoError ?? 'Correo inválido'); ?></span>
                                     <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                     <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['modalCorreo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                         <span class="validation-error" style="font-size:11px;color:#c62828;"><?php echo e($message); ?></span>
                                     <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                 </td>
                            </tr>
                            <tr>
                                <td><b>Tel&eacute;fono:</b></td>
                                <td>
                                    <div style="display:flex;gap:4px;align-items:center;">
                                        <select wire:model="modalPrefijoTelefono" style="padding:5px;border:1px solid #ccc;border-radius:4px;background:#fff;">
                                            <option value="0424">0424</option>
                                            <option value="0414">0414</option>
                                            <option value="0412">0412</option>
                                            <option value="0422">0422</option>
                                            <option value="0416">0416</option>
                                            <option value="0426">0426</option>
                                        </select>
                                        <input wire:model.lazy="modalNumeroTelefono" type="text" style="flex:1;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;" placeholder="XXX-XXXX">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Estado:</b> <span style="color:red;">*</span></td>
                                <td>
                                    <select wire:model.live="modalEstadoId" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;background:#fff;">
                                        <option value="">-- Seleccione --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $modalEstados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <option value="<?php echo e($e->est_codigo); ?>"><?php echo e($e->est_nombre); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Municipio:</b> <span style="color:red;">*</span></td>
                                <td>
                                    <select wire:model="modalMunicipioId" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;background:#fff;">
                                        <option value="">-- Seleccione --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $modalMunicipios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <option value="<?php echo e($m->mun_codigo); ?>"><?php echo e($m->mun_nombre); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Direcci&oacute;n exacta:</b> <span style="color:red;">*</span></td>
                                <td><input wire:model="modalDirNombre" type="text" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;" placeholder="Av./Calle/Casa Nro., sector..."></td>
                            </tr>
                        </table>

                        <div style="margin-top:15px;text-align:center;display:flex;gap:10px;justify-content:center;">
                            <button type="button" class="cm-btn cm-btn-success" wire:click="guardarComunidadDesdeModal">Guardar comunidad</button>
                            <button type="button" class="cm-btn cm-btn-danger" wire:click="cerrarModalComunidad">Cancelar</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </fieldset>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<script>
document.addEventListener('click', function(e) {
    var container = document.getElementById('comunidad-search-container');
    if (container && !container.contains(e.target)) {
        window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('mostrarDropdownComunidad', false);
    }
});
</script>
<?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/livewire/grupo-proyecto-manager.blade.php ENDPATH**/ ?>