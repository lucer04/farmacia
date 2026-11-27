@extends('template.administrador')

@section('title', 'EDITAR VENTA')
@section('description', 'Modificar los datos y productos de la venta')
@section('item-breadcrumb', 'Editar')
@section('section-breadcrumb', 'Ventas')
@section('module-breadcrumb', 'Gestión')

@section('content')
    @include('sales._form')
@endsection
