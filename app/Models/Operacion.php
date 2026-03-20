<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operacion extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'operaciones';

    protected $fillable = [
        'tipo','evento_id','usuario_destino_id','actor_id','unidad_id','fecha','observaciones','operacion_padre_id',
    ];

    protected $attributes = [
        'tipo' => 'asignacion',
    ];

    protected $casts = [
        'tipo'  => 'string',     // enum de Postgres
        'fecha' => 'datetime',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    public function usuarioDestino()
    {
        return $this->belongsTo(User::class, 'usuario_destino_id');
    }

    public function policia()
    {
        return $this->usuarioDestino();
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function detalles()
    {
        return $this->hasMany(OperacionDetalle::class, 'operacion_id');
    }

    public function padre()
    {
        return $this->belongsTo(self::class, 'operacion_padre_id');
    }

    public function devoluciones()
    {
        return $this->hasMany(self::class, 'operacion_padre_id');
    }

    public function scopeForUnidad($query, ?int $unidadId)
    {
        return $query->when($unidadId, fn ($builder) => $builder->where('unidad_id', $unidadId));
    }
}
