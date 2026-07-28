<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCompra extends Model
{
    protected $table = 'detalle_compras';

    protected $primaryKey = 'id_detalle_compra';

    protected $fillable = ['id_compra', 'id_medicamento', 'cantidad', 'precio_compra', 'subtotal'];

    protected function casts(): array
    {
        return ['precio_compra' => 'decimal:2', 'subtotal' => 'decimal:2'];
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class, 'id_medicamento', 'id_medicamento');
    }
}
