@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Historial de ventas</h1><a href="{{ route('ventas.create') }}" class="btn btn-primary">Registrar venta</a></div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Fecha</th><th>Usuario</th><th>Total</th><th class="text-end">Detalle</th></tr></thead><tbody>
@forelse($ventas as $venta)<tr><td>{{ $venta->fecha->format('d/m/Y H:i') }}</td><td>{{ $venta->usuario->nombre }}</td><td>{{ number_format((float) $venta->total, 2) }}</td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('ventas.show', $venta) }}">Ver detalle</a></td></tr>@empty<tr><td colspan="4" class="text-center text-secondary py-4">No hay ventas registradas.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $ventas->links() }}</div></div>
@endsection
