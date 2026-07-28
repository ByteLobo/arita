@extends('layouts.app')

@section('title', $modo . ' rol')

@section('content')
<div class="row justify-content-center"><div class="col-lg-6"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h1 class="h3 mb-4">{{ $modo }} rol</h1><form method="POST" action="{{ $rol->exists ? route('admin.roles.update', $rol) : route('admin.roles.store') }}">@csrf @if($rol->exists) @method('PUT') @endif<div class="mb-4"><label for="nombre" class="form-label">Nombre del rol</label><input id="nombre" name="nombre" value="{{ old('nombre', $rol->nombre) }}" class="form-control @error('nombre') is-invalid @enderror" required maxlength="50">@error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancelar</a> <button class="btn btn-primary">Guardar</button></form></div></div></div></div>
@endsection
