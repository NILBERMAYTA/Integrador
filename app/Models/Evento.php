<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evento extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre','descripcion','fecha_inicio','fecha_fin','nivel','estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin'    => 'datetime',
    ];

    public const NIVELES = ['bajo', 'medio', 'alto'];
    public const ESTADOS = ['planificado', 'activo', 'cerrado'];

    public function operaciones()
    {
        return $this->hasMany(Operacion::class);
    }
}
