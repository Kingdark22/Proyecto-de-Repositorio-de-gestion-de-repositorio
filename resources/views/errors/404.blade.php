@extends('layouts.app')

@section('title', 'Página no encontrada')

@section('content')
<div style="text-align: center; padding: 60px 20px;">
    <h1 style="font-size: 72px; color: #8b0000; margin: 0; font-weight: bold;">404</h1>
    <h2 style="color: #333; margin-top: 10px;">Página no encontrada</h2>
    <p style="color: #666; margin: 20px 0;">El recurso solicitado no existe o ha sido eliminado.</p>
    <a href="{{ url('/dashboard') }}" class="cm-btn cm-btn-primary">Volver al inicio</a>
</div>
@endsection
