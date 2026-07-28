<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->string('estado')->toString();

        return view('admin.categorias.index', [
            'categorias' => Categoria::query()
                ->when($request->filled('buscar'), fn ($query) => $query->where('nombre', 'ilike', '%'.$request->string('buscar')->toString().'%'))
                ->when($estado === 'activas', fn ($query) => $query->activos())
                ->when($estado === 'inactivas', fn ($query) => $query->where('activo', false))
                ->orderBy('nombre')->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categorias.form', ['categoria' => new Categoria, 'modo' => 'Crear']);
    }

    public function store(CategoriaRequest $request): RedirectResponse
    {
        Categoria::query()->create($request->validated());

        return redirect()->route('admin.categorias.index')->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria): View
    {
        return view('admin.categorias.form', ['categoria' => $categoria, 'modo' => 'Editar']);
    }

    public function update(CategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return redirect()->route('admin.categorias.index')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        $categoria->update(['activo' => false]);

        return back()->with('success', 'Categoría desactivada.');
    }
}
