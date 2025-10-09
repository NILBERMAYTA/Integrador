<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticuloSerie extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'articulo_series';

    protected $fillable = ['articulo_id','codigo_serie','observaciones'];

    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
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
