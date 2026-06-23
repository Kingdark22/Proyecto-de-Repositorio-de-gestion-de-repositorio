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
        .cm-btn-danger {
            background: #c82333;
            border-color: #a71d2a;
            color: #fff;
        }
        .cm-btn-secondary {
            background: #f4f4f4;
            border: 1px solid #c2c2c2;
            color: #222;
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
        .cm-btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }
    </style>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Comunidades</h2>

    <p style="font-size: 10px; color: #555; margin-bottom: 12px;">
        Datos en tabla <b>comunidades</b> del repositorio.
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lapsoVigente): ?>
            Lapso vigente intranet: <b><?php echo e($lapsoVigente->lap_nombre); ?></b>.
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </p>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewMode === 'list'): ?>
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 20px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Buscador y listado
            </legend>
            <table width="100%" border="0" cellpadding="8" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="65%">
                        <b>Buscar (nombre / RIF):</b>
                        <input wire:model.live="search" type="text" style="width: 90%; padding: 3px;" placeholder="Nombre o RIF...">
                    </td>
                    <td width="35%" align="right">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedeGestionar): ?>
                            <button type="button" wire:click="create" class="cm-btn cm-btn-success" style="font-size: 14px; padding: 8px 18px;">
                                Registrar nueva comunidad
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
            </table>

            <table width="100%" border="1" cellpadding="5" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="4%">N°</th>
                        <th width="30%">Comunidad / dirección</th>
                        <th width="11%">RIF</th>
                        <th width="16%">Contacto</th>
                        <th width="10%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $comunidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <tr style="background-color: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF'); ?>;"
                            valign="top">
                            <td align="center"><?php echo e($loop->iteration); ?></td>
                            <td>
                                <span style="font-weight: bold;"><?php echo e($c->nombre); ?></span>
                                <br><span style="font-size: 9px; color: #555;"><?php echo e($c->direccion?->municipio?->estado?->est_nombre ?? ''); ?> / <?php echo e($c->direccion?->municipio?->mun_nombre ?? ''); ?> - <?php echo e($c->direccion?->dir_calle ?? ''); ?></span>
                            </td>
                            <td align="center"><?php echo e($c->rif); ?></td>
                            <td align="center"><?php echo e($c->correo); ?><br><b><?php echo e($c->numero_telefono); ?></b></td>
                            <td align="center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedeGestionar): ?>
                                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                                        <button type="button" wire:click.prevent="edit(<?php echo e($c->id); ?>)"
                                            class="cm-btn cm-btn-secondary cm-btn-sm" wire:loading.attr="disabled"
                                            wire:target="edit">
                                            Editar
                                        </button>
                                        <button type="button" wire:click.prevent="delete(<?php echo e($c->id); ?>)"
                                            wire:confirm="¿Estás seguro de eliminar esta comunidad?"
                                            class="cm-btn cm-btn-danger cm-btn-sm">
                                            Eliminar
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #888; font-size: 10px;">Solo lectura</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comunidades->isEmpty()): ?>
                        <tr>
                            <td colspan="6" align="center" style="padding: 20px;">No hay comunidades registradas.
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top: 10px;"><?php echo e($comunidades->links()); ?></div>
        </fieldset>
    <?php else: ?>
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="font-weight: bold; font-style: italic; padding: 0 5px;">
                <?php echo e($editingId ? 'Modificar comunidad' : 'Registrar comunidad'); ?>

            </legend>
            <table width="100%" border="0" cellpadding="6" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="50%" style="vertical-align: top; padding: 0 4px 10px 0;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Nombre:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <input wire:model.live.debounce.500ms="nombre" type="text" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                                    <span class="obligatorio" style="color: red; font-weight: bold;">*</span>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nombreStatus === 'disponible'): ?>
                                    <span style="color: #28a745; font-size: 11px;">✓ Nombre disponible</span>
                                <?php elseif($nombreStatus === 'no_disponible'): ?>
                                    <span style="color: #dc3545; font-size: 11px;">✗ Este nombre ya está en uso</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="validation-error"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td width="50%" style="vertical-align: top; padding: 0 0 10px 4px;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 40px;">RIF:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    <select wire:model.live="rifLetra" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 11px; width: 48px;">
                                        <option value="V">V</option>
                                        <option value="E">E</option>
                                        <option value="J">J</option>
                                        <option value="G">G</option>
                                        <option value="P">P</option>
                                    </select>
                                    <input wire:model.live.debounce.500ms="rifNumero" type="text" maxlength="9" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="Número (máx. 9 dígitos)">
                                </div>
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rifStatus === 'valido'): ?>
                                    <span style="color: #28a745; font-size: 11px;">✓ RIF válido</span>
                                <?php elseif($rifStatus === 'invalido'): ?>
                                    <span style="color: #dc3545; font-size: 11px;">✗ RIF inválido</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['rifNumero'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="validation-error"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding: 0 4px 10px 0;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Correo:</b>
                            <div style="flex: 1;">
                                <input wire:model="correo" type="email" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['correo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="validation-error"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align: top; padding: 0 0 10px 4px;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Teléfono:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    <select wire:model="prefijo_telefono" style="padding: 6px 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: #fff; font-size: 11px;">
                                        <option value="0424">0424</option>
                                        <option value="0414">0414</option>
                                        <option value="0412">0412</option>
                                        <option value="0422">0422</option>
                                        <option value="0416">0416</option>
                                        <option value="0426">0426</option>
                                    </select>
                                    <input wire:model="numero_telefono" type="text" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="XXX-XXXX">
                                </div>
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['prefijo_telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="validation-error"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['numero_telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="validation-error"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding: 0 4px 10px 0;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Estado:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <select wire:model.live="estado_id" style="width: 100%; padding: 6px 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; background: #fff; font-size: 11px;">
                                        <option value="">-- Seleccione estado --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $estados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <option value="<?php echo e($e->est_codigo); ?>"><?php echo e($e->est_nombre); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                    <span class="obligatorio" style="color: red; font-weight: bold;">*</span>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['estado_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="validation-error"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align: top; padding: 0 0 10px 4px;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Municipio:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <select wire:model="municipio_id" style="width: 100%; padding: 6px 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; background: #fff; font-size: 11px;">
                                        <option value="">-- Seleccione municipio --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $municipios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <option value="<?php echo e($m->mun_codigo); ?>"><?php echo e($m->mun_nombre); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                    <span class="obligatorio" style="color: red; font-weight: bold;">*</span>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['municipio_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="validation-error"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 0;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 145px;">Dirección exacta:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: flex-start; gap: 4px;">
                                    <input wire:model="dir_nombre" type="text" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"
                                        placeholder="Av./Calle/Casa Nro., sector, referencia...">
                                    <span class="obligatorio" style="color: red; font-weight: bold; margin-top: 5px;">*</span>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['dir_nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="validation-error"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
             </table>
 
             <div style="margin-top: 15px; text-align: center;">
                 <button type="button" wire:click="cancel" class="cm-btn cm-btn-danger"
                     style="margin-right: 10px;">Cancelar</button>
                 <button type="button" wire:click="save" class="cm-btn cm-btn-primary">Guardar</button>
             </div>
         </fieldset>
     <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
 </div>
<?php /**PATH C:\Users\Emanuel\Desktop\Sistemax\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/livewire/comunidad-manager.blade.php ENDPATH**/ ?>