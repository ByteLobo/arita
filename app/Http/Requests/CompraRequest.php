<?php

namespace App\Http\Requests;

use App\Models\Proveedor;
use Illuminate\Foundation\Http\FormRequest;

class CompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->rol?->nombre === 'Administrador';
    }

    public function rules(): array
    {
        return [
            'id_proveedor' => ['required', 'integer', 'exists:proveedores,id_proveedor'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.id_medicamento' => ['required', 'integer', 'exists:medicamentos,id_medicamento'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_compra' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->integer('id_proveedor') && ! Proveedor::query()->whereKey($this->integer('id_proveedor'))->where('activo', true)->exists()) {
                $validator->errors()->add('id_proveedor', 'El proveedor debe existir y estar activo.');
            }

            $ids = collect($this->input('detalles', []))->pluck('id_medicamento')->filter();
            if ($ids->duplicates()->isNotEmpty()) {
                $validator->errors()->add('detalles', 'No se permiten medicamentos duplicados en una misma compra.');
            }
        });
    }
}
