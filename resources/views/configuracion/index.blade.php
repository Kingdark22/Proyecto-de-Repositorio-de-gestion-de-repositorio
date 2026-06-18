@extends('layouts.app')

@section('title', 'Configuración')
@section('header', 'Configuración')

@section('content')
<div style="font-size: 13px;">
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Perfil de usuario</legend>
        
        @php $user = auth()->user(); @endphp
        <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 13px;">
            <tr>
                <td width="30%"><b>Cédula:</b></td>
                <td>{{ $user->usu_cedula ?? '—' }}</td>
            </tr>
            <tr>
                <td><b>Nombre:</b></td>
                <td>{{ $user->usu_nombre ?? '—' }}</td>
            </tr>
            <tr>
                <td><b>Rol activo:</b></td>
                <td>{{ app(\App\Services\UserRoleService::class)->activeRoleLabel($user) ?? 'Sin rol' }}</td>
            </tr>
        </table>

        <div style="margin-top: 20px; text-align: center;">
            <a href="{{ route('acceso-rol.index') }}" class="cm-btn cm-btn-primary" style="display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; padding: 0.55rem 0.95rem; font-size: 0.92rem; font-weight: 600; border: 1px solid transparent; cursor: pointer; text-decoration: none; background: #19692e; border-color: #154f26; color: #fff;">
                Cambiar rol
            </a>
        </div>
    </fieldset>
</div>
@endsection
