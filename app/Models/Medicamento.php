<?php

namespace App\Models;

use Database\Factories\MedicamentoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicamento extends Model
{
    /** @use HasFactory<MedicamentoFactory> */
    use HasFactory;

    protected $table = 'medicamentos';

    protected $primaryKey = 'id_medicamento';

    protected $fillable = ['nombre', 'precio_compra', 'precio_venta', 'stock', 'fecha_vencimiento', 'id_categoria', 'activo'];

    protected function casts(): array
    {
        return [
            'precio_compra' => 'decimal:2',
            'precio_venta' => 'decimal:2',
            'fecha_vencimiento' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeVencidos(Builder $query): Builder
    {
        return $query->whereDate('fecha_vencimiento', '<', today());
    }

    public function scopePorVencer(Builder $query, int $dias = 30): Builder
    {
        return $query->whereBetween('fecha_vencimiento', [today(), today()->addDays($dias)]);
    }

    public function scopeStockBajo(Builder $query, ?int $limite = null): Builder
    {
        return $query->where('stock', '<=', $limite ?? config('farmacia.stock_bajo', 5));
    }

    public function scopeDisponiblesParaVenta(Builder $query): Builder
    {
        return $query->activos()->whereDate('fecha_vencimiento', '>=', today())->where('stock', '>', 0);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function detalleCompras(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'id_medicamento', 'id_medicamento');
    }

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'id_medicamento', 'id_medicamento');
    }
}
