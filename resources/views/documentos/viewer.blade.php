@extends('layouts.app')

@section('title', 'Visor de documento')
@section('header', 'Visor de documento')

@section('content')
<style>
    .watermark-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0.15;
    }
    .watermark-overlay img {
        width: 50%;
        max-width: 400px;
        height: auto;
    }
    .pdf-container {
        width: 100%;
        height: calc(100vh - 140px);
        position: relative;
    }
    .pdf-container embed,
    .pdf-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
</style>

<div style="background:#f5f5f5; padding:10px; margin-bottom:10px; border:1px solid #ccc; font-size:12px;">
    <b>Documento:</b> {{ $doc->componente?->nombre ?? 'Sin nombre' }} |
    <b>Proyecto:</b> {{ $doc->proyecto->titulo ?? 'N/A' }} |
    <a href="{{ route('proyectos.buscar') }}" style="color:#0000EE;">&larr; Volver a explorador</a>
</div>

<div class="pdf-container">
    <embed src="{{ $docUrl }}#toolbar=1&navpanes=0" type="application/pdf">
</div>

<div class="watermark-overlay">
    <img src="{{ asset('imagenes/uptp-logo.png') }}" alt="UPTP">
</div>
@endsection
