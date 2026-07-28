<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Medicamento;
use App\Models\Proveedor;
use App\Models\Venta;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $hoy = today();

        return view('dashboard', [
            'medicamentosActivos' => Medicamento::query()->activos()->count(),
            'categoriasActivas' => Categoria::query()->activos()->count(),
            'proveedoresActivos' => Proveedor::query()->activos()->count(),
            'ventasHoy' => Venta::query()->whereDate('fecha', $hoy)->count(),
            'vencidos' => Medicamento::query()->activos()->vencidos()->count(),
            'porVencer' => Medicamento::query()->activos()->porVencer()->count(),
            'stockBajo' => Medicamento::query()->activos()->stockBajo()->count(),
        ]);
    }
}
