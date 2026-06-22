<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unidad extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'unidades';

    protected $fillable = [
        'nombre',
        'sigla',
        'codigo_externo',
        'descripcion',
    ];

    public function asignacionesUsuarios()
    {
        return $this->hasMany(UserUnidadAsignacion::class, 'unidad_destino_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'unidad_id');
    }

    public function destinos()
    {
        return $this->hasMany(Destino::class);
    }

    public function operaciones()
    {
        return $this->hasMany(Operacion::class, 'unidad_id');
    }

    public function series()
    {
        return $this->hasMany(ArticuloSerie::class, 'unidad_id');
    }

    public function inventarios()
    {
        return $this->hasMany(InventarioUnidadArticulo::class, 'unidad_id');
    }
}
