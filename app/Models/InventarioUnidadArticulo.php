<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventarioUnidadArticulo extends BaseModel
{
    use HasFactory;

    protected $table = 'inventario_unidad_articulos';

    protected $fillable = [
        'unidad_id',
        'articulo_id',
        'cantidad_disponible',
        'cantidad_asignada',
        'cantidad_mantenimiento',
        'stock_minimo',
    ];

    protected $casts = [
        'cantidad_disponible' => 'decimal:2',
        'cantidad_asignada' => 'decimal:2',
        'cantidad_mantenimiento' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }
}
