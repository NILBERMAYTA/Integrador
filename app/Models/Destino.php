<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Destino extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'unidad_id',
        'nombre',
        'descripcion',
    ];

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
