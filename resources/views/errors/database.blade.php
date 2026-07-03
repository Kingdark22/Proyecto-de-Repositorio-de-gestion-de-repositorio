@extends('layouts.app')

@section('title', 'Error de conexión')
@section('header', 'Error de conexión')

@section('content')
<div style="font-size: 13px; text-align: center; padding: 40px;">
    <p style="font-size: 16px; color: #8b0000;">No se pudo establecer conexión con la base de datos.</p>
    <p>Por favor, intente nuevamente más tarde. Si el problema persiste, contacte al administrador del sistema.</p>
</div>
@endsection