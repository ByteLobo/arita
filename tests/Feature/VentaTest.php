<?php

namespace Tests\Feature;

use App\Exceptions\VentaException;
use App\Models\Medicamento;
use App\Models\Usuario;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(): Usuario
    {
        return Usuario::query()->where('email', 'vendedor@farmacia.test')->firstOrFail();
    }

    public function test_venta_descuenta_stock_y_conserva_precio_historico(): void
    {
        $this->seed();
        $usuario = $this->usuario();
        $medicamento = Medicamento::query()->where('activo', true)->whereDate('fecha_vencimiento', '>=', today())->firstOrFail();
        $stockInicial = $medicamento->stock;

        $venta = app(VentaService::class)->registrar([
            'detalles' => [['id_medicamento' => $medicamento->id_medicamento, 'cantidad' => 3]],
        ], $usuario);

        $this->assertSame(number_format((float) $medicamento->precio_venta * 3, 2, '.', ''), $venta->total);
        $this->assertSame($stockInicial - 3, $medicamento->fresh()->stock);
        $this->assertDatabaseHas('detalle_ventas', [
            'id_venta' => $venta->id_venta,
            'precio_unitario' => $medicamento->precio_venta,
            'subtotal' => number_format((float) $medicamento->precio_venta * 3, 2, '.', ''),
        ]);
    }

    public function test_no_se_puede_vender_sin_stock_y_hace_rollback(): void
    {
        $this->seed();
        $usuario = $this->usuario();
        $medicamento = Medicamento::query()->where('activo', true)->firstOrFail();
        $stockInicial = $medicamento->stock;

        $this->expectExceptionMessage('Stock insuficiente');

        try {
            app(VentaService::class)->registrar([
                'detalles' => [['id_medicamento' => $medicamento->id_medicamento, 'cantidad' => $stockInicial + 1]],
            ], $usuario);
        } finally {
            $this->assertSame(0, Venta::query()->count());
            $this->assertSame($stockInicial, $medicamento->fresh()->stock);
        }
    }

    public function test_no_se_puede_vender_medicamento_vencido(): void
    {
        $this->seed();
        $usuario = $this->usuario();
        $medicamento = Medicamento::query()->whereDate('fecha_vencimiento', '<', today())->firstOrFail();

        $this->expectException(VentaException::class);
        app(VentaService::class)->registrar([
            'detalles' => [['id_medicamento' => $medicamento->id_medicamento, 'cantidad' => 1]],
        ], $usuario);
    }

    public function test_vendedor_puede_registrar_venta(): void
    {
        $this->seed();
        $medicamento = Medicamento::query()->where('activo', true)->whereDate('fecha_vencimiento', '>=', today())->firstOrFail();

        $this->actingAs($this->usuario())->post(route('ventas.store'), [
            'detalles' => [['id_medicamento' => $medicamento->id_medicamento, 'cantidad' => 1]],
        ])->assertRedirect();

        $this->assertDatabaseCount('ventas', 1);
    }
}
