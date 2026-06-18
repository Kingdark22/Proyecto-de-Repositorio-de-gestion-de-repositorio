<div class="ppm-manager">
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Profesores de Proyecto</h2>

    {{-- Mensaje de aviso solo si no hay datos en absoluto --}}
    @if(! $intranetDisponible && $docentes->isEmpty() && $search === '' && ! $programaFilter)
        <div style="background-color: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; border-radius: 4px; margin-bottom: 15px; font-size: 13px; text-align: center;">
            El sistema está operando con la base de datos de respaldo.
        </div>
    @endif

    <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
        <legend style="font-weight: bold; font-size: 12px;">Filtros</legend>
        <table class="ppm-filters-table" width="100%" border="0" cellpadding="8" cellspacing="0">
            <tr>
                <td width="25%"><b>Lapso académico:</b><br>
                    <select wire:model.live="lapsoFilter">
                        <option value="">- Lapso -</option>
                        @foreach($lapsos as $lap)
                            <option value="{{ $lap->lap_codigo }}">{{ $lap->lap_nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td width="25%"><b>Programa:</b><br>
                    <select wire:model.live="programaFilter" @disabled(!$lapsoFilter)>
                        <option value="">- Todos -</option>
                        @foreach($programas as $pro)
                            <option value="{{ $pro->pro_codigo }}">{{ trim($pro->pro_siglas) }} - {{ trim($pro->pro_nombre) }}</option>
                        @endforeach
                    </select>
                </td>
                <td width="25%"><b>Trayecto:</b><br>
                    <select wire:model.live="trayectoFilter" @disabled(!$lapsoFilter)>
                        <option value="">- Todos -</option>
                        @foreach($trayectosCatalogo as $tra)
                            <option value="{{ $tra->tra_codigo }}">{{ trim($tra->tra_nombre) }}</option>
                        @endforeach
                    </select>
                </td>
                <td width="25%"><b>Sección:</b><br>
                    <select wire:model.live="seccionFilter" @disabled(!$lapsoFilter)>
                        <option value="">- Todas -</option>
                        @foreach($secciones as $sec)
                            <option value="{{ $sec->sec_codigo }}">{{ trim($sec->sec_nombre) }}@if($sec->pro_siglas) ({{ trim($sec->pro_siglas) }})@endif</option>
                        @endforeach
                    </select>
                </td>
            </tr>
            <tr class="ppm-search-row">
                <td colspan="4"><b>Búsqueda:</b><br>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cédula, nombre, programa, trayecto, sección, UC...">
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
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0; position: relative;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Docentes en intranet (lapso seleccionado)</legend>

        <div wire:loading.flex wire:target="lapsoFilter, programaFilter, trayectoFilter, seccionFilter, search" 
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 10; justify-content: center; align-items: center; flex-direction: column; gap: 8px;">
            <div style="width: 200px; height: 4px; background: #e0e0e0; border-radius: 2px; overflow: hidden;">
                <div style="width: 40%; height: 100%; background: #8b0000; border-radius: 2px; animation: ppmProgress 1.2s ease-in-out infinite;"></div>
            </div>
            <span style="font-weight: bold; color: #8b0000; font-size: 12px;">Consultando docentes...</span>
        </div>

        <table width="100%" border="1" cellpadding="6" cellspacing="0" class="ppm-table" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px; position: relative;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                    <th width="24%">Docente / cédula</th>
                    <th width="12%">PNF</th>
                    <th width="16%">Asignación intranet</th>
                    <th width="13%">Módulo repositorio</th>
                    <th width="20%">Trayecto, PNF y sección</th>
                    <th width="15%">Acción</th>
                </tr>
            </thead>
            <tbody class="Texto">
                @foreach($docentes as $doc)
                    @php
                        $cedula = $doc->cedula;
                        $habilitado = $doc->habilitado_modulo;
                        $canRevoke = true;
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
                        <td align="center" style="padding: 5px;">
                            @if($habilitado)
                                <span class="ppm-estado ppm-estado--ok">HABILITADO</span>
                            @else
                                <span class="ppm-estado ppm-estado--off">Solo intranet</span>
                            @endif
                        </td>
                        <td align="center" style="padding: 5px;">
                            @if(!$habilitado)
                                @php
                                    $selProCod = $selectedPrograma[$cedula] ?? null;
                                    $selProSiglas = $selProCod ? collect($programas)->firstWhere('pro_codigo', (int)$selProCod)?->pro_siglas ?? '' : '';
                                    $profTrayectos = $selProSiglas
                                        ? $doc->asignaciones->where('programa_siglas', $selProSiglas)->pluck('trayecto_nombre')->unique()->filter()->values()
                                        : collect();
                                    $selTrayecto = $selectedYear[$cedula] ?? '';
                                    $profSecciones = $selProSiglas && $selTrayecto
                                        ? $doc->asignaciones->where('programa_siglas', $selProSiglas)->where('trayecto_nombre', $selTrayecto)->pluck('seccion')->unique()->filter()->values()
                                        : collect();
                                @endphp
                                <div class="ppm-row-inputs" style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                                    <select wire:model.live="selectedPrograma.{{ $cedula }}" style="width: 160px;">
                                        <option value="">- PNF -</option>
                                        @foreach($programas as $pro)
                                            <option value="{{ $pro->pro_codigo }}">{{ trim($pro->pro_siglas) }}</option>
                                        @endforeach
                                    </select>
                                    @if($selProSiglas)
                                        <select wire:model.live="selectedYear.{{ $cedula }}" style="width: 160px;">
                                            <option value="">- Trayecto -</option>
                                            @foreach($profTrayectos as $t)
                                                <option value="{{ $t }}">{{ $t }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @if($selTrayecto && $profSecciones->isNotEmpty())
                                        <select wire:model.live="selectedSection.{{ $cedula }}" style="width: 160px;">
                                            <option value="">- Sección -</option>
                                            @foreach($profSecciones as $s)
                                                <option value="{{ $s }}">Sec. {{ $s }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($selProSiglas)
                                        <input wire:model="selectedSection.{{ $cedula }}" type="text" placeholder="Sección..." style="width: 150px;">
                                    @endif
                                </div>
                            @else
                                <span style="font-weight: bold; color: #8b0000;">{{ $doc->ppm_anio ?? '-' }}</span><br>
                                Sec: {{ $doc->ppm_seccion ?? '-' }}
                            @endif
                        </td>
                        <td align="center" style="padding: 5px;">
                            <button type="button"
                                wire:click="toggleProjectProfessor('{{ $cedula }}')"
                                wire:loading.attr="disabled"
                                wire:target="toggleProjectProfessor"
                                class="ppm-btn-action {{ $habilitado ? 'ppm-btn-action--disable' : 'ppm-btn-action--enable' }}">
                                <span wire:loading.remove wire:target="toggleProjectProfessor">{{ $habilitado ? 'Deshabilitar' : 'Habilitar' }}</span>
                                <span wire:loading wire:target="toggleProjectProfessor">...</span>
                            </button>
                        </td>
                    </tr>
                @endforeach
                @if($docentes->isEmpty())
                    <tr>
                        <td colspan="6" align="center" style="padding: 20px;">
                            No hay docentes en intranet para este lapso o criterio de búsqueda.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div style="margin-top: 10px;">{{ $docentes->links() }}</div>
    </fieldset>
</div>
