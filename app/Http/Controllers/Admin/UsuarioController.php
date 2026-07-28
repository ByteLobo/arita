<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsuarioRequest;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.usuarios.index', [
            'usuarios' => Usuario::query()->with('rol')
                ->when($request->filled('buscar'), function ($query) use ($request): void {
                    $termino = $request->string('buscar')->toString();
                    $query->where(fn ($subquery) => $subquery->where('nombre', 'ilike', '%'.$termino.'%')->orWhere('email', 'ilike', '%'.$termino.'%'));
                })
                ->orderBy('nombre')->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.usuarios.form', ['usuario' => new Usuario, 'roles' => Rol::query()->where('activo', true)->orderBy('nombre')->get(), 'modo' => 'Crear']);
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        Usuario::query()->create($request->validated());

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(Usuario $usuario): View
    {
        return view('admin.usuarios.form', ['usuario' => $usuario, 'roles' => Rol::query()->where('activo', true)->orderBy('nombre')->get(), 'modo' => 'Editar']);
    }

    public function update(UsuarioRequest $request, Usuario $usuario): RedirectResponse
    {
        $datos = $request->validated();
        if (blank($datos['password'] ?? null)) {
            unset($datos['password'], $datos['password_confirmation']);
        }
        if ($usuario->is(auth()->user()) && array_key_exists('activo', $datos) && ! $datos['activo']) {
            return back()->withInput()->withErrors(['activo' => 'No puedes desactivar tu propio usuario.']);
        }

        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        if ($usuario->is(auth()->user())) {
            return back()->withErrors(['usuario' => 'No puedes desactivar tu propio usuario.']);
        }

        $usuario->update(['activo' => false]);

        return back()->with('success', 'Usuario desactivado.');
    }
}
