<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px; text-align: center;">Acervo Institucional de Proyectos (UPTP)</h2>
    <p style="text-align: center; color: #555; font-size: 11px; margin-bottom: 20px;">
        Consulta la producci&oacute;n intelectual validada y bajo custodia de la Universidad Polit&eacute;cnica Territorial Juan de Jes&uacute;s Montilla.
    </p>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Búsqueda en el Repositorio</legend>
        <table width="100%" border="0" cellpadding="8" cellspacing="0" style="font-size: 11px;">
            <tr>
                <td width="50%">
                    <b>B&uacute;squeda global (T&iacute;tulo, resumen):</b><br>
                    <input wire:model.live="search" type="text" style="width: 95%;" placeholder="Título o palabra clave...">
                </td>
                <td width="25%">
                    <b>Filtrar por Programa:</b><br>
                    <select wire:model.live="filterPrograma" style="width: 95%;">
                        <option value="">Todos los programas</option>
                        @foreach($programas as $p) <option value="{{ $p->id }}">{{ $p->siglas }} - {{ $p->nombre }}</option> @endforeach
                    </select>
                </td>
                <td width="25%">
                    <b>Lapso Acad&eacute;mico:</b><br>
                    <select wire:model.live="filterLapso" style="width: 95%;">
                        <option value="">Cualquier Lapso...</option>
                        <option value="2024-II">2024-II</option>
                        <option value="2025-I">2025-I</option>
                        <option value="2025-II">2025-II</option>
                        <option value="2026-I">2026-I</option>
                    </select>
                </td>
            </tr>
        </table>
    </fieldset>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Resultados de B&uacute;squeda</legend>

        <table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
            <thead style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                <tr>
                    <th width="35%" align="center">Informaci&oacute;n del Proyecto</th>
                    <th width="40%" align="center">Resumen Abstracto</th>
                    <th width="15%" align="center">Clasificaci&oacute;n</th>
                    <th width="10%" align="center">Descarga</th>
                </tr>
            </thead>
            <tbody class="Texto">
                @foreach($proyectos as $p)
                    <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                        <td style="padding: 10px;">
                            <span style="font-weight: bold; font-size: 12px; color: #000;">{{ $p->titulo }}</span>
                            <br><br>
                            <span style="font-size: 10px; font-weight: bold; color: #333;">Autores: {{ $p->autores ?? 'N/A' }}</span>
                            @if($p->tutor)
                                <br><span style="font-size: 10px; color: #555;">Tutor: {{ $p->tutor->nombre }} {{ $p->tutor->apellido }}</span>
                            @endif
                        </td>
                        <td align="justify" style="padding: 10px; font-size: 10px; color: #333;">
                            {{ Str::limit($p->resumen, 200) }}
                        </td>
                        <td align="center" style="padding: 10px;">
                            <span style="font-size: 10px; font-weight: bold;">Lapso: {{ $p->lapso_academico->nombre ?? 'N/A' }}</span>
                        </td>
                        <td align="center" style="padding: 10px;">
                            @php $pubDocs = $p->documentos; @endphp
                            @if($pubDocs->isNotEmpty())
                                @foreach($pubDocs as $doc)
                                    <a href="{{ route('documentos.serve', ['path' => $doc->pd_archivo_path]) }}" target="_blank" style="display: inline-block; text-align: center; color: #0000EE; text-decoration: none; font-weight: bold; margin-top: 5px;">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/PDF_file_icon.svg/640px-PDF_file_icon.svg.png" alt="PDF" style="width: 24px; height: 24px; border: 0; margin-bottom: 2px;">
                                        <br>{{ $doc->componente?->nombre ?? 'Documento' }}
                                    </a>
                                @endforeach
                            @else
                                <span style="font-size: 10px; color: #999;">Sin Documento</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if($proyectos->isEmpty())
                    <tr>
                        <td colspan="4" align="center" style="padding: 30px; font-weight: bold; background-color: #FFFFFF;">
                            No se encontraron proyectos publicados que coincidan con los criterios.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <div style="margin-top: 15px;">
            {{ $proyectos->links() }}
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
