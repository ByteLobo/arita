@extends('layouts.app')

@section('title', 'Detalle de venta')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="page-title mb-0">Venta #{{ $venta->id_venta }}</h1><div class="receipt-actions"><a href="{{ route('ventas.pdf', $venta) }}" class="btn btn-primary">Descargar comprobante PDF</a><a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">Volver</a></div></div>
<div class="card border-0 shadow-sm mb-3"><div class="card-body"><div class="row"><div class="col-md-4"><span class="text-secondary">Registrada por</span><br><strong>{{ $venta->usuario->nombre }}</strong></div><div class="col-md-4"><span class="text-secondary">Fecha</span><br><strong>{{ $venta->fecha->format('d/m/Y H:i') }}</strong></div></div></div></div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Medicamento</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr></thead><tbody>@foreach($venta->detalles as $detalle)<tr><td>{{ $detalle->medicamento->nombre }}</td><td>{{ $detalle->cantidad }}</td><td>{{ number_format((float) $detalle->precio_unitario, 2) }}</td><td>{{ number_format((float) $detalle->subtotal, 2) }}</td></tr>@endforeach</tbody><tfoot><tr><th colspan="3" class="text-end">Total</th><th>{{ number_format((float) $venta->total, 2) }}</th></tr></tfoot></table></div></div>
@endsection
