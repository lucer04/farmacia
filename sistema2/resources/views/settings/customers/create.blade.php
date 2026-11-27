@extends('template.administrador')

@section('title', 'NUEVO CLIENTE')
@section('description', 'Registrar cliente')
@section('item-breadcrumb', 'Nuevo')
@section('section-breadcrumb', 'Clientes')
@section('module-breadcrumb', 'Gestión')

@section('content')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-body">
            @include('partials.flash')
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                @include('settings.customers._form')
            </form>
        </div>
    </div>
@endsection
