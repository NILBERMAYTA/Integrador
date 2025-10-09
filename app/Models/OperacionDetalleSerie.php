<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperacionDetalleSerie extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operacion_detalle_series';

    protected $fillable = ['operacion_detalle_id','serie_id'];

    public function detalle()
    {
        return $this->belongsTo(OperacionDetalle::class, 'operacion_detalle_id');
    }

    public function serie()
    {
        return $this->belongsTo(ArticuloSerie::class, 'serie_id');
    }
}
