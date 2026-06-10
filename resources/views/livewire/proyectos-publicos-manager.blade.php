<div>
    <style>
        .cm-btn {
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 6px; padding: 0.55rem 0.95rem;
            font-size: 0.92rem; font-weight: 600;
            border: 1px solid transparent; cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
            text-decoration: none;
        }
        .cm-btn:hover { transform: translateY(-1px); }
        .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
        .cm-btn-secondary { background: #f4f4f4; border: 1px solid #c2c2c2; color: #222; }
        .cm-btn-sm { padding: 0.35rem 0.75rem; font-size: 0.85rem; }
    </style>

    <h2 style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px; color: #1a1a2e;">Proyectos Publicados</h2>

    @if($mensaje)
        <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px; display: flex; justify-content: space-between; align-items: center;">
            <span>{{ $mensaje }}</span>
            <a href="#" wire:click.prevent="limpiarMensaje" style="font-size:16px; font-weight:bold; text-decoration:none; color:inherit;">&times;</a>
        </div>
    @endif

    @if($selectedPubId)
        @php
            $pub = $publicaciones->firstWhere('id', $selectedPubId);
        @endphp
        @if($pub)
            <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
                <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                    {{ $pub->proyecto->titulo ?? 'Proyecto no disponible' }}
                </legend>

                <div style="margin-bottom: 12px;">
                    <button type="button" wire:click="cerrar" class="cm-btn cm-btn-secondary cm-btn-sm">&larr; Volver</button>
                </div>

                <p><b>Resumen:</b> {{ $pub->proyecto->resumen ?? '(sin resumen)' }}</p>
                <p><b>Fecha de publicaci&oacute;n:</b> {{ $pub->created_at ? $pub->created_at->format('d/m/Y') : '-' }}</p>

                <hr style="border:none; border-top:1px solid #ccc; margin:15px 0;">

                <h4 style="margin:0 0 10px 0;">Comentarios</h4>

                @if($comentarios->isEmpty())
                    <p style="color:#777; font-size:12px; font-style:italic;">No hay comentarios todav&iacute;a.</p>
                @else
                    @foreach($comentarios as $c)
                        <div style="background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; padding: 8px; margin-bottom: 6px;">
                            <div style="font-size: 10px; color: #888; margin-bottom: 3px;">
                                {{ $c->fecha_creacion ? $c->fecha_creacion->format('d/m/Y h:i A') : '' }}
                                &middot;
                                {{ $c->nombre_contacto ?? ($c->usuarioExterno?->nombre ?? 'Anónimo') }}
                            </div>
                            <div style="font-size: 13px;">{{ $c->descripcion }}</div>
                        </div>
                    @endforeach
                @endif

                <div style="margin-top: 12px;">
                    @if(!auth()->check())
                        <div style="margin-bottom: 6px;">
                            <input type="text" wire:model="nombreContacto" placeholder="Tu nombre"
                                style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
                            @error('nombreContacto')<div style="color:red;font-size:10px;margin-top:2px;">{{ $message }}</div>@enderror
                        </div>
                    @endif
                    <textarea wire:model="nuevoComentario" rows="3"
                        style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px;"
                        placeholder="Escribe un comentario..."></textarea>
                    @error('nuevoComentario')<div style="color:red;font-size:10px;margin-top:3px;">{{ $message }}</div>@enderror
                    <div style="text-align: right; margin-top: 6px;">
                        <button type="button" wire:click="comentar" class="cm-btn cm-btn-primary cm-btn-sm">Enviar Comentario</button>
                    </div>
                </div>
            </fieldset>
        @endif
    @else
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Proyectos Publicados</legend>

            @if($publicaciones->isEmpty())
                <p style="color:#666; font-style:italic; padding: 10px;">No hay proyectos publicados.</p>
            @else
                <table width="100%" border="1" cellpadding="5" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                            <th width="5%">N&deg;</th>
                            <th width="30%">T&iacute;tulo</th>
                            <th width="20%">Resumen</th>
                            <th width="12%">Publicado</th>
                            <th width="15%">Comentarios</th>
                            <th width="18%">Acci&oacute;n</th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        @foreach($publicaciones as $pub)
                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                                <td align="center">{{ $loop->iteration }}</td>
                                <td>{{ $pub->proyecto->titulo ?? 'N/A' }}</td>
                                <td style="font-size:10px;">{{ \Illuminate\Support\Str::limit($pub->proyecto->resumen ?? '', 60) }}</td>
                                <td align="center">{{ $pub->created_at ? $pub->created_at->format('d/m/Y') : '-' }}</td>
                                <td align="center">{{ $pub->comentarios->count() }}</td>
                                <td align="center">
                                    <button type="button" wire:click.prevent="seleccionar({{ $pub->id }})"
                                        class="cm-btn cm-btn-secondary cm-btn-sm">Ver</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </fieldset>
    @endif
</div>
