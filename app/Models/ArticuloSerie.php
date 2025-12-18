<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticuloSerie extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'articulo_series';

    protected $fillable = ['articulo_id','codigo_serie','observaciones','estado','operacion_detalle_id_actual'];

    protected $casts = [
        'estado' => 'string',
    ];

    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }

    public function operacionDetalleActual()
    {
        return $this->belongsTo(OperacionDetalle::class, 'operacion_detalle_id_actual');
    }

    public function detalleSeries()
    {
        return $this->hasMany(OperacionDetalleSerie::class, 'serie_id');
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'serie_id');
    }

    public function inspecciones()
    {
        return $this->hasMany(Inspeccion::class, 'serie_id');
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'serie_id');
    }
}
