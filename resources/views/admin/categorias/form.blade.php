@extends('layouts.app')

@section('title', $modo . ' categoría')

@section('content')
<div class="row justify-content-center"><div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h1 class="h3 mb-4">{{ $modo }} categoría</h1><form method="POST" action="{{ $categoria->exists ? route('admin.categorias.update', $categoria) : route('admin.categorias.store') }}">@csrf @if($categoria->exists) @method('PUT') @endif
<div class="mb-3"><label for="nombre" class="form-label">Nombre</label><input id="nombre" name="nombre" value="{{ old('nombre', $categoria->nombre) }}" class="form-control @error('nombre') is-invalid @enderror" required maxlength="120">@error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-4"><label for="descripcion" class="form-label">Descripción</label><textarea id="descripcion" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="4">{{ old('descripcion', $categoria->descripcion) }}</textarea>@error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<a href="{{ route('admin.categorias.index') }}" class="btn btn-outline-secondary">Cancelar</a> <button class="btn btn-primary">Guardar</button></form></div></div></div></div>
@endsection
