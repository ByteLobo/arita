<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Medicamento;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $administrador = Rol::query()->create(['nombre' => 'Administrador']);
        $vendedor = Rol::query()->create(['nombre' => 'Vendedor']);

        Usuario::query()->create([
            'nombre' => 'Administrador Demo',
            'email' => 'admin@farmacia.test',
            'password' => 'Admin123!',
            'id_rol' => $administrador->id_rol,
            'activo' => true,
        ]);

        Usuario::query()->create([
            'nombre' => 'Vendedor Demo',
            'email' => 'vendedor@farmacia.test',
            'password' => 'Vendedor123!',
            'id_rol' => $vendedor->id_rol,
            'activo' => true,
        ]);

        $analgesicos = Categoria::query()->create([
            'nombre' => 'Analgésicos',
            'descripcion' => 'Medicamentos para aliviar el dolor.',
        ]);
        $antibioticos = Categoria::query()->create([
            'nombre' => 'Antibióticos',
            'descripcion' => 'Medicamentos antimicrobianos sujetos a control profesional.',
        ]);
        $vitaminas = Categoria::query()->create([
            'nombre' => 'Vitaminas',
            'descripcion' => 'Suplementos vitamínicos de demostración.',
        ]);

        Proveedor::query()->insert([
            [
                'nombre_proveedor' => 'Distribuidora Andina',
                'telefono' => '72000001',
                'nit_ci' => '1020304050',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proveedor' => 'Farmacéutica Illimani',
                'telefono' => '72000002',
                'nit_ci' => '5060708090',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Medicamento::query()->insert([
            $this->medicamento('Paracetamol 500 mg', 0.50, 1.00, 100, today()->addYear(), $analgesicos->id_categoria),
            $this->medicamento('Ibuprofeno 400 mg', 0.80, 1.50, 3, today()->addMonths(8), $analgesicos->id_categoria),
            $this->medicamento('Amoxicilina 500 mg', 1.20, 2.30, 40, today()->subDay(), $antibioticos->id_categoria),
            $this->medicamento('Vitamina C 1 g', 0.70, 1.40, 25, today()->addDays(15), $vitaminas->id_categoria),
            $this->medicamento('Complejo B', 0.90, 1.80, 30, today()->addMonths(10), $vitaminas->id_categoria),
        ]);
    }

    /** @return array<string, mixed> */
    private function medicamento(
        string $nombre,
        float $precioCompra,
        float $precioVenta,
        int $stock,
        mixed $vencimiento,
        int $categoriaId,
    ): array {
        return [
            'nombre' => $nombre,
            'precio_compra' => $precioCompra,
            'precio_venta' => $precioVenta,
            'stock' => $stock,
            'fecha_vencimiento' => $vencimiento,
            'id_categoria' => $categoriaId,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
