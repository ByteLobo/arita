<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Medicamento;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Medicamento> */
class MedicamentoFactory extends Factory
{
    protected $model = Medicamento::class;

    public function definition(): array
    {
        $precioCompra = fake()->randomFloat(2, 1, 100);

        return [
            'nombre' => fake()->unique()->words(2, true),
            'precio_compra' => $precioCompra,
            'precio_venta' => round($precioCompra * 1.3, 2),
            'stock' => fake()->numberBetween(0, 100),
            'fecha_vencimiento' => today()->addMonths(6),
            'id_categoria' => Categoria::factory(),
            'activo' => true,
        ];
    }
}
