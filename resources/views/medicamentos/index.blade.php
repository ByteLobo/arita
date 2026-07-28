@extends('layouts.app')

@section('title', 'Consulta de medicamentos')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h1 class="h3 mb-0">Medicamentos y stock</h1><form class="d-flex" method="GET"><input name="buscar" value="{{ request('buscar') }}" class="form-control me-2" placeholder="Buscar medicamento o categoría"><button class="btn btn-outline-primary">Buscar</button></form></div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Medicamento</th><th>Categoría</th><th>Precio venta</th><th>Stock</th><th>Vencimiento</th></tr></thead><tbody>
@forelse($medicamentos as $medicamento)<tr><td>{{ $medicamento->nombre }}</td><td>{{ $medicamento->categoria->nombre }}</td><td>{{ number_format((float) $medicamento->precio_venta, 2) }}</td><td><span class="badge text-bg-{{ $medicamento->stock <= config('farmacia.stock_bajo') ? 'warning' : 'success' }}">{{ $medicamento->stock }}</span></td><td>{{ $medicamento->fecha_vencimiento->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="5" class="text-center text-secondary py-4">No se encontraron medicamentos activos.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $medicamentos->links() }}</div></div>
@endsection
