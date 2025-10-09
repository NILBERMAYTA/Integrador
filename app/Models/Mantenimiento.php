<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mantenimiento extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'articulo_id','serie_id','creado_por','tipo','descripcion',
        'fecha_inicio','fecha_fin','costo',
    ];

    protected $casts = [
        'tipo'         => 'string',   // enum Postgres
        'fecha_inicio' => 'datetime',
        'fecha_fin'    => 'datetime',
        'costo'        => 'decimal:2',
    ];

    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }

    public function serie()
    {
        return $this->belongsTo(ArticuloSerie::class, 'serie_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
