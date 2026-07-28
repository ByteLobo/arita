<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;

class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->rol?->nombre === 'Administrador';
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');
        $passwordRules = $usuario ? ['nullable', 'string', 'min:8', 'confirmed'] : ['required', 'string', 'min:8', 'confirmed'];

        return [
            'nombre' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'password' => $passwordRules,
            'id_rol' => ['required', 'integer', 'exists:roles,id_rol'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $query = Usuario::query()->whereRaw('LOWER(email) = LOWER(?)', [$this->string('email')->toString()]);
            if ($this->route('usuario')) {
                $query->whereKeyNot($this->route('usuario')->id_usuario);
            }
            if ($query->exists()) {
                $validator->errors()->add('email', 'Ya existe un usuario con ese correo.');
            }
        });
    }
}
