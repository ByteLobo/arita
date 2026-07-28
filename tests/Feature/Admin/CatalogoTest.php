<?php

namespace Tests\Feature\Admin;

use App\Models\Categoria;
use App\Models\Medicamento;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoTest extends TestCase
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

    public function test_administrador_puede_crear_categoria(): void
    {
        $this->seed();

        $this->actingAs($this->admin())->post(route('admin.categorias.store'), [
            'nombre' => 'Dermatología',
            'descripcion' => 'Productos de cuidado de la piel.',
        ])->assertRedirectToRoute('admin.categorias.index');

        $this->assertDatabaseHas('categorias', ['nombre' => 'Dermatología', 'activo' => true]);
    }

    public function test_categoria_rechaza_nombre_duplicado_sin_distinguir_mayusculas(): void
    {
        $this->seed();

        $this->actingAs($this->admin())->post(route('admin.categorias.store'), [
            'nombre' => 'ANALGÉSICOS',
        ])->assertSessionHasErrors('nombre');
    }

    public function test_vendedor_no_puede_acceder_a_crud_administrativo(): void
    {
        $this->seed();

        $this->actingAs($this->vendedor())->get(route('admin.categorias.index'))->assertForbidden();
        $this->actingAs($this->vendedor())->post(route('admin.proveedores.store'), [
            'nombre_proveedor' => 'Proveedor no autorizado',
        ])->assertForbidden();
    }

    public function test_vendedor_consulta_solo_medicamentos_disponibles(): void
    {
        $this->seed();
        $inactivo = Medicamento::query()->firstOrFail();
        $inactivo->update(['activo' => false]);
        $sinStock = Medicamento::query()->where('activo', true)->firstOrFail();
        $sinStock->update(['stock' => 0]);
        $vencido = Medicamento::query()->whereDate('fecha_vencimiento', '<', today())->firstOrFail();

        $this->actingAs($this->vendedor())
            ->get(route('medicamentos.index'))
            ->assertOk()
            ->assertDontSee($inactivo->nombre)
            ->assertDontSee($sinStock->nombre)
            ->assertDontSee($vencido->nombre);
    }

    public function test_administrador_consulta_inventario_activo_incluso_sin_stock_o_vencido(): void
    {
        $this->seed();
        $vencido = Medicamento::query()->whereDate('fecha_vencimiento', '<', today())->firstOrFail();

        $this->actingAs($this->admin())
            ->get(route('medicamentos.index'))
            ->assertOk()
            ->assertSee($vencido->nombre);
    }

    public function test_medicamento_rechaza_valores_negativos(): void
    {
        $this->seed();
        $categoria = Categoria::query()->where('activo', true)->firstOrFail();

        $this->actingAs($this->admin())->post(route('admin.medicamentos.store'), [
            'nombre' => 'Medicamento inválido',
            'precio_compra' => -1,
            'precio_venta' => 1,
            'stock' => 1,
            'fecha_vencimiento' => today()->addYear()->toDateString(),
            'id_categoria' => $categoria->id_categoria,
        ])->assertSessionHasErrors('precio_compra');
    }

    public function test_no_se_puede_crear_medicamento_en_categoria_inactiva(): void
    {
        $this->seed();
        $categoria = Categoria::query()->firstOrFail();
        $categoria->update(['activo' => false]);

        $this->actingAs($this->admin())->post(route('admin.medicamentos.store'), [
            'nombre' => 'Medicamento sin categoría activa',
            'precio_compra' => 1,
            'precio_venta' => 2,
            'stock' => 1,
            'fecha_vencimiento' => today()->addYear()->toDateString(),
            'id_categoria' => $categoria->id_categoria,
        ])->assertSessionHasErrors('id_categoria');
    }

    public function test_medicamento_rechaza_precio_de_venta_menor_al_de_compra(): void
    {
        $this->seed();
        $categoria = Categoria::query()->where('activo', true)->firstOrFail();

        $this->actingAs($this->admin())->post(route('admin.medicamentos.store'), [
            'nombre' => 'Medicamento sin margen',
            'precio_compra' => 10,
            'precio_venta' => 9,
            'stock' => 1,
            'fecha_vencimiento' => today()->addYear()->toDateString(),
            'id_categoria' => $categoria->id_categoria,
        ])->assertSessionHasErrors('precio_venta');
    }

    public function test_medicamento_rechaza_fecha_de_vencimiento_pasada(): void
    {
        $this->seed();
        $categoria = Categoria::query()->where('activo', true)->firstOrFail();

        $this->actingAs($this->admin())->post(route('admin.medicamentos.store'), [
            'nombre' => 'Medicamento vencido nuevo',
            'precio_compra' => 1,
            'precio_venta' => 2,
            'stock' => 1,
            'fecha_vencimiento' => today()->subDay()->toDateString(),
            'id_categoria' => $categoria->id_categoria,
        ])->assertSessionHasErrors('fecha_vencimiento');
    }

    public function test_proveedor_rechaza_nit_ci_duplicado(): void
    {
        $this->seed();
        $proveedor = Proveedor::query()->firstOrFail();

        $this->actingAs($this->admin())->post(route('admin.proveedores.store'), [
            'nombre_proveedor' => 'Proveedor repetido',
            'nit_ci' => strtolower($proveedor->nit_ci),
        ])->assertSessionHasErrors('nit_ci');
    }

    public function test_catalogos_se_desactivan_logicamente(): void
    {
        $this->seed();
        $categoria = Categoria::query()->firstOrFail();
        $medicamento = Medicamento::query()->firstOrFail();
        $proveedor = Proveedor::query()->firstOrFail();
        $admin = $this->admin();

        $this->actingAs($admin)->delete(route('admin.categorias.destroy', $categoria));
        $this->actingAs($admin)->delete(route('admin.medicamentos.destroy', $medicamento));
        $this->actingAs($admin)->delete(route('admin.proveedores.destroy', $proveedor));

        $this->assertFalse($categoria->fresh()->activo);
        $this->assertFalse($medicamento->fresh()->activo);
        $this->assertFalse($proveedor->fresh()->activo);
    }
}
