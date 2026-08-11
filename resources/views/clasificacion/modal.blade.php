@php
    $puedeGestionar = auth()->user() && (
        auth()->user()->hasRole('administrador', 'coordinador', 'gestionador')
    );
@endphp
<div id="modalClasificacion" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:10000; background:rgba(0,0,0,0.5);" onclick="if(event.target===this)cerrarModalClasificacion()">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:920px; max-height:90vh; background:#fff; border-radius:10px; box-shadow:0 8px 32px rgba(0,0,0,0.3); display:flex; flex-direction:column; overflow:hidden;">
        <!-- Header -->
        <div style="background:#8b0000; color:#fff; padding:14px 20px; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
            <h3 style="margin:0; font-size:18px; font-weight:bold;">Clasificación de Proyectos</h3>
            <button onclick="cerrarModalClasificacion()" style="background:none; border:none; color:#fff; font-size:22px; cursor:pointer; padding:0 4px;" title="Cerrar">&times;</button>
        </div>
        <!-- Body -->
        <div style="flex:1; overflow-y:auto; padding:16px 20px;">
            {{-- Accordion: Comunidades --}}
            <div class="cls-accordion-item" data-tipo="comunidades">
                <div class="cls-accordion-header" onclick="toggleClasificacion(this)">
                    <span class="cls-arrow">&#9654;</span>
                    <span style="font-weight:bold; font-size:14px;">Comunidades</span>
                    @if($puedeGestionar)
                    <a href="{{ route('comunidades.create') }}" onclick="event.stopPropagation()" class="cls-btn-add" title="Registrar comunidad">+ Nuevo</a>
                    @endif
                </div>
                <div class="cls-accordion-body">
                    <div class="cls-search-bar">
                        <input type="text" placeholder="Buscar comunidad..." class="cls-search-input" oninput="buscarCatalogo('comunidades', this)">
                    </div>
                    <div class="cls-table-container" id="contenedor-comunidades">
                        <div style="text-align:center;padding:20px;color:#888;">Cargando...</div>
                    </div>
                </div>
            </div>

            {{-- Accordion: Líneas de Investigación --}}
            <div class="cls-accordion-item" data-tipo="lineas">
                <div class="cls-accordion-header" onclick="toggleClasificacion(this)">
                    <span class="cls-arrow">&#9654;</span>
                    <span style="font-weight:bold; font-size:14px;">Líneas de Investigación</span>
                    @if($puedeGestionar)
                    <a href="{{ route('lineas-investigacion.create') }}" onclick="event.stopPropagation()" class="cls-btn-add" title="Registrar línea">+ Nuevo</a>
                    @endif
                </div>
                <div class="cls-accordion-body">
                    <div class="cls-search-bar">
                        <input type="text" placeholder="Buscar línea..." class="cls-search-input" oninput="buscarCatalogo('lineas', this)">
                    </div>
                    <div class="cls-table-container" id="contenedor-lineas">
                        <div style="text-align:center;padding:20px;color:#888;">Cargando...</div>
                    </div>
                </div>
            </div>

            {{-- Accordion: Tipos de Investigación --}}
            <div class="cls-accordion-item" data-tipo="tipos">
                <div class="cls-accordion-header" onclick="toggleClasificacion(this)">
                    <span class="cls-arrow">&#9654;</span>
                    <span style="font-weight:bold; font-size:14px;">Tipos de Investigación</span>
                    @if($puedeGestionar)
                    <a href="{{ route('tipos-investigacion.create') }}" onclick="event.stopPropagation()" class="cls-btn-add" title="Registrar tipo">+ Nuevo</a>
                    @endif
                </div>
                <div class="cls-accordion-body">
                    <div class="cls-search-bar">
                        <input type="text" placeholder="Buscar tipo..." class="cls-search-input" oninput="buscarCatalogo('tipos', this)">
                    </div>
                    <div class="cls-table-container" id="contenedor-tipos">
                        <div style="text-align:center;padding:20px;color:#888;">Cargando...</div>
                    </div>
                </div>
            </div>

            {{-- Accordion: Metodologías --}}
            <div class="cls-accordion-item" data-tipo="metodologias">
                <div class="cls-accordion-header" onclick="toggleClasificacion(this)">
                    <span class="cls-arrow">&#9654;</span>
                    <span style="font-weight:bold; font-size:14px;">Metodologías</span>
                    @if($puedeGestionar)
                    <a href="{{ route('metodologia-investigacion.create') }}" onclick="event.stopPropagation()" class="cls-btn-add" title="Registrar metodología">+ Nuevo</a>
                    @endif
                </div>
                <div class="cls-accordion-body">
                    <div class="cls-search-bar">
                        <input type="text" placeholder="Buscar metodología..." class="cls-search-input" oninput="buscarCatalogo('metodologias', this)">
                    </div>
                    <div class="cls-table-container" id="contenedor-metodologias">
                        <div style="text-align:center;padding:20px;color:#888;">Cargando...</div>
                    </div>
                </div>
            </div>

            {{-- Accordion: Objetivos de Investigación --}}
            <div class="cls-accordion-item" data-tipo="objetivos">
                <div class="cls-accordion-header" onclick="toggleClasificacion(this)">
                    <span class="cls-arrow">&#9654;</span>
                    <span style="font-weight:bold; font-size:14px;">Objetivos de Investigación</span>
                    @if($puedeGestionar)
                    <a href="{{ route('objetivos-investigacion.create') }}" onclick="event.stopPropagation()" class="cls-btn-add" title="Registrar objetivo">+ Nuevo</a>
                    @endif
                </div>
                <div class="cls-accordion-body">
                    <div class="cls-search-bar">
                        <input type="text" placeholder="Buscar objetivo..." class="cls-search-input" oninput="buscarCatalogo('objetivos', this)">
                    </div>
                    <div class="cls-table-container" id="contenedor-objetivos">
                        <div style="text-align:center;padding:20px;color:#888;">Cargando...</div>
                    </div>
                </div>
            </div>

            {{-- Accordion: Componentes --}}
            <div class="cls-accordion-item" data-tipo="componentes">
                <div class="cls-accordion-header" onclick="toggleClasificacion(this)">
                    <span class="cls-arrow">&#9654;</span>
                    <span style="font-weight:bold; font-size:14px;">Componentes</span>
                    @if($puedeGestionar)
                    <a href="{{ route('componentes.create') }}" onclick="event.stopPropagation()" class="cls-btn-add" title="Registrar componente">+ Nuevo</a>
                    @endif
                </div>
                <div class="cls-accordion-body">
                    <div class="cls-search-bar">
                        <input type="text" placeholder="Buscar componente..." class="cls-search-input" oninput="buscarCatalogo('componentes', this)">
                    </div>
                    <div class="cls-table-container" id="contenedor-componentes">
                        <div style="text-align:center;padding:20px;color:#888;">Cargando...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
