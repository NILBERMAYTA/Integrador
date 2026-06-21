<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticuloSerie extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'articulo_series';

    protected $fillable = ['articulo_id','unidad_id','codigo_serie','observaciones','foto_path','estado','condicion_actual','operacion_detalle_id_actual'];

    protected $casts = [
        'estado' => 'string',
        'condicion_actual' => 'string',
    ];

    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
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

    public function scopeForUnidad($query, ?int $unidadId)
    {
        return $query->when($unidadId, fn ($builder) => $builder->where('unidad_id', $unidadId));
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (empty($this->foto_path) || ! \Illuminate\Support\Facades\Storage::disk('public')->exists($this->foto_path)) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->foto_path);
    }
}
