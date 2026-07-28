<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Proveedor> */
class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition(): array
    {
        return [
            'nombre_proveedor' => fake()->company(),
            'telefono' => fake()->numerify('7#######'),
            'nit_ci' => fake()->unique()->numerify('########'),
            'activo' => true,
        ];
    }
}
