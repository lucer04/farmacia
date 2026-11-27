@extends('template.administrador')

@section('title', 'NUEVA VENTA')
@section('description', 'Registrar venta y sus medicamentos')
@section('item-breadcrumb', 'Nueva')
@section('section-breadcrumb', 'Ventas')
@section('module-breadcrumb', 'Gestión')

@section('content')
    @include('sales._form')
@endsection
