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
    </style>
    <h2 class="titulo" style="margin-bottom: 10px; font-weight: bolder;">Equipos de proyecto</h2>

    <p style="font-size: 11px; color: #444; margin-bottom: 12px;">
        Registre el <strong>grupo de proyecto</strong> eligiendo estudiantes de la <strong>secci&oacute;n del PNF</strong>.
        Queda identificado con la clave <code>EQGRP:&hellip;</code> para usarlo al registrar el expediente.
    </p>

    @if (session()->has('message'))
        <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px; font-size: 12px;">
            {{ session('message') }}</div>
    @endif
    @if (session()->has('message_error'))
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px; font-size: 12px;">
            {{ session('message_error') }}</div>
    @endif

    @if (!$tablaLista)
        <div style="background: #fff3cd; padding: 10px; font-size: 11px; margin-bottom: 12px;">
            Falta la tabla <code>grupo_proyecto_modulo</code> en MySQL repositorio (solo del m&oacute;dulo, no es intranet).
            Ejecute:
            <code>php artisan migrate
                --path=database/migrations/2026_05_26_100000_create_grupo_proyecto_modulo_table.php</code>
        </div>
    @endif

    @if ($viewMode === 'list')
        <div style="margin-bottom: 10px; display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
            <select wire:model.live="filterLapso" class="grp-filter-select" wire:loading.attr="disabled">
                <option value="">Lapso</option>
                @foreach ($lapsos as $l)
                    <option value="{{ $l->lap_codigo }}">{{ $l->lap_nombre }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterPrograma" class="grp-filter-select" @if (!$filterLapso || $isProfessor) disabled @endif wire:loading.attr="disabled">
                <option value="">PNF / Programa</option>
                @foreach ($programas as $p)
                    <option value="{{ $p->pro_codigo }}">{{ $p->pro_siglas }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterSeccion" class="grp-filter-select" @if (!$filterLapso || !$filterPrograma) disabled @endif wire:loading.attr="disabled">
                <option value="">Secci&oacute;n</option>
                @foreach ($secciones as $s)
                    <option value="{{ $s->sec_codigo }}">{{ $s->sec_nombre }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterPrograma" class="grp-filter-select" @if (!$filterLapso) disabled @endif>
                <option value="">PNF / Programa</option>
                @foreach ($programas as $p)
                    <option value="{{ $p->pro_codigo }}">{{ $p->pro_siglas }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterSeccion" class="grp-filter-select" @if (!$filterLapso) disabled @endif>
                <option value="">Secci&oacute;n</option>
                @foreach ($secciones as $s)
                    <option value="{{ $s->sec_codigo }}">{{ $s->sec_nombre }}</option>
                @endforeach
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
                    @forelse($gruposList as $g)
                        <tr>
                            <td><b>{{ $g->nombre }}</b></td>
                            <td>{{ $g->pro_siglas ?: ($g->pro_nombre ?: '—') }}</td>
                            <td>{{ $g->sec_nombre ?: 'Sec. ' . $g->sec_codigo }}</td>
                            <td>{{ $g->lap_nombre ?: '—' }}</td>
                            <td align="center">{{ $g->integrantes }}</td>
                            <td><code style="font-size:9px;">{{ $g->clave }}</code></td>
                            <td align="center" nowrap>
                                <button type="button" class="cm-btn cm-btn-secondary cm-btn-sm"
                                    wire:click="editarGrupo({{ $g->grp_codigo }})">Editar</button>
                                <button type="button" class="cm-btn cm-btn-danger cm-btn-sm"
                                    wire:click="eliminarGrupo({{ $g->grp_codigo }})"
                                    wire:confirm="&iquest;Eliminar este grupo?">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" align="center">No hay grupos registrados. Cree uno con integrantes de la
                                secci&oacute;n.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $gruposList->links() }}
        </fieldset>
    @else
        <fieldset style="border: 2px solid #8b0000; padding: 10px;">
            <legend style="font-weight: bold;">{{ $editingGrpCodigo ? 'Editar grupo' : 'Registrar grupo de proyecto' }}
            </legend>
            <table width="100%" style="font-size: 11px;">
                <tr>
                    <td width="50%"><b>Nombre del proyecto:</b><br><input wire:model="nombreGrupo" type="text"
                            class="grp-filter-input" style="width:90%;"></td>
                    <td><b>Comunidad:</b><br>
                        <div style="display: flex; gap: 4px; align-items: center;">
                            <select wire:model="comunidadId" class="grp-filter-select" style="flex:1;">
                                <option value="">&mdash;</option>
                                @foreach ($comunidades as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
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
                                @foreach ($lapsos as $l)
                                    <option value="{{ $l->lap_codigo }}">{{ $l->lap_nombre }}</option>
                                @endforeach
                            </select>
                            <select wire:model.live="filterPrograma" class="grp-filter-select"
                                @if (!$filterLapso || ($isProfessor && $viewMode === 'form')) disabled @endif wire:loading.attr="disabled">
                                <option value="">PNF</option>
                                @foreach ($programas as $p)
                                    <option value="{{ $p->pro_codigo }}">{{ $p->pro_siglas }}</option>
                                @endforeach
                            </select>
                            <select wire:model.live="filterSeccion" class="grp-filter-select"
                                @if (!$filterLapso || !$filterPrograma) disabled @endif wire:loading.attr="disabled">
                                <option value="">Secci&oacute;n</option>
                                @foreach ($secciones as $s)
                                    <option value="{{ $s->sec_codigo }}">{{ $s->sec_nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($filterLapso && $filterSeccion)
                            @php
                                $lapLabel = $lapsos->firstWhere('lap_codigo', (int)$filterLapso)?->lap_nombre ?? 'Lapso #'.$filterLapso;
                                $proLabel = $programas->firstWhere('pro_codigo', (int)$filterPrograma)?->pro_siglas ?? '—';
                                $secLabel = $secciones->firstWhere('sec_codigo', (int)$filterSeccion)?->sec_nombre ?? 'Sección #'.$filterSeccion;
                            @endphp
                            <div style="margin-top:6px; background:#f0f7f0; border:1px solid #b8d4b8; border-radius:4px; padding:6px 10px; font-size:12px;">
                                <b>Sección seleccionada:</b>
                                {!! html_entity_decode($proLabel) !!} &middot; {!! html_entity_decode($secLabel) !!}
                                @if($lapLabel) <span style="color:#666;">({!! html_entity_decode($lapLabel) !!})</span>@endif
                            </div>
                        @else
                            <p style="font-size: 11px; color: #856404; margin-top:4px;">
                                Seleccione lapso, PNF y sección para ver los estudiantes candidatos.
                            </p>
                        @endif
                    </td>
                </tr>
            </table>

            @if ($filterSeccion !== '')
                <div style="margin-top: 12px; padding: 8px; background: #f5f5f5; border: 1px solid #ccc;">
                    <b>Agregar integrante (de la secci&oacute;n):</b><br>
                    <div style="display: flex; gap: 16px; align-items: center; margin-top: 4px;">
                        <select wire:model="selectedCedula" class="grp-filter-select" style="flex: 1;">
                            <option value="">Estudiante inscrito&hellip;</option>
                            @foreach ($candidatos as $c)
                                <option value="{{ $c->cedula }}">{{ $c->apellido }}, {{ $c->nombre }}
                                    ({{ $c->cedula }})
                                </option>
                            @endforeach
                        </select>
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
                        @forelse($miembrosSeleccionados as $m)
                            <tr>
                                <td>{{ $m['cedula'] }}</td>
                                <td>{{ $m['apellido'] }}, {{ $m['nombre'] }}</td>
                                <td>{{ $m['rol_name'] }}</td>
                                <td><a href="#"
                                        wire:click.prevent="quitarIntegrante({{ json_encode($m['cedula']) }})">Quitar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" align="center">Agregue al menos un l&iacute;der y los autores del grupo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <p style="font-size: 11px; color: #856404;">Seleccione lapso y secci&oacute;n para ver estudiantes candidatos.
                </p>
            @endif

            <div style="margin-top: 14px;">
                <button type="button" class="cm-btn cm-btn-success" wire:click="registrarGrupo">Registrar
                    grupo</button>
                <button type="button" class="cm-btn cm-btn-danger" wire:click="volver">Cancelar</button>
            </div>
            <p style="font-size: 10px; color: #555; margin-top: 8px;">El registro del expediente del proyecto es un
                paso aparte; luego elija este grupo al crear el expediente.</p>

            @if ($mostrarModalComunidad)
                <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;">
                    <div style="background:#fff;border-radius:8px;padding:20px;max-width:600px;width:90%;max-height:90vh;overflow-y:auto;">
                        <h3 style="margin-top:0;font-size:16px;">Registrar nueva comunidad</h3>

                        @error('modalNombre') <div style="color:red;font-size:10px;margin-bottom:4px;">{{ $message }}</div> @enderror
                        <table width="100%" style="font-size:11px;">
                            <tr>
                                <td width="30%"><b>Nombre:</b> <span style="color:red;">*</span></td>
                                <td><input wire:model="modalNombre" type="text" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;"></td>
                            </tr>
                            <tr>
                                <td><b>RIF:</b></td>
                                <td><input wire:model="modalRif" type="text" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;"></td>
                            </tr>
                            <tr>
                                <td><b>Correo:</b></td>
                                <td><input wire:model="modalCorreo" type="email" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;"></td>
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
                                        @foreach ($modalEstados as $e)
                                            <option value="{{ $e->est_codigo }}">{{ $e->est_nombre }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Municipio:</b> <span style="color:red;">*</span></td>
                                <td>
                                    <select wire:model="modalMunicipioId" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;background:#fff;">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($modalMunicipios as $m)
                                            <option value="{{ $m->mun_codigo }}">{{ $m->mun_nombre }}</option>
                                        @endforeach
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
            @endif
        </fieldset>
    @endif
</div>
