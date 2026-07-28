@extends('layouts.app')

@section('title', 'Administración')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4"><div class="d-flex align-items-center gap-3"><img src="{{ asset('images/logo.jpg') }}" alt="Logo de Farmacia Académica" width="64" height="64" class="rounded-circle shadow-sm object-fit-cover"><div><h1 class="h3 mb-1">Módulos administrativos</h1><p class="text-secondary mb-0">Gestiona catálogos, compras e inventario.</p></div></div><a href="{{ route('admin.compras.create') }}" class="btn btn-primary">Registrar compra</a></div>
<div class="row g-3">
    @foreach ([['Categorías', 'admin.categorias.index', 'Organiza los medicamentos por grupos.'], ['Medicamentos', 'admin.medicamentos.index', 'Administra precios, stock y vencimientos.'], ['Proveedores', 'admin.proveedores.index', 'Mantén actualizados tus proveedores.'], ['Compras', 'admin.compras.index', 'Consulta ingresos de mercadería y stock.'], ['Usuarios', 'admin.usuarios.index', 'Administra accesos y estados de usuarios.'], ['Roles', 'admin.roles.index', 'Configura los roles disponibles.']] as [$titulo, $ruta, $descripcion])
        <div class="col-12 col-md-6"><div class="card module-card border-0 h-100"><div class="card-body"><div class="page-kicker mb-2">Módulo</div><h2 class="h5">{{ $titulo }}</h2><p class="text-secondary">{{ $descripcion }}</p><a href="{{ route($ruta) }}" class="btn btn-outline-primary">Abrir módulo</a></div></div></div>
    @endforeach
</div>
@endsection
