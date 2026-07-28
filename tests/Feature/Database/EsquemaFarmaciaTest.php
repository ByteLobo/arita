<?php

namespace Tests\Feature\Database;

use App\Models\Categoria;
use App\Models\Medicamento;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EsquemaFarmaciaTest extends TestCase
{
    use RefreshDatabase;

    public function test_crea_las_nueve_tablas_del_modelo_relacional(): void
    {
        foreach ([
            'roles', 'usuarios', 'categorias', 'medicamentos', 'proveedores',
            'compras', 'detalle_compras', 'ventas', 'detalle_ventas',
        ] as $tabla) {
            $this->assertTrue(Schema::hasTable($tabla), "No existe la tabla {$tabla}.");
        }
    }

    public function test_seeder_carga_usuarios_con_password_hasheado_y_datos_de_alerta(): void
    {
        $this->seed();

        $admin = Usuario::query()->where('email', 'admin@farmacia.test')->firstOrFail();

        $this->assertTrue(Hash::check('Admin123!', $admin->password));
        $this->assertDatabaseCount('roles', 2);
        $this->assertDatabaseCount('usuarios', 2);
        $this->assertDatabaseCount('categorias', 3);
        $this->assertDatabaseCount('proveedores', 2);
        $this->assertDatabaseCount('medicamentos', 5);
        $this->assertTrue(Medicamento::query()->whereDate('fecha_vencimiento', '<', today())->exists());
        $this->assertTrue(Medicamento::query()->whereBetween('fecha_vencimiento', [today(), today()->addDays(30)])->exists());
        $this->assertTrue(Medicamento::query()->where('stock', '<=', 5)->exists());
    }

    public function test_nombre_de_medicamento_es_unico_sin_distinguir_mayusculas(): void
    {
        $categoria = Categoria::factory()->create();
        Medicamento::factory()->create(['nombre' => 'Paracetamol', 'id_categoria' => $categoria->id_categoria]);

        $this->expectException(QueryException::class);

        Medicamento::factory()->create(['nombre' => 'PARACETAMOL', 'id_categoria' => $categoria->id_categoria]);
    }

    public function test_check_rechaza_stock_negativo(): void
    {
        $this->expectException(QueryException::class);

        Medicamento::factory()->create(['stock' => -1]);
    }

    public function test_clave_foranea_rechaza_categoria_inexistente(): void
    {
        $this->expectException(QueryException::class);

        Medicamento::factory()->create(['id_categoria' => 999999]);
    }

    public function test_detalle_rechaza_cantidad_cero(): void
    {
        $this->seed();
        $usuarioId = Usuario::query()->valueOrFail('id_usuario');
        $proveedorId = DB::table('proveedores')->value('id_proveedor');
        $medicamentoId = Medicamento::query()->valueOrFail('id_medicamento');
        $compraId = DB::table('compras')->insertGetId([
            'id_proveedor' => $proveedorId,
            'id_usuario' => $usuarioId,
            'fecha' => now(),
            'total' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_compra');

        $this->expectException(QueryException::class);

        DB::table('detalle_compras')->insert([
            'id_compra' => $compraId,
            'id_medicamento' => $medicamentoId,
            'cantidad' => 0,
            'precio_compra' => 1,
            'subtotal' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_restriccion_compuesta_rechaza_medicamento_duplicado(): void
    {
        $this->seed();
        $usuarioId = Usuario::query()->valueOrFail('id_usuario');
        $medicamentoId = Medicamento::query()->valueOrFail('id_medicamento');
        $ventaId = DB::table('ventas')->insertGetId([
            'id_usuario' => $usuarioId,
            'fecha' => now(),
            'total' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_venta');
        $detalle = [
            'id_venta' => $ventaId,
            'id_medicamento' => $medicamentoId,
            'cantidad' => 1,
            'precio_unitario' => 1,
            'subtotal' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('detalle_ventas')->insert($detalle);

        $this->expectException(QueryException::class);

        DB::table('detalle_ventas')->insert($detalle);
    }

    public function test_no_se_puede_eliminar_medicamento_con_operacion_historica(): void
    {
        $this->seed();
        $usuarioId = Usuario::query()->valueOrFail('id_usuario');
        $medicamento = Medicamento::query()->firstOrFail();
        $ventaId = DB::table('ventas')->insertGetId([
            'id_usuario' => $usuarioId,
            'fecha' => now(),
            'total' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_venta');
        DB::table('detalle_ventas')->insert([
            'id_venta' => $ventaId,
            'id_medicamento' => $medicamento->id_medicamento,
            'cantidad' => 1,
            'precio_unitario' => 1,
            'subtotal' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        $medicamento->delete();
    }
}
