@extends('layouts.app')

@section('title', $modo . ' proveedor')

@section('content')
<div class="row justify-content-center"><div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h1 class="h3 mb-4">{{ $modo }} proveedor</h1><form method="POST" action="{{ $proveedor->exists ? route('admin.proveedores.update', $proveedor) : route('admin.proveedores.store') }}">@csrf @if($proveedor->exists) @method('PUT') @endif
<div class="mb-3"><label for="nombre_proveedor" class="form-label">Nombre del proveedor</label><input id="nombre_proveedor" name="nombre_proveedor" value="{{ old('nombre_proveedor', $proveedor->nombre_proveedor) }}" class="form-control @error('nombre_proveedor') is-invalid @enderror" required maxlength="160">@error('nombre_proveedor')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="row"><div class="col-md-6 mb-3"><label for="telefono" class="form-label">Teléfono</label><input id="telefono" name="telefono" value="{{ old('telefono', $proveedor->telefono) }}" class="form-control @error('telefono') is-invalid @enderror" maxlength="30">@error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6 mb-4"><label for="nit_ci" class="form-label">NIT/CI</label><input id="nit_ci" name="nit_ci" value="{{ old('nit_ci', $proveedor->nit_ci) }}" class="form-control @error('nit_ci') is-invalid @enderror" maxlength="40">@error('nit_ci')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
<a href="{{ route('admin.proveedores.index') }}" class="btn btn-outline-secondary">Cancelar</a> <button class="btn btn-primary">Guardar</button></form></div></div></div></div>
@endsection
