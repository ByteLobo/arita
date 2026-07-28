<?php

namespace App\Http\Requests;

use App\Models\Categoria;
use Illuminate\Foundation\Http\FormRequest;

class CategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->rol?->nombre === 'Administrador';
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $query = Categoria::query()->whereRaw('LOWER(public.unaccent_immutable(nombre)) = LOWER(public.unaccent_immutable(?))', [$this->string('nombre')->toString()]);
            if ($this->route('categoria')) {
                $query->whereKeyNot($this->route('categoria')->id_categoria);
            }
            if ($query->exists()) {
                $validator->errors()->add('nombre', 'Ya existe una categoría con ese nombre.');
            }
        });
    }
}
