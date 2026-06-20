<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Articulo extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'categoria_id',
        'nombre',
        'unidad_medida',
        'descripcion',
        'foto_path',
        'tipo',
        'seguimiento',
    ];

    protected $casts = [
        'tipo' => 'string',
        'seguimiento' => 'string',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function series()
    {
        return $this->hasMany(ArticuloSerie::class);
    }

    public function inventariosUnidad()
    {
        return $this->hasMany(InventarioUnidadArticulo::class, 'articulo_id');
    }

    public function detalles()
    {
        return $this->hasMany(OperacionDetalle::class);
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class);
    }

    public function inspecciones()
    {
        return $this->hasMany(Inspeccion::class);
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }

    public function isSerializado(): bool
    {
        return $this->tipo === 'reutilizable';
    }

    public function isCantidad(): bool
    {
        return $this->tipo === 'consumible';
    }

    public function seguimientoLabel(): string
    {
        return $this->isSerializado() ? 'Serie' : 'Cantidad';
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (empty($this->foto_path) || ! Storage::disk('public')->exists($this->foto_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->foto_path);
    }
}
