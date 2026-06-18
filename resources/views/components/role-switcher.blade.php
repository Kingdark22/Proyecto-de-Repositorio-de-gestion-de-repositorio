@props(['roleButtons' => [], 'activeRoleLabel' => null])

<style>
    .rol-ventana {
        max-width: 520px;
        margin: 10px auto 24px;
        border: 2px solid #8b0000;
        border-radius: 10px;
        background: #fafafa;
        box-shadow: 4px 4px 12px rgba(0, 0, 0, 0.15);
        padding: 0;
        overflow: hidden;
    }
    .rol-ventana-titulo {
        background: linear-gradient(180deg, #e8e8e8 0%, #d4d4d4 100%);
        border-bottom: 1px solid #999;
        padding: 8px 14px;
        font-weight: 900;
        font-size: 14px;
        text-align: center;
        color: #000;
    }
    .rol-ventana-cuerpo {
        padding: 20px 24px 24px;
    }
    .rol-botones {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-top: 8px;
    }
    .rol-boton {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 72px;
        padding: 12px 10px;
        font-family: Verdana, Arial, sans-serif;
        font-size: 15px;
        font-weight: 900;
        text-align: center;
        border: 2px solid #bcbcbc;
        border-radius: 6px;
        background: #e8e8e8;
        color: #000;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s;
    }
    .rol-boton:hover:not(:disabled) {
        background: #d0d0d0;
        border-color: #888;
    }
    .rol-boton--activo {
        background: #8fc4cb;
        border-color: #2c4760;
        box-shadow: inset 0 0 0 1px #2c4760;
    }
    .rol-boton--activo::after {
        content: ' ✓';
    }
    .rol-boton--disabled {
        opacity: 0.45;
        cursor: not-allowed;
        pointer-events: none;
    }
    .rol-activo-badge {
        text-align: center;
        margin-bottom: 12px;
        font-size: 13px;
        color: #2c4760;
        font-weight: bold;
    }
</style>

<div style="font-family: Verdana, Arial, sans-serif;">

    <div class="rol-ventana">
        <div class="rol-ventana-titulo">Simular acceso por rol</div>
        <div class="rol-ventana-cuerpo">
            @if ($activeRoleLabel)
                <p class="rol-activo-badge">Accediendo como: {{ $activeRoleLabel }}</p>
            @else
                <p class="rol-activo-badge" style="color: #856404;">Elija un rol para simular su acceso en el sistema</p>
            @endif

            <p style="font-size: 12px; color: #555; text-align: center; margin: 0 0 4px;">
                El sistema mostrará menús y permisos como si usted fuera ese rol.
            </p>

            <div class="rol-botones">
                @foreach ($roleButtons as $btn)
                    <a href="{{ route('simular-rol', ['moduleKey' => $btn['key']]) }}"
                        class="rol-boton {{ $btn['active'] ? 'rol-boton--activo' : '' }} {{ !$btn['enabled'] ? 'rol-boton--disabled' : '' }}"
                        @if(! $btn['enabled'])
                            onclick="return false;"
                            title="Rol no disponible"
                        @else
                            title="Acceder como {{ $btn['label'] }}"
                        @endif
                    >
                        {{ $btn['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>