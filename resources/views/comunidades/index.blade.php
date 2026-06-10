@extends('layouts.app')

@section('title', 'Gestión de Comunidades')
@section('header', 'Gestión de Comunidades')

@section('content')
    <div style="background-color: #FFFFFF; border: 1px solid #CCC; margin: 10px; padding: 15px;">
        <livewire:comunidad-manager />
    </div>
@endsection
