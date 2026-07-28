<?php

namespace App\Http\Requests;

use App\Models\Proveedor;
use Illuminate\Foundation\Http\FormRequest;

class ProveedorRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['nit_ci' => trim((string) $this->input('nit_ci')) ?: null]);
    }

    public function authorize(): bool
    {
        return $this->user()?->rol?->nombre === 'Administrador';
    }

    public function rules(): array
    {
        return [
            'nombre_proveedor' => ['required', 'string', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'nit_ci' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $nit = trim((string) $this->input('nit_ci'));
            if ($nit === '') {
                return;
            }

            $query = Proveedor::query()->whereRaw('LOWER(nit_ci) = LOWER(?)', [$nit]);
            if ($this->route('proveedor')) {
                $query->whereKeyNot($this->route('proveedor')->id_proveedor);
            }
            if ($query->exists()) {
                $validator->errors()->add('nit_ci', 'Ya existe un proveedor con ese NIT/CI.');
            }
        });
    }
}
