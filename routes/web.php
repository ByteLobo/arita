<?php

use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\CompraController;
use App\Http\Controllers\Admin\MedicamentoController;
use App\Http\Controllers\Admin\ProveedorController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicamentoConsultaController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::middleware(['auth', 'activo'])->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/medicamentos', [MedicamentoConsultaController::class, 'index'])
        ->middleware('rol:Administrador,Vendedor')->name('medicamentos.index');

    Route::middleware('rol:Administrador,Vendedor')->prefix('ventas')->name('ventas.')->group(function (): void {
        Route::get('/', [VentaController::class, 'index'])->name('index');
        Route::get('/create', [VentaController::class, 'create'])->name('create');
        Route::post('/', [VentaController::class, 'store'])->name('store');
        Route::get('/{venta}/pdf', [VentaController::class, 'pdf'])->name('pdf');
        Route::get('/{venta}', [VentaController::class, 'show'])->name('show');
    });

    Route::middleware('rol:Administrador')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', fn () => view('admin.panel'))->name('panel');
        Route::resource('categorias', CategoriaController::class)->except(['show']);
        Route::resource('medicamentos', MedicamentoController::class)->except(['show']);
        Route::resource('proveedores', ProveedorController::class)
            ->parameters(['proveedores' => 'proveedor'])
            ->except(['show']);
        Route::resource('usuarios', UsuarioController::class)->parameters(['usuarios' => 'usuario'])->except(['show']);
        Route::resource('roles', RolController::class)->parameters(['roles' => 'rol'])->except(['show']);
        Route::get('compras', [CompraController::class, 'index'])->name('compras.index');
        Route::get('compras/create', [CompraController::class, 'create'])->name('compras.create');
        Route::post('compras', [CompraController::class, 'store'])->name('compras.store');
        Route::get('compras/{compra}/pdf', [CompraController::class, 'pdf'])->name('compras.pdf');
        Route::get('compras/{compra}', [CompraController::class, 'show'])->name('compras.show');
    });

    Route::middleware('rol:Administrador,Vendedor')->prefix('vendedor')->name('vendedor.')->group(function (): void {
        Route::get('/', fn () => view('vendedor.panel'))->name('panel');
    });
});
