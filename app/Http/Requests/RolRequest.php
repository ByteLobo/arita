<?php

namespace App\Http\Requests;

use App\Models\Rol;
use Illuminate\Foundation\Http\FormRequest;

class RolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->rol?->nombre === 'Administrador';
    }

    public function rules(): array
    {
        return ['nombre' => ['required', 'string', 'max:50']];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $query = Rol::query()->whereRaw('LOWER(nombre) = LOWER(?)', [$this->string('nombre')->toString()]);
            if ($this->route('rol')) {
                $query->whereKeyNot($this->route('rol')->id_rol);
            }
            if ($query->exists()) {
                $validator->errors()->add('nombre', 'Ya existe un rol con ese nombre.');
            }
        });
    }
}
