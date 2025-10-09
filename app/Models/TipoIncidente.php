<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoIncidente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipos_incidente';

    protected $fillable = ['nombre','severidad'];

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'tipo_id');
    }
}
