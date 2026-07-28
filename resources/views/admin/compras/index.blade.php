@extends('layouts.app')

@section('title', 'Compras')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Historial de compras</h1><a href="{{ route('admin.compras.create') }}" class="btn btn-primary">Registrar compra</a></div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Fecha</th><th>Proveedor</th><th>Usuario</th><th>Total</th><th class="text-end">Detalle</th></tr></thead><tbody>
@forelse($compras as $compra)<tr><td>{{ $compra->fecha->format('d/m/Y H:i') }}</td><td>{{ $compra->proveedor->nombre_proveedor }}</td><td>{{ $compra->usuario->nombre }}</td><td>{{ number_format((float) $compra->total, 2) }}</td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.compras.show', $compra) }}">Ver detalle</a></td></tr>@empty<tr><td colspan="5" class="text-center text-secondary py-4">No hay compras registradas.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $compras->links() }}</div></div>
@endsection
