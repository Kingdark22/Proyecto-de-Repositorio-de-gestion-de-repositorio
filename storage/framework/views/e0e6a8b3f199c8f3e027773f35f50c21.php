<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px; text-align: center;">Acervo Institucional de Proyectos (UPTP)</h2>
    <p style="text-align: center; color: #555; font-size: 11px; margin-bottom: 20px;">
        Consulta la producción intelectual validada y bajo custodia de la Universidad Politécnica Territorial Juan de Jesús Montilla.
    </p>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Búsqueda en el Repositorio</legend>
        <table width="100%" border="0" cellpadding="8" cellspacing="0" style="font-size: 11px;">
            <tr>
                <td width="50%">
                    <b>Búsqueda global (Título, resumen):</b><br>
                    <input wire:model.live="search" type="text" style="width: 95%;" placeholder="Título o palabra clave...">
                </td>
                <td width="25%">
                    <b>Filtrar por Programa:</b><br>
                    <select wire:model.live="filterPrograma" style="width: 95%;">
                        <option value="">Todos los programas</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $programas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?> <option value="<?php echo e($p->id); ?>"><?php echo e($p->siglas); ?> - <?php echo e($p->nombre); ?></option> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </td>
                <td width="25%">
                    <b>Lapso Académico:</b><br>
                    <select wire:model.live="filterLapso" style="width: 95%;">
                        <option value="">Cualquier Lapso...</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $lapsos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($l->lap_codigo); ?>"><?php echo e($l->lap_nombre); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </td>
            </tr>
        </table>
    </fieldset>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Resultados de Búsqueda</legend>

        <table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
            <thead style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                <tr>
                    <th width="35%" align="center">Información del Proyecto</th>
                    <th width="40%" align="center">Resumen Abstracto</th>
                    <th width="15%" align="center">Clasificación</th>
                    <th width="10%" align="center">Descarga</th>
                </tr>
            </thead>
            <tbody class="Texto">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $proyectos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <tr style="background-color: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF'); ?>;" valign="top">
                        <td style="padding: 10px;">
                            <span style="font-weight: bold; font-size: 12px; color: #000;"><?php echo e($p->titulo); ?></span>
                            <br><br>
                            <span style="font-size: 10px; font-weight: bold; color: #333;">Autores: <?php echo e($p->autores ?? 'N/A'); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->tutor): ?>
                                <br><span style="font-size: 10px; color: #555;">Tutor: <?php echo e($p->tutor->nombre); ?> <?php echo e($p->tutor->apellido); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td align="justify" style="padding: 10px; font-size: 10px; color: #333;">
                            <?php echo e(Str::limit($p->resumen, 200)); ?>

                        </td>
                        <td align="center" style="padding: 10px;">
                            <span style="font-size: 10px; font-weight: bold;">Lapso: <?php echo e($p->lapso_academico->nombre ?? 'N/A'); ?></span>
                        </td>
                        <td align="center" style="padding: 10px;">
                            <?php $pubDocs = $p->documentos; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pubDocs->isNotEmpty()): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pubDocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <a href="<?php echo e(route('documentos.serve', ['path' => $doc->pd_archivo_path])); ?>" target="_blank" style="display: inline-block; text-align: center; color: #0000EE; text-decoration: none; font-weight: bold; margin-top: 5px;">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/PDF_file_icon.svg/640px-PDF_file_icon.svg.png" alt="PDF" style="width: 24px; height: 24px; border: 0; margin-bottom: 2px;">
                                        <br><?php echo e($doc->componente?->nombre ?? 'Documento'); ?>

                                    </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php else: ?>
                                <span style="font-size: 10px; color: #999;">Sin Documento</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proyectos->isEmpty()): ?>
                    <tr>
                        <td colspan="4" align="center" style="padding: 30px; font-weight: bold; background-color: #FFFFFF;">
                            No se encontraron proyectos publicados que coincidan con los criterios.
                        </td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 15px;">
            <?php echo e($proyectos->links()); ?>

        </div>
    </fieldset>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('refresh-icons', () => {
                setTimeout(() => lucide.createIcons(), 10);
            });
        });
    </script>
</div>
<?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views\livewire\repositorio-publico.blade.php ENDPATH**/ ?>