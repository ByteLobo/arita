<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicamentoConsultaController extends Controller
{
    public function index(Request $request): View
    {
        $consulta = Medicamento::query()->with('categoria');
        if ($request->user()->rol->nombre === 'Vendedor') {
            $consulta->disponiblesParaVenta();
        } else {
            $consulta->activos();
        }

        $medicamentos = $consulta
            ->when($request->string('buscar')->isNotEmpty(), function ($query) use ($request): void {
                $termino = $request->string('buscar')->toString();
                $query->where(function ($subquery) use ($termino): void {
                    $subquery->where('nombre', 'ilike', "%{$termino}%")
                        ->orWhereHas('categoria', fn ($categoria) => $categoria->where('nombre', 'ilike', "%{$termino}%"));
                });
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('medicamentos.index', compact('medicamentos'));
    }
}
