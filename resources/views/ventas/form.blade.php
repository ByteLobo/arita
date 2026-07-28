@extends('layouts.app')

@section('title', 'Registrar venta')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Registrar venta</h1><a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">Volver</a></div>
<form method="POST" action="{{ route('ventas.store') }}" class="card border-0 shadow-sm"><div class="card-body">
    @csrf
    <div id="detalles">
        <div class="row g-2 align-items-end detalle-item mb-3"><div class="col-md-8"><label class="form-label">Medicamento</label><select name="detalles[0][id_medicamento]" class="form-select" required><option value="">Seleccionar...</option>@foreach($medicamentos as $medicamento)<option value="{{ $medicamento->id_medicamento }}">{{ $medicamento->nombre }} (stock: {{ $medicamento->stock }}, Bs {{ number_format((float) $medicamento->precio_venta, 2) }})</option>@endforeach</select></div><div class="col-md-3"><label class="form-label">Cantidad</label><input name="detalles[0][cantidad]" type="number" min="1" class="form-control" required></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger quitarDetalle" disabled>Quitar</button></div></div>
    </div>
    <button type="button" id="agregarDetalle" class="btn btn-outline-primary">Agregar medicamento</button>
</div><div class="card-footer bg-white text-end"><button class="btn btn-primary">Registrar venta</button></div></form>
<template id="detalleTemplate"><div class="row g-2 align-items-end detalle-item mb-3"><div class="col-md-8"><label class="form-label">Medicamento</label><select name="detalles[__INDEX__][id_medicamento]" class="form-select" required><option value="">Seleccionar...</option>@foreach($medicamentos as $medicamento)<option value="{{ $medicamento->id_medicamento }}">{{ $medicamento->nombre }} (stock: {{ $medicamento->stock }}, Bs {{ number_format((float) $medicamento->precio_venta, 2) }})</option>@endforeach</select></div><div class="col-md-3"><label class="form-label">Cantidad</label><input name="detalles[__INDEX__][cantidad]" type="number" min="1" class="form-control" required></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger quitarDetalle">Quitar</button></div></div></template>
@push('scripts')<script>
let indice = 1;
const detalles = document.getElementById('detalles');
document.getElementById('agregarDetalle').addEventListener('click', () => {
    detalles.insertAdjacentHTML('beforeend', document.getElementById('detalleTemplate').innerHTML.replaceAll('__INDEX__', indice++));
    actualizarBotones();
});
detalles.addEventListener('click', (event) => {
    if (event.target.classList.contains('quitarDetalle')) {
        event.target.closest('.detalle-item').remove();
        actualizarBotones();
    }
});
function actualizarBotones() {
    const botones = detalles.querySelectorAll('.quitarDetalle');
    botones.forEach((boton) => { boton.disabled = botones.length === 1; });
}
</script>@endpush
@endsection
