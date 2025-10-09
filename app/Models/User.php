<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name','email','password','role','can_login',
        'rango','numero_escalafon','fecha_ingreso','remember_token',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'fecha_ingreso'     => 'date',
        'can_login'         => 'boolean',
        // enums de Postgres se manejan como string
        'role'              => 'string',
    ];

    // Operaciones donde el usuario es el receptor/afectado (policía)
    public function operacionesComoPolicia()
    {
        return $this->hasMany(Operacion::class, 'policia_id');
    }

    // Operaciones registradas por el usuario (furriel/admin)
    public function operacionesRegistradas()
    {
        return $this->hasMany(Operacion::class, 'actor_id');
    }

    public function mantenimientosCreados()
    {
        return $this->hasMany(Mantenimiento::class, 'creado_por');
    }

    public function inspeccionesRealizadas()
    {
        return $this->hasMany(Inspeccion::class, 'inspector_id');
    }

    public function incidenciasCreadas()
    {
        return $this->hasMany(Incidencia::class, 'creado_por');
    }

    public function incidenciasComoPolicia()
    {
        return $this->hasMany(Incidencia::class, 'policia_id');
    }
}
