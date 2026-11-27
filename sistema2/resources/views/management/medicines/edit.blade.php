@extends('template.administrador')

@section('title', 'EDITAR MEDICAMENTO')
@section('description', 'Actualizar medicamento')
@section('item-breadcrumb', 'Actualizar')
@section('section-breadcrumb', 'Medicamentos')
@section('module-breadcrumb', 'Gestión')

@section('content')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <div class="card-body">
            @include('partials.flash')
            <form action="{{ route('medicines.update', $medicine) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('management.medicines._form')
            </form>
        </div>
    </div>
@endsection
