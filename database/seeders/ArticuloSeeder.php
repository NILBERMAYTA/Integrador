<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Unidad;

class ArticuloSeeder extends Seeder
{
    public function run(): void
    {
        $catProteccion = Categoria::where('nombre','Protección')->firstOrFail()->id;
        $catDisuasivos = Categoria::where('nombre','Disuasivos')->firstOrFail()->id;
        $catMunicion   = Categoria::where('nombre','Munición')->firstOrFail()->id;

        $articulos = [
            // Reutilizables por serie
            ['categoria_id'=>$catProteccion,'nombre'=>'Chaleco','tipo'=>'reutilizable','seguimiento'=>'serie','unidad_medida'=>'unidad'],
            ['categoria_id'=>$catProteccion,'nombre'=>'Casco','tipo'=>'reutilizable','seguimiento'=>'serie','unidad_medida'=>'unidad'],
            ['categoria_id'=>$catDisuasivos ,'nombre'=>'Escudo','tipo'=>'reutilizable','seguimiento'=>'serie','unidad_medida'=>'unidad'],

            // Consumibles por cantidad
            ['categoria_id'=>$catDisuasivos ,'nombre'=>'Granada de humo','tipo'=>'consumible','seguimiento'=>'cantidad','unidad_medida'=>'unidad'],
            ['categoria_id'=>$catMunicion   ,'nombre'=>'Munición 9mm','tipo'=>'consumible','seguimiento'=>'cantidad','unidad_medida'=>'cartucho'],
        ];

        $unidades = Unidad::query()->orderBy('id')->get();

        foreach ($articulos as $a) {
            // Usamos updateOrCreate por si corres el seeder más de una vez
            $art = Articulo::updateOrCreate(
                ['categoria_id'=>$a['categoria_id'], 'nombre'=>$a['nombre']],
                [
                    'unidad_medida'=>$a['unidad_medida'],
                    'descripcion'=>null,
                    'tipo'=>$a['tipo'],
                    'seguimiento'=>$a['seguimiento'],
                ]
            );

            // Para los de seguimiento por serie, creamos algunas series
            if ($a['seguimiento'] === 'serie') {
                foreach ($unidades as $unidad) {
                    for ($i = 1; $i <= 4; $i++) {
                        ArticuloSerie::updateOrCreate(
                            ['codigo_serie' => strtoupper(substr($a['nombre'], 0, 3)).'-'.$unidad->sigla.'-'.str_pad($i, 3, '0', STR_PAD_LEFT)],
                            [
                                'articulo_id' => $art->id,
                                'unidad_id' => $unidad->id,
                                'estado' => 'disponible',
                            ]
                        );
                    }
                }
            }
        }
    }
}
