<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Articulo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'categoria_id','nombre','unidad_medida','descripcion','tipo','seguimiento',
    ];

    protected $casts = [
        // enums en Postgres → strings en PHP
        'tipo'        => 'string',      // reutilizable | consumible
        'seguimiento' => 'string',      // serie | cantidad
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function series()
    {
        return $this->hasMany(ArticuloSerie::class);
    }

    public function detalles()
    {
        return $this->hasMany(OperacionDetalle::class);
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class);
    }

    public function inspecciones()
    {
        return $this->hasMany(Inspeccion::class);
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }
}
