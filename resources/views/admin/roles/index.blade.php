@extends('layouts.app')

@section('title', 'Roles')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Roles</h1><a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Nuevo rol</a></div>
<div class="alert alert-info">Los roles personalizados se pueden registrar, pero los permisos operativos actuales están definidos para los roles Administrador y Vendedor.</div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Rol</th><th>Usuarios</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>@forelse($roles as $rol)<tr><td>{{ $rol->nombre }}</td><td>{{ $rol->usuarios_count }}</td><td><span class="badge text-bg-{{ $rol->activo ? 'success' : 'secondary' }}">{{ $rol->activo ? 'Activo' : 'Inactivo' }}</span></td><td class="text-end"><a href="{{ route('admin.roles.edit', $rol) }}" class="btn btn-sm btn-outline-primary">Editar</a>@if($rol->activo && $rol->usuarios_count === 0)<form class="d-inline" method="POST" action="{{ route('admin.roles.destroy', $rol) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Desactivar este rol?')">Desactivar</button></form>@endif</td></tr>@empty<tr><td colspan="4" class="text-center text-secondary py-4">No hay roles registrados.</td></tr>@endforelse</tbody></table></div><div class="card-footer bg-white">{{ $roles->links() }}</div></div>
@endsection
