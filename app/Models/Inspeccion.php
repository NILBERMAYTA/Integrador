<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inspeccion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inspecciones';

    protected $fillable = [
        'articulo_id','serie_id','inspector_id','resultado',
        'observaciones','realizada_en',
    ];

    protected $casts = [
        'resultado'   => 'string',   // enum Postgres
        'realizada_en'=> 'datetime',
    ];

    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }

    public function serie()
    {
        return $this->belongsTo(ArticuloSerie::class, 'serie_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
