<div class="ppm-manager">
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Listado de Profesores de Proyecto</h2>

    {{-- Mensaje de aviso solo si no hay datos en absoluto --}}
    @if(! $intranetDisponible && $docentes->isEmpty() && $search === '')
        <div style="background-color: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; border-radius: 4px; margin-bottom: 15px; font-size: 13px; text-align: center;">
            El sistema está operando con la base de datos de respaldo.
        </div>
    @endif

    <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px; box-sizing: border-box;">
        <legend style="font-weight: bold; font-size: 12px;">Filtros</legend>
        <table class="ppm-filters-table" width="100%" border="0" cellpadding="8" cellspacing="0">
            <tr>
                <td width="30%"><b>Lapso académico:</b><br>
                    <select wire:model.live="lapsoFilter" style="width:100%;padding:6px 8px;border:1px solid #ccc;border-radius:4px;font-size:12px;">
                        <option value="">- Lapso -</option>
                        @foreach($lapsos as $lap)
                            <option value="{{ $lap->lap_codigo }}">{{ $lap->lap_nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td width="70%"><b>Buscar docente:</b><br>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cédula o nombre del docente..." style="width: 100%; padding:6px 8px;border:1px solid #ccc;border-radius:4px;font-size:12px;box-sizing:border-box;">

                </td>
            </tr>
        </table>
    </fieldset>

    <style>
        @keyframes ppmProgress {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(350%); }
        }
    </style>
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0; position: relative; box-sizing: border-box;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Docentes asignados al lapso vigente</legend>

        <div wire:loading.flex wire:target="search, lapsoFilter" 

            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 10; justify-content: center; align-items: center; flex-direction: column; gap: 8px;">
            <div style="width: 200px; height: 4px; background: #e0e0e0; border-radius: 2px; overflow: hidden;">
                <div style="width: 40%; height: 100%; background: #8b0000; border-radius: 2px; animation: ppmProgress 1.2s ease-in-out infinite;"></div>
            </div>
            <span style="font-weight: bold; color: #8b0000; font-size: 12px;">Consultando docentes...</span>
        </div>

        <table width="100%" border="1" cellpadding="6" cellspacing="0" class="ppm-table" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px; position: relative; table-layout: fixed;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                    <th width="25%">Docente / cédula</th>
                    <th width="12%">PNF</th>
                    <th width="30%">Asignación intranet</th>
                    <th width="18%">Módulo</th>
                    <th width="15%">Estatus</th>

                </tr>
            </thead>
            <tbody class="Texto">
                @foreach($docentes as $doc)
                    @php
                        $cedula = $doc->cedula;
                        $habilitado = $doc->habilitado_modulo ?? false;

                    @endphp
                    <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                        <td style="padding: 5px;">
                            <b>{{ $doc->nombre }} {{ $doc->apellido }}</b><br>
                            <span style="font-size: 10px;">{{ $cedula }}</span>
                            @if(trim((string) auth()->user()->usu_cedula) === $cedula)
                                <span style="color: #0000EE; font-size: 10px;"> (Tú)</span>
                            @endif
                        </td>
                        <td align="center" style="padding: 5px; font-weight: bold; font-size: 11px;">
                            {{ $doc->programa_siglas ?: '-' }}
                        </td>
                        <td style="padding: 5px; font-size: 10px;">
                            <strong>Lapso:</strong> {{ $doc->lapso_nombre }}<br>
                            @foreach($doc->asignaciones->take(3) as $asig)
                                &bull; {{ $asig->unidad_siglas }}
                                @if($asig->programa_siglas) ({{ $asig->programa_siglas }}) @endif
                                - Sec. {{ $asig->seccion }}
                                @if($asig->trayecto_nombre) / {{ $asig->trayecto_nombre }} @endif
                                <br>
                            @endforeach
                            @if($doc->asignaciones->count() > 3)
                                <span style="color: #666;">+ {{ $doc->asignaciones->count() - 3 }} más</span>
                            @endif
                        </td>
                        <td align="center" style="padding: 5px; font-size: 10px;">
                            @if($habilitado)
                                <span style="display:inline-block;background:#e8f5e9;border:1px solid #198754;border-radius:4px;padding:2px 8px;font-weight:bold;color:#198754;font-size:10px;">HABILITADO</span>
                                @if($doc->ppm_anio)
                                    <br><span style="font-size:9px;color:#555;">{{ $doc->ppm_anio }} - Sec. {{ $doc->ppm_seccion ?? '-' }}</span>
                                @endif
                            @else
                                <span style="display:inline-block;background:#f5f5f5;border:1px solid #ccc;border-radius:4px;padding:2px 8px;color:#888;font-size:10px;">Solo intranet</span>
                            @endif
                        </td>
                        <td align="center" style="padding: 5px;">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $habilitado ? '#198754' : '#ccc' }};"></span>
                            <span style="font-size:9px;color:#555;display:block;margin-top:2px;">{{ $habilitado ? 'Activo' : 'Inactivo' }}</span>

                        </td>
                    </tr>
                @endforeach
                @if($docentes->isEmpty())
                    <tr>
                        <td colspan="5" align="center" style="padding: 20px;">
                            No hay docentes asignados para el lapso seleccionado.

                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div style="margin-top: 10px;">{{ $docentes->links() }}</div>
    </fieldset>
</div>
