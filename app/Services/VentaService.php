<?php

namespace App\Services;

use App\Exceptions\VentaException;
use App\Models\Medicamento;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class VentaService
{
    /**
     * @param  array{detalles:array<int, array{id_medicamento:int, cantidad:int}>}  $datos
     */
    public function registrar(array $datos, Usuario $usuario): Venta
    {
        return DB::transaction(function () use ($datos, $usuario): Venta {
            $detalles = collect($datos['detalles'])->map(function (array $detalle): array {
                return [
                    'id_medicamento' => (int) $detalle['id_medicamento'],
                    'cantidad' => (int) $detalle['cantidad'],
                ];
            })->sortBy('id_medicamento')->values();

            if ($detalles->isEmpty()) {
                throw new VentaException('La venta debe contener al menos un medicamento.');
            }
            if ($detalles->pluck('id_medicamento')->duplicates()->isNotEmpty()) {
                throw new VentaException('No se permiten medicamentos duplicados en una misma venta.');
            }

            $venta = Venta::query()->create([
                'id_usuario' => $usuario->id_usuario,
                'fecha' => now(),
                'total' => 0,
            ]);
            $total = 0.0;

            foreach ($detalles as $detalle) {
                if ($detalle['cantidad'] <= 0) {
                    throw new VentaException('La cantidad debe ser mayor que cero.');
                }

                $medicamento = Medicamento::query()->lockForUpdate()->find($detalle['id_medicamento']);
                if (! $medicamento) {
                    throw new VentaException('Medicamento inexistente.');
                }
                if (! $medicamento->activo) {
                    throw new VentaException("El medicamento {$medicamento->nombre} está inactivo.");
                }
                if ($medicamento->fecha_vencimiento->isBefore(today())) {
                    throw new VentaException("El medicamento {$medicamento->nombre} está vencido.");
                }
                if ($medicamento->stock < $detalle['cantidad']) {
                    throw new VentaException("Stock insuficiente para {$medicamento->nombre}.");
                }

                $precio = round((float) $medicamento->precio_venta, 2);
                $subtotal = round($detalle['cantidad'] * $precio, 2);
                $venta->detalles()->create([
                    'id_medicamento' => $medicamento->id_medicamento,
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                ]);

                $medicamento->decrement('stock', $detalle['cantidad']);
                $total += $subtotal;
            }

            $venta->update(['total' => round($total, 2)]);

            return $venta->load(['usuario', 'detalles.medicamento']);
        });
    }
}
