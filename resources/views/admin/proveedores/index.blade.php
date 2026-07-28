@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Proveedores</h1><a href="{{ route('admin.proveedores.create') }}" class="btn btn-primary">Nuevo proveedor</a></div>
<form class="row g-2 mb-3" method="GET"><div class="col-md-6"><input name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Buscar proveedor"></div><div class="col-md-4"><select name="estado" class="form-select"><option value="">Todos los estados</option><option value="activas" @selected(request('estado') === 'activas')>Activos</option><option value="inactivas" @selected(request('estado') === 'inactivas')>Inactivos</option></select></div><div class="col-md-2 d-grid"><button class="btn btn-outline-primary">Filtrar</button></div></form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Proveedor</th><th>Teléfono</th><th>NIT/CI</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>
@forelse ($proveedores as $proveedor)<tr><td>{{ $proveedor->nombre_proveedor }}</td><td>{{ $proveedor->telefono ?: '—' }}</td><td>{{ $proveedor->nit_ci ?: '—' }}</td><td><span class="badge text-bg-{{ $proveedor->activo ? 'success' : 'secondary' }}">{{ $proveedor->activo ? 'Activo' : 'Inactivo' }}</span></td><td class="text-end"><a href="{{ route('admin.proveedores.edit', $proveedor) }}" class="btn btn-sm btn-outline-primary">Editar</a>@if ($proveedor->activo)<form class="d-inline" method="POST" action="{{ route('admin.proveedores.destroy', $proveedor) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Desactivar este proveedor?')">Desactivar</button></form>@endif</td></tr>@empty<tr><td colspan="5" class="text-center text-secondary py-4">No hay proveedores registrados.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $proveedores->links() }}</div></div>
@endsection
