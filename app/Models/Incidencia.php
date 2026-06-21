<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incidencia extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tipo_id','articulo_id','serie_id','policia_id',
        'descripcion','latitud','longitud','fecha','creado_por',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'latitud' => 'float',
        'longitud' => 'float',
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoIncidente::class, 'tipo_id');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }

    public function serie()
    {
        return $this->belongsTo(ArticuloSerie::class, 'serie_id');
    }

    public function policia()
    {
        return $this->belongsTo(User::class, 'policia_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
