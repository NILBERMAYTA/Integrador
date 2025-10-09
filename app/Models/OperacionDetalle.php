<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperacionDetalle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operacion_detalles';

    protected $fillable = [
        'operacion_id','articulo_id','cantidad','condicion','observaciones',
    ];

    protected $casts = [
        'cantidad'  => 'decimal:2',
        'condicion' => 'string', // enum Postgres
    ];

    public function operacion()
    {
        return $this->belongsTo(Operacion::class);
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }

    public function series()
    {
        return $this->hasMany(OperacionDetalleSerie::class, 'operacion_detalle_id');
    }
}
