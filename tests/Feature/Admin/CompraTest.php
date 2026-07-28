<?php

namespace Tests\Feature\Admin;

use App\Exceptions\CompraException;
use App\Models\Compra;
use App\Models\Medicamento;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Services\CompraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompraTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Usuario
    {
        return Usuario::query()->where('email', 'admin@farmacia.test')->firstOrFail();
    }

    private function vendedor(): Usuario
    {
        return Usuario::query()->where('email', 'vendedor@farmacia.test')->firstOrFail();
    }

    public function test_compra_aumenta_stock_actualiza_precio_y_calcula_total(): void
    {
        $this->seed();
        $admin = $this->admin();
        $proveedor = Proveedor::query()->where('activo', true)->firstOrFail();
        $medicamentos = Medicamento::query()->where('activo', true)->take(2)->get();
        $stockInicial = $medicamentos->pluck('stock', 'id_medicamento');

        $compra = app(CompraService::class)->registrar([
            'id_proveedor' => $proveedor->id_proveedor,
            'detalles' => [
                ['id_medicamento' => $medicamentos[0]->id_medicamento, 'cantidad' => 10, 'precio_compra' => 1.25],
                ['id_medicamento' => $medicamentos[1]->id_medicamento, 'cantidad' => 4, 'precio_compra' => 2.50],
            ],
        ], $admin);

        $this->assertSame('22.50', $compra->total);
        $this->assertCount(2, $compra->detalles);
        $this->assertSame($stockInicial[$medicamentos[0]->id_medicamento] + 10, $medicamentos[0]->fresh()->stock);
        $this->assertSame($stockInicial[$medicamentos[1]->id_medicamento] + 4, $medicamentos[1]->fresh()->stock);
        $this->assertSame('1.25', $medicamentos[0]->fresh()->precio_compra);
        $this->assertDatabaseHas('detalle_compras', ['id_compra' => $compra->id_compra, 'subtotal' => 12.50]);
    }

    public function test_detalle_inexistente_hace_rollback_completo(): void
    {
        $this->seed();
        $admin = $this->admin();
        $proveedor = Proveedor::query()->firstOrFail();
        $medicamento = Medicamento::query()->firstOrFail();
        $stockInicial = $medicamento->stock;

        $this->expectException(CompraException::class);

        try {
            app(CompraService::class)->registrar([
                'id_proveedor' => $proveedor->id_proveedor,
                'detalles' => [
                    ['id_medicamento' => $medicamento->id_medicamento, 'cantidad' => 10, 'precio_compra' => 1],
                    ['id_medicamento' => 999999, 'cantidad' => 2, 'precio_compra' => 5],
                ],
            ], $admin);
        } finally {
            $this->assertSame(0, Compra::query()->count());
            $this->assertSame($stockInicial, $medicamento->fresh()->stock);
        }
    }

    public function test_medicamento_inactivo_hace_rollback_completo(): void
    {
        $this->seed();
        $admin = $this->admin();
        $proveedor = Proveedor::query()->firstOrFail();
        $medicamento = Medicamento::query()->firstOrFail();
        $medicamento->update(['activo' => false]);

        $this->expectExceptionMessage('está inactivo');

        try {
            app(CompraService::class)->registrar([
                'id_proveedor' => $proveedor->id_proveedor,
                'detalles' => [['id_medicamento' => $medicamento->id_medicamento, 'cantidad' => 1, 'precio_compra' => 1]],
            ], $admin);
        } finally {
            $this->assertSame(0, Compra::query()->count());
        }
    }

    public function test_cantidad_invalida_hace_rollback_completo(): void
    {
        $this->seed();
        $admin = $this->admin();
        $proveedor = Proveedor::query()->firstOrFail();
        $medicamento = Medicamento::query()->firstOrFail();

        $this->expectExceptionMessage('mayor que cero');

        try {
            app(CompraService::class)->registrar([
                'id_proveedor' => $proveedor->id_proveedor,
                'detalles' => [['id_medicamento' => $medicamento->id_medicamento, 'cantidad' => 0, 'precio_compra' => 1]],
            ], $admin);
        } finally {
            $this->assertSame(0, Compra::query()->count());
        }
    }

    public function test_formulario_rechaza_medicamentos_duplicados(): void
    {
        $this->seed();
        $admin = $this->admin();
        $proveedor = Proveedor::query()->firstOrFail();
        $medicamento = Medicamento::query()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.compras.store'), [
            'id_proveedor' => $proveedor->id_proveedor,
            'detalles' => [
                ['id_medicamento' => $medicamento->id_medicamento, 'cantidad' => 1, 'precio_compra' => 1],
                ['id_medicamento' => $medicamento->id_medicamento, 'cantidad' => 2, 'precio_compra' => 1],
            ],
        ])->assertSessionHasErrors('detalles');

        $this->assertSame(0, Compra::query()->count());
    }

    public function test_vendedor_no_puede_registrar_compras(): void
    {
        $this->seed();

        $this->actingAs($this->vendedor())->get(route('admin.compras.index'))->assertForbidden();
        $this->actingAs($this->vendedor())->get(route('admin.compras.create'))->assertForbidden();
    }
}
