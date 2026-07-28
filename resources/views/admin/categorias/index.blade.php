@extends('layouts.app')

@section('title', 'Categorías')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Categorías</h1><a href="{{ route('admin.categorias.create') }}" class="btn btn-primary">Nueva categoría</a></div>
<form class="row g-2 mb-3" method="GET"><div class="col-md-6"><input name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Buscar categoría"></div><div class="col-md-4"><select name="estado" class="form-select"><option value="">Todos los estados</option><option value="activas" @selected(request('estado') === 'activas')>Activas</option><option value="inactivas" @selected(request('estado') === 'inactivas')>Inactivas</option></select></div><div class="col-md-2 d-grid"><button class="btn btn-outline-primary">Filtrar</button></div></form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Nombre</th><th>Descripción</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>
@forelse ($categorias as $categoria)<tr><td>{{ $categoria->nombre }}</td><td>{{ $categoria->descripcion ?: '—' }}</td><td><span class="badge text-bg-{{ $categoria->activo ? 'success' : 'secondary' }}">{{ $categoria->activo ? 'Activa' : 'Inactiva' }}</span></td><td class="text-end"><a href="{{ route('admin.categorias.edit', $categoria) }}" class="btn btn-sm btn-outline-primary">Editar</a>@if ($categoria->activo)<form class="d-inline" method="POST" action="{{ route('admin.categorias.destroy', $categoria) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Desactivar esta categoría?')">Desactivar</button></form>@endif</td></tr>@empty<tr><td colspan="4" class="text-center text-secondary py-4">No hay categorías registradas.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $categorias->links() }}</div></div>
@endsection
