@extends('template.administrador')

@section('title', 'EDITAR CLIENTE')
@section('description', 'Actualizar cliente')
@section('item-breadcrumb', 'Actualizar')
@section('section-breadcrumb', 'Clientes')
@section('module-breadcrumb', 'Gestión')

@section('content')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-body">
            @include('partials.flash')
            <form action="{{ route('customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')
                @include('settings.customers._form')
            </form>
        </div>
    </div>
@endsection
