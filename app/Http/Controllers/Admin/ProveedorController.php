<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->string('estado')->toString();

        return view('admin.proveedores.index', [
            'proveedores' => Proveedor::query()
                ->when($request->filled('buscar'), fn ($query) => $query->where('nombre_proveedor', 'ilike', '%'.$request->string('buscar')->toString().'%'))
                ->when($estado === 'activos', fn ($query) => $query->activos())
                ->when($estado === 'inactivos', fn ($query) => $query->where('activo', false))
                ->orderBy('nombre_proveedor')->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.proveedores.form', ['proveedor' => new Proveedor, 'modo' => 'Crear']);
    }

    public function store(ProveedorRequest $request): RedirectResponse
    {
        Proveedor::query()->create($request->validated());

        return redirect()->route('admin.proveedores.index')->with('success', 'Proveedor creado correctamente.');
    }

    public function edit(Proveedor $proveedor): View
    {
        return view('admin.proveedores.form', ['proveedor' => $proveedor, 'modo' => 'Editar']);
    }

    public function update(ProveedorRequest $request, Proveedor $proveedor): RedirectResponse
    {
        $proveedor->update($request->validated());

        return redirect()->route('admin.proveedores.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        $proveedor->update(['activo' => false]);

        return back()->with('success', 'Proveedor desactivado.');
    }
}
