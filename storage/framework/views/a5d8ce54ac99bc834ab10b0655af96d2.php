<?php $__env->startSection('title', 'Editar Comunidad'); ?>
<?php $__env->startSection('header', 'Editar Comunidad'); ?>

<?php $__env->startPush('styles'); ?>
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
    .cm-btn:hover { transform: translateY(-1px); }
    .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
    .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
    .obligatorio { color: red; font-weight: bold; }
    .validation-error { color: #dc3545; font-size: 11px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
        <legend style="padding:0 5px;font-weight:bold;">&nbsp;</legend>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold;">
                <ul style="margin:0;padding-left:20px;"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?> <li><?php echo e($e); ?></li> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></ul>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form method="POST" action="<?php echo e(route('comunidades.update', $id)); ?>" style="margin: 0;" onsubmit="return validarFormularioComunidad(this)">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <input type="hidden" name="id_edit" value="<?php echo e($id); ?>">
            <table width="100%" border="0" cellpadding="6" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="50%" style="vertical-align: top; padding: 0 4px 10px 0;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Nombre:</b>
                            <div style="flex: 1;">
                                 <input name="nombre" type="text" value="<?php echo e(old('nombre', $datos['nombre'])); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" required oninput="validarNombre(this)" data-check-url="/comunidades/check-nombre">
                                <span class="obligatorio">*</span>
                                <span id="nombreStatus" style="font-size:11px;display:none;"></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><br><span class="validation-error"><?php echo e($message); ?></span><?php unset($message);
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
                                    <select name="rif_letra" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 11px; width: 48px;" onchange="validarRif(this)">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['V','C','J','G','P']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <option value="<?php echo e($l); ?>" <?php echo e(($parsed['letra'] ?? 'J') == $l ? 'selected' : ''); ?>><?php echo e($l); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                    <input name="rif_numero" type="text" inputmode="numeric" maxlength="9" value="<?php echo e(old('rif_numero', $parsed['numero'] ?? '')); ?>"
                                        style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"
                                        data-check-url="<?php echo e(route('comunidades.check-rif')); ?>"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,''); validarRif(this)"
                                        placeholder="Número (máx. 9 dígitos)">
                                    <span id="rifStatus" style="display:none; font-size:11px;"></span>
                                </div>
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['rif_numero'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><br><span class="validation-error"><?php echo e($message); ?></span><?php unset($message);
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
                                    <input name="correo" type="email" value="<?php echo e(old('correo', $datos['correo'])); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="ejemplo@gmail.com" maxlength="40" data-check-url="<?php echo e(route('comunidades.check-email')); ?>" oninput="validarCorreoRemoto(this)">
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                <span id="correoStatus" style="display:none; font-size:11px;"></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['correo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><br><span class="validation-error"><?php echo e($message); ?></span><?php unset($message);
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
                                    <select name="prefijo_telefono" style="padding: 6px 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: #fff; font-size: 11px;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['0424','0414','0412','0422','0416','0426']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <option value="<?php echo e($p); ?>" <?php echo e($prefijo == $p ? 'selected' : ''); ?>><?php echo e($p); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                    <input name="numero_telefono" type="text" value="<?php echo e(old('numero_telefono', $numeroTel)); ?>" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="XXX-XXXX" maxlength="7" oninput="this.value=this.value.replace(/\D/g,'').slice(0,7)">
                                </div>
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['numero_telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><br><span class="validation-error"><?php echo e($message); ?></span><?php unset($message);
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
                                    <select name="estado_id" id="estado_id" onchange="cargarMunicipios(this.value)" style="width: 100%; padding: 6px 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; background: #fff; font-size: 11px;" required>
                                        <option value="">-- Seleccione estado --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $estados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <option value="<?php echo e($e->est_codigo); ?>" <?php echo e($datos['estado_id'] == $e->est_codigo ? 'selected' : ''); ?>><?php echo e($e->est_nombre); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                    <span class="obligatorio">*</span>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['estado_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><br><span class="validation-error"><?php echo e($message); ?></span><?php unset($message);
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
                                    <select name="municipio_id" id="municipio_id" style="width: 100%; padding: 6px 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; background: #fff; font-size: 11px;" required>
                                        <option value="">-- Seleccione municipio --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $municipios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <option value="<?php echo e($m->mun_codigo); ?>" <?php echo e($datos['municipio_id'] == $m->mun_codigo ? 'selected' : ''); ?>><?php echo e($m->mun_nombre); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                    <span class="obligatorio">*</span>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['municipio_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><br><span class="validation-error"><?php echo e($message); ?></span><?php unset($message);
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
                                    <input name="dir_nombre" type="text" value="<?php echo e(old('dir_nombre', $datos['dir_nombre'])); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="Av./Calle/Casa Nro., sector, referencia..." required>
                                    <span class="obligatorio" style="margin-top: 5px;">*</span>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['dir_nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><br><span class="validation-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 15px; font-size: 13px;">
                Los campos con <span class="obligatorio">*</span> son obligatorios
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button type="button" onclick="window.location='<?php echo e(route('comunidades.index')); ?>'" class="cm-btn cm-btn-danger" style="margin-right: 10px;">Cancelar</button>
                <button type="submit" class="cm-btn cm-btn-primary">Guardar</button>
            </div>
        </form>
    </fieldset>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function cargarMunicipios(estadoId) {
    var munSelect = document.getElementById('municipio_id');
    munSelect.innerHTML = '<option value="">-- Cargando... --</option>';
    if (!estadoId) {
        munSelect.innerHTML = '<option value="">-- Seleccione municipio --</option>';
        return;
    }
    var selectedMun = '<?php echo e($datos['municipio_id']); ?>';
    fetch('/comunidades/municipios/' + estadoId)
        .then(r => r.json())
        .then(data => {
            munSelect.innerHTML = '<option value="">-- Seleccione municipio --</option>';
            data.forEach(function(m) {
                var opt = document.createElement('option');
                opt.value = m.mun_codigo;
                opt.textContent = m.mun_nombre;
                if (selectedMun == m.mun_codigo) opt.selected = true;
                munSelect.appendChild(opt);
            });
        })
        .catch(() => {
            munSelect.innerHTML = '<option value="">-- Error al cargar --</option>';
        });
}
document.addEventListener('DOMContentLoaded', function() {
    var est = document.getElementById('estado_id');
    if (est && est.value) { cargarMunicipios(est.value); }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views\comunidades\edit.blade.php ENDPATH**/ ?>