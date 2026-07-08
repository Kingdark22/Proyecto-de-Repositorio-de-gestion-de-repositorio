<?php $__env->startSection('title', 'Vinculación de Componentes'); ?>
<?php $__env->startSection('header', 'Vinculación de Componentes'); ?>

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

    .comp-checkbox:checked + .comp-label {
        background: #e8f5e9;
        border-color: #198754;
        font-weight: bold;
    }
    .comp-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        background: #fafafa;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.15s ease;
    }
    .comp-label:hover {
        background: #f0f0f0;
        border-color: #999;
    }
    .comp-label input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <form method="POST" action="<?php echo e(route('componentes.vinculacion.guardar')); ?>" id="vinculacionForm">
        <?php echo csrf_field(); ?>

        
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 15px; margin-bottom: 20px; background: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                Seleccionar Componentes
            </legend>

            <div style="font-size:12px;color:#666;margin-bottom:10px;">
                Seleccione uno o más componentes para vincularlos a los PNF y trayectos que elija abajo.
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($componentes->isEmpty()): ?>
                <div style="text-align:center;padding:20px;font-weight:bold;color:#999;">
                    No hay componentes activos disponibles.
                </div>
            <?php else: ?>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $componentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <?php
                            $asigsComp = $asignaciones->where('comp_codigo', $comp->id);
                        ?>
                        <label class="comp-label" style="<?php echo e($asigsComp->isNotEmpty() ? 'background:#e8f5e9;border-color:#198754;' : ''); ?>">
                            <input type="checkbox" name="componente_ids[]" value="<?php echo e($comp->id); ?>"
                                class="comp-checkbox"
                                onchange="toggleComponenteLabel(this)">
                            <span>
                                <b><?php echo e($comp->nombre); ?></b>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asigsComp->isNotEmpty()): ?>
                                    <span style="font-size:10px;color:#666;display:block;">
                                        (<?php echo e($asigsComp->count()); ?> vinculacione<?php echo e($asigsComp->count() === 1 ? 's' : 's'); ?>)
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>

                <div style="margin-top:10px;font-size:11px;color:#666;">
                    <span id="selectedCount">0</span> componente(s) seleccionado(s).
                    <button type="button" class="cm-btn cm-btn-sm" onclick="seleccionarTodos(true)" style="margin-left:8px;">Seleccionar todos</button>
                    <button type="button" class="cm-btn cm-btn-sm" onclick="seleccionarTodos(false)">Deseleccionar todos</button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </fieldset>

        
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 15px; margin-bottom: 20px; background: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                Asignar PNF y Trayectos
            </legend>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($pnfRows)): ?>
                <div style="font-size:12px;color:#666;margin-bottom:10px;">
                    Marque los PNF y trayectos que desea asignar a los componentes seleccionados arriba.
                </div>

                <table width="100%" border="1" cellpadding="6" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                            <th width="5%">N&deg;</th>
                            <th width="15%">PNF</th>
                            <th width="5%">Activo</th>
                            <th width="60%">Trayectos asignados</th>
                            <th width="15%"></th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pnfRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proCodigo => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <tr style="background-color: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF'); ?>;" valign="top">
                                <td align="center"><?php echo e($loop->iteration); ?></td>
                                <td style="font-weight: bold; padding: 8px; font-size: 12px;">
                                    <?php echo e($row['pro_siglas'] ?? 'PNF #'.$proCodigo); ?>

                                </td>
                                <td align="center">
                                    <input type="hidden" name="pnf_activo[<?php echo e($proCodigo); ?>]" value="0">
                                    <input type="checkbox"
                                        name="pnf_activo[<?php echo e($proCodigo); ?>]"
                                        value="1"
                                        <?php echo e($row['activo'] ? 'checked' : ''); ?>

                                        onchange="togglePnfTrayectos(this, 'pnf_<?php echo e($proCodigo); ?>')"
                                        style="width:18px;height:18px;cursor:pointer;">
                                </td>
                                <td style="padding: 6px;" id="pnf_<?php echo e($proCodigo); ?>_trayectos">
                                    <div style="display:flex;flex-wrap:wrap;gap:6px;<?php echo e(!$row['activo'] ? 'opacity:0.5;' : ''); ?>">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $row['trayectos'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $traCodigo => $traData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <label style="display:flex;align-items:center;gap:4px;background:#f8f8f8;border:1px solid #ddd;border-radius:5px;padding:4px 8px;cursor:pointer;font-size:11px;">
                                                <input type="checkbox"
                                                    name="tra_selected[<?php echo e($proCodigo); ?>][<?php echo e($traCodigo); ?>]"
                                                    value="1"
                                                    <?php echo e($traData['selected'] ?? false ? 'checked' : ''); ?>

                                                    class="tra-<?php echo e($proCodigo); ?>"
                                                    onchange="actualizarActivoPnf(<?php echo e($proCodigo); ?>)"
                                                    style="cursor:pointer;">
                                                <span><?php echo e($traData['nombre'] ?? $traCodigo); ?></span>
                                            </label>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                </td>
                                <td align="center"></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($pnfRows)): ?>
                            <tr>
                                <td colspan="5" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                    No hay PNF disponibles.
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align:center;padding:20px;font-weight:bold;color:#999;">
                    No hay PNF disponibles para vincular.
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </fieldset>

        
        <div style="text-align: center; margin-top: 20px;">
            <button type="button" class="cm-btn cm-btn-success" style="margin-right: 10px;"
                onclick="validarFormulario()">
                Guardar Vinculación
            </button>
            <a href="<?php echo e(route('componentes.index')); ?>" class="cm-btn cm-btn-danger">
                Cancelar
            </a>
        </div>
    </form>

    
    <div id="confirmModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)cerrarConfirmModal()">
        <div style="background:#fff;border-radius:10px;padding:28px;max-width:440px;width:92%;box-shadow:0 8px 32px rgba(0,0,0,0.2);text-align:center;">
            <div style="font-size:48px;margin-bottom:12px;">⚠️</div>
            <h3 style="margin:0 0 8px;font-size:16px;color:#333;">¿Está seguro?</h3>
            <p id="confirmMsg" style="margin:0 0 20px;font-size:13px;color:#666;">Se guardará la vinculación seleccionada.</p>
            <div style="display:flex;gap:12px;justify-content:center;">
                <button type="button" class="cm-btn cm-btn-success" onclick="confirmarGuardar()" style="min-width:100px;">Sí, guardar</button>
                <button type="button" class="cm-btn cm-btn-danger" onclick="cerrarConfirmModal()" style="min-width:100px;">Cancelar</button>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    var pendienteSubmit = false;

    function toggleComponenteLabel(checkbox) {
        var label = checkbox.closest('.comp-label');
        if (checkbox.checked) {
            label.style.background = '#e8f5e9';
            label.style.borderColor = '#198754';
        } else {
            label.style.background = '';
            label.style.borderColor = '';
        }
        actualizarContador();
    }

    function actualizarContador() {
        var checked = document.querySelectorAll('.comp-checkbox:checked');
        document.getElementById('selectedCount').textContent = checked.length;
    }

    function seleccionarTodos(seleccionar) {
        var checkboxes = document.querySelectorAll('.comp-checkbox');
        checkboxes.forEach(function(cb) {
            cb.checked = seleccionar;
            toggleComponenteLabel(cb);
        });
    }

    function togglePnfTrayectos(checkbox, prefix) {
        var trayectosContainer = document.getElementById(prefix + '_trayectos');
        var traCheckboxes = trayectosContainer.querySelectorAll('input[type="checkbox"]');
        traCheckboxes.forEach(function(cb) {
            cb.checked = checkbox.checked;
        });
    }

    function actualizarActivoPnf(proCodigo) {
        var checkboxes = document.querySelectorAll('.tra-' + proCodigo);
        var algunSeleccionado = false;
        checkboxes.forEach(function(cb) {
            if (cb.checked) algunSeleccionado = true;
        });
        var pnfCheckbox = document.querySelector('input[name="pnf_activo[' + proCodigo + ']"]');
        if (pnfCheckbox) {
            pnfCheckbox.checked = algunSeleccionado;
        }
    }

    function validarFormulario() {
        var componentesSeleccionados = document.querySelectorAll('.comp-checkbox:checked');
        if (componentesSeleccionados.length === 0) {
            showNotifyToast('warning', 'Debe seleccionar al menos un componente.');
            return;
        }
        var pnfActivos = document.querySelectorAll('input[name^="pnf_activo["]:checked');
        if (pnfActivos.length === 0) {
            showNotifyToast('warning', 'Debe seleccionar al menos un PNF con trayectos.');
            return;
        }
        document.getElementById('confirmMsg').textContent =
            'Se guardará la vinculación para ' + componentesSeleccionados.length + ' componente(s).';
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function cerrarConfirmModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    function confirmarGuardar() {
        document.getElementById('vinculacionForm').submit();
    }

    // Inicializar contador
    document.addEventListener('DOMContentLoaded', function() {
        actualizarContador();
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views\componentes\vinculacion_global.blade.php ENDPATH**/ ?>