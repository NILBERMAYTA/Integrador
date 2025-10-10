<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

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

    // Ordena por el apellido completo concatenado
    public function scopeOrderByApellidos(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

        return $query
            ->orderByRaw("LOWER(apellido_paterno) $direction NULLS LAST")
            ->orderByRaw("LOWER(apellido_materno) $direction NULLS LAST");
    }

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

    public function initials(): string
{
    // Toma name o, si no hay, el usuario del email (antes del @)
    $name = trim($this->name ?: (string) str($this->email)->before('@'));

    if ($name === '') {
        return 'U'; // fallback
    }

    // Obtiene 2 iniciales máximo (soporta acentos/UTF-8)
    $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
    $letters = [];
    foreach ($parts as $p) {
        $letters[] = mb_strtoupper(mb_substr($p, 0, 1));
        if (count($letters) === 2) break;
    }
    return implode('', $letters);
}

}
