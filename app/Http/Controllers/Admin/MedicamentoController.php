<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicamentoRequest;
use App\Models\Categoria;
use App\Models\Medicamento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicamentoController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.medicamentos.index', [
            'medicamentos' => Medicamento::query()->with('categoria')
                ->when($request->filled('buscar'), function ($query) use ($request): void {
                    $termino = $request->string('buscar')->toString();
                    $query->where(function ($subquery) use ($termino): void {
                        $subquery->where('nombre', 'ilike', '%'.$termino.'%')
                            ->orWhereHas('categoria', fn ($categoria) => $categoria->where('nombre', 'ilike', '%'.$termino.'%'));
                    });
                })
                ->when($request->string('estado')->toString() === 'activos', fn ($query) => $query->activos())
                ->when($request->string('estado')->toString() === 'inactivos', fn ($query) => $query->where('activo', false))
                ->orderBy('nombre')->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.medicamentos.form', [
            'medicamento' => new Medicamento,
            'categorias' => Categoria::query()->activos()->orderBy('nombre')->get(),
            'modo' => 'Crear',
        ]);
    }

    public function store(MedicamentoRequest $request): RedirectResponse
    {
        Medicamento::query()->create($request->validated());

        return redirect()->route('admin.medicamentos.index')->with('success', 'Medicamento creado correctamente.');
    }

    public function edit(Medicamento $medicamento): View
    {
        return view('admin.medicamentos.form', [
            'medicamento' => $medicamento,
            'categorias' => Categoria::query()->activos()->orderBy('nombre')->get(),
            'modo' => 'Editar',
        ]);
    }

    public function update(MedicamentoRequest $request, Medicamento $medicamento): RedirectResponse
    {
        $medicamento->update($request->validated());

        return redirect()->route('admin.medicamentos.index')->with('success', 'Medicamento actualizado correctamente.');
    }

    public function destroy(Medicamento $medicamento): RedirectResponse
    {
        $medicamento->update(['activo' => false]);

        return back()->with('success', 'Medicamento desactivado.');
    }
}
