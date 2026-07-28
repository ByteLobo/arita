<?php

namespace App\Services;

use App\Exceptions\CompraException;
use App\Models\Compra;
use App\Models\Medicamento;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class CompraService
{
    /**
     * @param  array{id_proveedor:int, detalles:array<int, array{id_medicamento:int, cantidad:int, precio_compra:numeric-string|float|int}>}  $datos
     */
    public function registrar(array $datos, Usuario $usuario): Compra
    {
        return DB::transaction(function () use ($datos, $usuario): Compra {
            $proveedor = Proveedor::query()->lockForUpdate()->find($datos['id_proveedor']);
            if (! $proveedor) {
                throw new CompraException('Proveedor inexistente.');
            }
            if (! $proveedor->activo) {
                throw new CompraException('El proveedor está inactivo.');
            }

            $detalles = collect($datos['detalles'])->map(function (array $detalle): array {
                return [
                    'id_medicamento' => (int) $detalle['id_medicamento'],
                    'cantidad' => (int) $detalle['cantidad'],
                    'precio_compra' => round((float) $detalle['precio_compra'], 2),
                ];
            })->sortBy('id_medicamento')->values();

            if ($detalles->isEmpty()) {
                throw new CompraException('La compra debe contener al menos un medicamento.');
            }
            if ($detalles->pluck('id_medicamento')->duplicates()->isNotEmpty()) {
                throw new CompraException('No se permiten medicamentos duplicados en una misma compra.');
            }

            $compra = Compra::query()->create([
                'id_proveedor' => $proveedor->id_proveedor,
                'id_usuario' => $usuario->id_usuario,
                'fecha' => now(),
                'total' => 0,
            ]);
            $total = 0.0;

            foreach ($detalles as $detalle) {
                if ($detalle['cantidad'] <= 0) {
                    throw new CompraException('La cantidad debe ser mayor que cero.');
                }
                if ($detalle['precio_compra'] < 0) {
                    throw new CompraException('El precio de compra no puede ser negativo.');
                }

                $medicamento = Medicamento::query()->lockForUpdate()->find($detalle['id_medicamento']);
                if (! $medicamento) {
                    throw new CompraException('Medicamento inexistente.');
                }
                if (! $medicamento->activo) {
                    throw new CompraException("El medicamento {$medicamento->nombre} está inactivo.");
                }

                $subtotal = round($detalle['cantidad'] * $detalle['precio_compra'], 2);
                $compra->detalles()->create([
                    'id_medicamento' => $medicamento->id_medicamento,
                    'cantidad' => $detalle['cantidad'],
                    'precio_compra' => $detalle['precio_compra'],
                    'subtotal' => $subtotal,
                ]);

                $medicamento->stock += $detalle['cantidad'];
                $medicamento->precio_compra = $detalle['precio_compra'];
                $medicamento->save();
                $total += $subtotal;
            }

            $compra->update(['total' => round($total, 2)]);

            return $compra->load(['proveedor', 'usuario', 'detalles.medicamento']);
        });
    }
}
