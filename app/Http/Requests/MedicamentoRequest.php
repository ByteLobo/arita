<?php

namespace App\Http\Requests;

use App\Models\Categoria;
use App\Models\Medicamento;
use Illuminate\Foundation\Http\FormRequest;

class MedicamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->rol?->nombre === 'Administrador';
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:160'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'fecha_vencimiento' => ['required', 'date'],
            'id_categoria' => ['required', 'integer', 'exists:categorias,id_categoria'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $nombre = $this->string('nombre')->toString();
            $query = Medicamento::query()->whereRaw('LOWER(public.unaccent_immutable(nombre)) = LOWER(public.unaccent_immutable(?))', [$nombre]);
            if ($this->route('medicamento')) {
                $query->whereKeyNot($this->route('medicamento')->id_medicamento);
            }
            if ($query->exists()) {
                $validator->errors()->add('nombre', 'Ya existe un medicamento con ese nombre.');
            }

            $precioCompra = (float) $this->input('precio_compra');
            $precioVenta = (float) $this->input('precio_venta');
            if ($precioVenta < $precioCompra) {
                $validator->errors()->add('precio_venta', 'El precio de venta no puede ser menor que el precio de compra.');
            }

            $fechaVencimiento = $this->date('fecha_vencimiento');
            $medicamento = $this->route('medicamento');
            $fechaAnterior = $medicamento?->fecha_vencimiento;
            if ($fechaVencimiento && $fechaVencimiento->isBefore(today()) && (! $fechaAnterior || ! $fechaVencimiento->isSameDay($fechaAnterior))) {
                $validator->errors()->add('fecha_vencimiento', 'La fecha de vencimiento debe ser hoy o posterior.');
            }

            if ($this->integer('id_categoria') && ! Categoria::query()->whereKey($this->integer('id_categoria'))->where('activo', true)->exists()) {
                $validator->errors()->add('id_categoria', 'La categoría debe existir y estar activa.');
            }
        });
    }
}
