<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->rol?->nombre, ['Administrador', 'Vendedor'], true);
    }

    public function rules(): array
    {
        return [
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.id_medicamento' => ['required', 'integer', 'exists:medicamentos,id_medicamento'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $ids = collect($this->input('detalles', []))->pluck('id_medicamento')->filter();
            if ($ids->duplicates()->isNotEmpty()) {
                $validator->errors()->add('detalles', 'No se permiten medicamentos duplicados en una misma venta.');
            }
        });
    }
}
