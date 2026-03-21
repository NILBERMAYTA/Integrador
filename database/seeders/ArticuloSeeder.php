<?php

namespace Database\Seeders;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Categoria;
use App\Models\Unidad;
use Illuminate\Database\Seeder;

class ArticuloSeeder extends Seeder
{
    public function run(): void
    {
        $catProteccion = Categoria::where('nombre', 'Proteccion')->firstOrFail()->id;
        $catMunicion = Categoria::where('nombre', 'Municion')->firstOrFail()->id;

        $articulos = [
            ['categoria_id' => $catProteccion, 'nombre' => 'Chaleco antibalas', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad'],
            ['categoria_id' => $catProteccion, 'nombre' => 'Casco tactico', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad'],
            ['categoria_id' => $catProteccion, 'nombre' => 'Escudo antimotin', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad'],
            ['categoria_id' => $catMunicion, 'nombre' => 'Municion 9 mm', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'cartucho'],
            ['categoria_id' => $catMunicion, 'nombre' => 'Municion calibre 12', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'cartucho'],
        ];

        $unidades = Unidad::query()->orderBy('id')->get();

        foreach ($articulos as $a) {
            $art = Articulo::updateOrCreate(
                ['categoria_id' => $a['categoria_id'], 'nombre' => $a['nombre']],
                [
                    'unidad_medida' => $a['unidad_medida'],
                    'descripcion' => null,
                    'tipo' => $a['tipo'],
                    'seguimiento' => $a['seguimiento'],
                ]
            );

            if ($a['seguimiento'] === 'serie') {
                foreach ($unidades as $unidad) {
                    for ($i = 1; $i <= 4; $i++) {
                        ArticuloSerie::updateOrCreate(
                            ['codigo_serie' => strtoupper(substr($a['nombre'], 0, 3)).'-'.$unidad->sigla.'-'.str_pad($i, 3, '0', STR_PAD_LEFT)],
                            [
                                'articulo_id' => $art->id,
                                'unidad_id' => $unidad->id,
                                'estado' => 'disponible',
                                'condicion_actual' => 'bueno',
                            ]
                        );
                    }
                }
            }
        }
    }
}
