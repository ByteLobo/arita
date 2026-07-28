<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CompraException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompraRequest;
use App\Models\Compra;
use App\Models\Medicamento;
use App\Models\Proveedor;
use App\Services\CompraService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompraController extends Controller
{
    public function index(): View
    {
        return view('admin.compras.index', [
            'compras' => Compra::query()->with(['proveedor', 'usuario'])->latest('fecha')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.compras.form', [
            'proveedores' => Proveedor::query()->activos()->orderBy('nombre_proveedor')->get(),
            'medicamentos' => Medicamento::query()->activos()->orderBy('nombre')->get(),
        ]);
    }

    public function store(CompraRequest $request, CompraService $compraService): RedirectResponse
    {
        try {
            $compra = $compraService->registrar($request->validated(), $request->user());
        } catch (CompraException $exception) {
            return back()->withInput()->withErrors(['compra' => $exception->getMessage()]);
        }

        return redirect()->route('admin.compras.show', $compra)->with('success', 'Compra registrada y stock actualizado correctamente.');
    }

    public function show(Compra $compra): View
    {
        return view('admin.compras.show', ['compra' => $compra->load(['proveedor', 'usuario', 'detalles.medicamento'])]);
    }

    public function pdf(Compra $compra): mixed
    {
        $compra->load(['proveedor', 'usuario', 'detalles.medicamento']);

        return Pdf::loadView('comprobantes.compra', ['compra' => $compra])
            ->setPaper('a4')
            ->download('comprobante-compra-'.$compra->id_compra.'.pdf');
    }
}
