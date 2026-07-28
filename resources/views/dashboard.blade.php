@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-3"><img src="{{ asset('images/logo.jpg') }}" alt="Logo de Farmacia Académica" width="64" height="64" class="rounded-circle shadow-sm app-logo"><div><div class="page-kicker">Resumen operativo</div><h1 class="page-title mb-1">Dashboard</h1>
            <p class="text-secondary mb-0">Resumen de la farmacia y alertas de inventario.</p>
            </div></div>
        </div>
        <span class="badge text-bg-secondary">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</span>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            ['label' => 'Medicamentos activos', 'value' => $medicamentosActivos, 'color' => 'primary'],
            ['label' => 'Categorías activas', 'value' => $categoriasActivas, 'color' => 'success'],
            ['label' => 'Proveedores activos', 'value' => $proveedoresActivos, 'color' => 'info'],
            ['label' => 'Ventas de hoy', 'value' => $ventasHoy, 'color' => 'dark'],
        ] as $indicador)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card border-0 h-100" style="--stat-color: var(--pharmacy-{{ $indicador['color'] === 'primary' ? 'blue' : ($indicador['color'] === 'success' ? 'mint' : ($indicador['color'] === 'info' ? 'blue' : 'gold')) }});">
                    <div class="card-body">
                        <div class="stat-label">{{ $indicador['label'] }}</div>
                        <div class="stat-value mt-3">{{ $indicador['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0">
        <div class="card-header bg-white"><h2 class="h5 mb-0">Alertas de inventario</h2></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><div class="alert alert-danger mb-0"><strong>{{ $vencidos }}</strong> medicamento(s) vencido(s)</div></div>
                <div class="col-md-4"><div class="alert alert-warning mb-0"><strong>{{ $porVencer }}</strong> vence(n) en los próximos 30 días</div></div>
                <div class="col-md-4"><div class="alert alert-info mb-0"><strong>{{ $stockBajo }}</strong> con stock bajo (≤ {{ config('farmacia.stock_bajo') }})</div></div>
            </div>
        </div>
    </div>
@endsection
