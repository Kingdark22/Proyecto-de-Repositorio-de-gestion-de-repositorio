@extends('layouts.app')

@section('title', 'Gestión de Proyectos')
@section('header', 'Gestión de Proyectos')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/proyectos-gestion.css') }}">
@endpush

@section('content')
    <livewire:proyecto-manager />
@endsection
