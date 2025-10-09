<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operaciones';

    protected $fillable = [
        'tipo','evento_id','policia_id','actor_id','fecha','observaciones',
    ];

    protected $casts = [
        'tipo'  => 'string',     // enum de Postgres
        'fecha' => 'datetime',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    public function policia()
    {
        return $this->belongsTo(User::class, 'policia_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function detalles()
    {
        return $this->hasMany(OperacionDetalle::class);
    }
}
