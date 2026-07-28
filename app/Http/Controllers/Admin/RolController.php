<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolRequest;
use App\Models\Rol;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RolController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', ['roles' => Rol::query()->withCount('usuarios')->orderBy('nombre')->paginate(10)]);
    }

    public function create(): View
    {
        return view('admin.roles.form', ['rol' => new Rol, 'modo' => 'Crear']);
    }

    public function store(RolRequest $request): RedirectResponse
    {
        Rol::query()->create($request->validated());

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function edit(Rol $rol): View
    {
        return view('admin.roles.form', ['rol' => $rol, 'modo' => 'Editar']);
    }

    public function update(RolRequest $request, Rol $rol): RedirectResponse
    {
        $rol->update($request->validated());

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Rol $rol): RedirectResponse
    {
        if ($rol->usuarios()->where('activo', true)->exists()) {
            return back()->withErrors(['rol' => 'No puedes desactivar un rol con usuarios activos.']);
        }

        $rol->update(['activo' => false]);

        return back()->with('success', 'Rol desactivado.');
    }
}
