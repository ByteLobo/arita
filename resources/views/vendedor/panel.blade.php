@extends('layouts.app')

@section('title', 'Operaciones')

@section('content')
<div class="mb-4 d-flex align-items-center gap-3"><img src="{{ asset('images/logo.jpg') }}" alt="Logo de Farmacia Académica" width="64" height="64" class="rounded-circle shadow-sm object-fit-cover"><div><h1 class="h3 mb-1">Operaciones de vendedor</h1><p class="text-secondary mb-0">Consulta disponibilidad y registra ventas.</p></div></div>
<div class="row g-3"><div class="col-12 col-md-6"><div class="card module-card border-0 h-100"><div class="card-body"><div class="page-kicker mb-2">Consulta</div><h2 class="h5">Medicamentos</h2><p class="text-secondary">Consulta precios, stock y fechas de vencimiento.</p><a href="{{ route('medicamentos.index') }}" class="btn btn-outline-primary">Consultar catálogo</a></div></div></div><div class="col-12 col-md-6"><div class="card module-card border-0 h-100"><div class="card-body"><div class="page-kicker mb-2">Operación</div><h2 class="h5">Ventas</h2><p class="text-secondary">Registra una venta o revisa el historial.</p><a href="{{ route('ventas.create') }}" class="btn btn-primary me-2">Nueva venta</a><a href="{{ route('ventas.index') }}" class="btn btn-outline-primary">Historial</a></div></div></div></div>
@endsection
