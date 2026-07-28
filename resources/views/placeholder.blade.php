@extends('layouts.app')

@section('title', $titulo)

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h1 class="h3">{{ $titulo }}</h1>
            @if (request()->routeIs('admin.panel'))
                <p class="text-secondary">Gestiona los catálogos base del sistema.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-primary" href="{{ route('admin.categorias.index') }}">Categorías</a>
                    <a class="btn btn-outline-primary" href="{{ route('admin.medicamentos.index') }}">Medicamentos</a>
                    <a class="btn btn-outline-primary" href="{{ route('admin.proveedores.index') }}">Proveedores</a>
                    <a class="btn btn-outline-primary" href="{{ route('admin.compras.index') }}">Compras</a>
                </div>
            @else
                <p class="text-secondary mb-0">Las compras y ventas se implementarán en las siguientes fases.</p>
            @endif
        </div>
    </div>
@endsection
