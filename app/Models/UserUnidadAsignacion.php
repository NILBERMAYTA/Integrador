<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserUnidadAsignacion extends BaseModel
{
    use HasFactory;

    protected $table = 'user_unidad_asignaciones';

    protected $fillable = [
        'user_id',
        'unidad_origen_id',
        'unidad_destino_id',
        'transferido_por',
        'fecha_transferencia',
        'motivo',
    ];

    protected $casts = [
        'fecha_transferencia' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_destino_id');
    }

    public function unidadOrigen()
    {
        return $this->belongsTo(Unidad::class, 'unidad_origen_id');
    }

    public function unidadDestino()
    {
        return $this->belongsTo(Unidad::class, 'unidad_destino_id');
    }

    public function transferidoPor()
    {
        return $this->belongsTo(User::class, 'transferido_por');
    }
}
