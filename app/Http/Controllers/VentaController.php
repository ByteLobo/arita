<?php

namespace App\Http\Controllers;

use App\Exceptions\VentaException;
use App\Http\Requests\VentaRequest;
use App\Models\Medicamento;
use App\Models\Venta;
use App\Services\VentaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(): View
    {
        return view('ventas.index', [
            'ventas' => Venta::query()->with('usuario')->latest('fecha')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('ventas.form', [
            'medicamentos' => Medicamento::query()->disponiblesParaVenta()
                ->orderBy('nombre')->get(),
        ]);
    }

    public function store(VentaRequest $request, VentaService $ventaService): RedirectResponse
    {
        try {
            $venta = $ventaService->registrar($request->validated(), $request->user());
        } catch (VentaException $exception) {
            return back()->withInput()->withErrors(['venta' => $exception->getMessage()]);
        }

        return redirect()->route('ventas.show', $venta)->with('success', 'Venta registrada y stock actualizado correctamente.');
    }

    public function show(Venta $venta): View
    {
        return view('ventas.show', ['venta' => $venta->load(['usuario', 'detalles.medicamento'])]);
    }

    public function pdf(Venta $venta): mixed
    {
        $venta->load(['usuario', 'detalles.medicamento']);

        return Pdf::loadView('comprobantes.venta', ['venta' => $venta])
            ->setPaper('a4')
            ->download('comprobante-venta-'.$venta->id_venta.'.pdf');
    }
}
