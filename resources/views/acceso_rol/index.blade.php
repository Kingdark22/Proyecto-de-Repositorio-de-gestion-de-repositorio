@extends('layouts.app')

@section('title', 'Simular acceso por rol')
@section('header', 'Simular acceso por rol')

@section('content')
    <x-role-switcher :roleButtons="$roleButtons" :activeRoleLabel="$activeRoleLabel" />
@endsection
