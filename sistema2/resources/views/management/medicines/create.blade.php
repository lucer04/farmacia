@extends('template.administrador')

@section('title', 'NUEVO MEDICAMENTO')
@section('description', 'Registrar medicamento')
@section('item-breadcrumb', 'Nuevo')
@section('section-breadcrumb', 'Medicamentos')
@section('module-breadcrumb', 'Gestión')

@section('content')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-body">
            @include('partials.flash')
            <form action="{{ route('medicines.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('management.medicines._form')
            </form>
        </div>
    </div>
@endsection
