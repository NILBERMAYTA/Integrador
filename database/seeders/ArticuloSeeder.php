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
        $categorias = Categoria::query()->pluck('id', 'nombre');

        $articulos = [
            ['categoria' => 'Proteccion', 'nombre' => 'Overol retardante al fuego', 'prefijo' => 'OVR', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Prenda ignifuga interna para operaciones de control de multitudes, con inspeccion y lavado posterior a cada uso.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Protector de dorso y brazos', 'prefijo' => 'PDB', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Proteccion superior antitrauma de policarbonato con acolchado para tronco, brazos y dorso de la mano.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Protector de antebrazos', 'prefijo' => 'PAB', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'par', 'descripcion' => 'Protectores de policarbonato para antebrazos con velcros y broches de sujecion.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Guantes antiflama anticorte', 'prefijo' => 'GAA', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'par', 'descripcion' => 'Guantes para aislar manos y munecas ante objetos cortantes, sustancias inflamables o fuego.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Pasamontanas antiflama', 'prefijo' => 'PSM', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Prenda antiflama para cubrir cabeza y rostro en procedimientos con riesgo de quemaduras.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Protector interno de genitales', 'prefijo' => 'PIG', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Protector interno de nylon con platina de proteccion y velcros de sujecion.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Protector externo de genitales', 'prefijo' => 'PEG', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Protector acolchado y resistente al fuego para zona pelvica durante despliegues antidisturbios.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Protector de muslos', 'prefijo' => 'PMS', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'par', 'descripcion' => 'Proteccion inferior de policarbonato y espuma EVA con correas para movilidad y cobertura de muslos.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Canilleras antidisturbios', 'prefijo' => 'CNL', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'par', 'descripcion' => 'Proteccion de rodilla, tibia, tobillo y empeine para impactos contundentes.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Casco tactico', 'prefijo' => 'CAS', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Casco antidisturbios con visor, barbijo y proteccion de nuca; no sustituye proteccion balistica.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Casco balistico', 'prefijo' => 'CBL', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Casco de proteccion balistica para craneo, con sistema de suspension y retencion.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Escudo antimotin', 'prefijo' => 'ESC', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Escudo de policarbonato para cubrir al usuario ante objetos lanzados, palos, piedras y botellas.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Escudo balistico', 'prefijo' => 'EBL', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Escudo con nivel minimo III para amenazas con armas de fuego o artefactos explosivos.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Chaleco antibalas', 'prefijo' => 'CHA', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Chaleco de proteccion balistica con panel frontal, posterior y placa reductora de trauma.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Mascara antigas', 'prefijo' => 'MAG', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Proteccion respiratoria con visor, valvulas, arneses y rosca para filtro contra vapores y agentes quimicos.'],
            ['categoria' => 'Proteccion', 'nombre' => 'Estuche porta mascara', 'prefijo' => 'EPM', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Estuche resistente para transporte de mascara antigas y filtro en la armadura policial.'],
            ['categoria' => 'Armamento menos letal', 'nombre' => 'Baston policial PR-24', 'prefijo' => 'PR24', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Baston de 24 pulgadas para defensa, proteccion, conduccion y mantenimiento de distancia.'],
            ['categoria' => 'Armamento menos letal', 'nombre' => 'Rifle lanza gas', 'prefijo' => 'RLG', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Arma portatil de un disparo para cartuchos de agente quimico, asignada a personal calificado.'],
            ['categoria' => 'Armamento menos letal', 'nombre' => 'Pistola lanza gas', 'prefijo' => 'PLG', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Dispositivo corto para lanzamiento de cartuchos de agente quimico en control de multitudes.'],
            ['categoria' => 'Armamento menos letal', 'nombre' => 'Escopeta antidisturbios calibre 12', 'prefijo' => 'E12', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Escopeta policial para municion de impacto o adaptador tromblon, bajo registro de armeria.'],
            ['categoria' => 'Armamento menos letal', 'nombre' => 'Tromblon adaptador lanza granadas', 'prefijo' => 'TRB', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Adaptador para lanzar granadas de mano mediante escopeta con cartucho impulsor.'],
            ['categoria' => 'Armamento menos letal', 'nombre' => 'Megafono operativo', 'prefijo' => 'MEG', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Equipo audible para advertencias, ordenes y negociacion durante operaciones.'],
            ['categoria' => 'Agentes quimicos', 'nombre' => 'Granada lacrimogena simple accion CS', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'unidad', 'descripcion' => 'Granada manual de agente quimico CS para dispersion en espacios abiertos con vias de salida.'],
            ['categoria' => 'Agentes quimicos', 'nombre' => 'Granada lacrimogena triple accion CS', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'unidad', 'descripcion' => 'Granada de multiples emisiones para saturacion planificada en control de multitudes.'],
            ['categoria' => 'Agentes quimicos', 'nombre' => 'Granada fumigena HC', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'unidad', 'descripcion' => 'Granada de humo para cortina visual e impacto psicologico; prohibida en lugares cerrados.'],
            ['categoria' => 'Agentes quimicos', 'nombre' => 'Granada de aturdimiento', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'unidad', 'descripcion' => 'Granada de luz, sonido y nube inerte para dispersion de multitudes violentas.'],
            ['categoria' => 'Agentes quimicos', 'nombre' => 'Proyectil lacrimogeno 37/38 mm corto alcance', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'cartucho', 'descripcion' => 'Cartucho de agente quimico para rifle lanza gas en distancias cortas segun especificacion tecnica.'],
            ['categoria' => 'Agentes quimicos', 'nombre' => 'Proyectil lacrimogeno 37/38 mm largo alcance', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'cartucho', 'descripcion' => 'Cartucho de agente quimico para tiro parabolico o rastrero con rifle lanza gas.'],
            ['categoria' => 'Agentes quimicos', 'nombre' => 'Spray gas pimienta OC 500 ml', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'frasco', 'descripcion' => 'Aerosol OC para control de alteraciones de poca magnitud o grupos reducidos.'],
            ['categoria' => 'Agentes quimicos', 'nombre' => 'Balon lanza gas CN/CS', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'balon', 'descripcion' => 'Dispositivo presurizado para saturar areas pequenas con agente lacrimogeno o irritante.'],
            ['categoria' => 'Agentes quimicos', 'nombre' => 'Filtro para mascara antigas', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'unidad', 'descripcion' => 'Filtro reemplazable para mascara antigas; debe inspeccionarse y cambiarse cuando este saturado o vencido.'],
            ['categoria' => 'Municion', 'nombre' => 'Municion 9 mm', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'cartucho', 'descripcion' => 'Cartucheria de dotacion controlada para armamento autorizado.'],
            ['categoria' => 'Municion', 'nombre' => 'Municion calibre 12', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'cartucho', 'descripcion' => 'Cartucheria calibre 12 para escopeta policial segun procedimiento y registro.'],
            ['categoria' => 'Municion', 'nombre' => 'Cartucho impulsor calibre 12', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'cartucho', 'descripcion' => 'Cartucho impulsor para lanzamiento de granadas mediante tromblon.'],
            ['categoria' => 'Municion', 'nombre' => 'Cartucho perdigon de goma calibre 12', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'cartucho', 'descripcion' => 'Municion de impacto menos letal para escopeta antidisturbios.'],
            ['categoria' => 'Vehiculos tacticos', 'nombre' => 'Vehiculo Neptuno lanza agua', 'prefijo' => 'NEP', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Vehiculo antidisturbios lanza agua con blindaje, pala, mallas protectoras y sistema de video.'],
            ['categoria' => 'Vehiculos tacticos', 'nombre' => 'Vehiculo tactico antidisturbios VTA', 'prefijo' => 'VTA', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Vehiculo blindado de apoyo a personal a pie, con capacidad de diseminacion de agentes quimicos.'],
            ['categoria' => 'Vehiculos tacticos', 'nombre' => 'Vehiculo de apoyo tactico VAT', 'prefijo' => 'VAT', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Vehiculo para traslado de personal, logistica y apoyo operativo.'],
            ['categoria' => 'Primeros auxilios', 'nombre' => 'Ferulas de inmovilizacion', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'unidad', 'descripcion' => 'Ferulas de madera cubiertas con tela y esponja para inmovilizacion de lesionados.'],
            ['categoria' => 'Primeros auxilios', 'nombre' => 'Vendas elasticas', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'unidad', 'descripcion' => 'Vendas para contencion, soporte e inmovilizacion en primeros auxilios.'],
            ['categoria' => 'Primeros auxilios', 'nombre' => 'Paquetes de gasas', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'paquete', 'descripcion' => 'Gasas esteriles para limpieza y cobertura de heridas.'],
            ['categoria' => 'Primeros auxilios', 'nombre' => 'Algodon hidrofilo', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'paquete', 'descripcion' => 'Insumo de botiquin para curacion y apoyo en limpieza.'],
            ['categoria' => 'Primeros auxilios', 'nombre' => 'Agua oxigenada', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'frasco', 'descripcion' => 'Solucion de uso sanitario para botiquin de primera respuesta.'],
            ['categoria' => 'Primeros auxilios', 'nombre' => 'Alcohol medicinal', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'frasco', 'descripcion' => 'Alcohol para apoyo sanitario y desinfeccion externa.'],
            ['categoria' => 'Primeros auxilios', 'nombre' => 'Guantes de exploracion', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'par', 'descripcion' => 'Guantes descartables para atencion inicial y bioseguridad.'],
            ['categoria' => 'Primeros auxilios', 'nombre' => 'Guantes quirurgicos', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'par', 'descripcion' => 'Guantes de bioseguridad contemplados en equipo de primeros auxilios y armadura policial.'],
            ['categoria' => 'Primeros auxilios', 'nombre' => 'Jeringa 5cc aguja 21', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'unidad', 'descripcion' => 'Jeringa de 5cc con aguja numero 21 para botiquin paramedico.'],
            ['categoria' => 'Primeros auxilios', 'nombre' => 'Cloruro de sodio 1L', 'tipo' => 'consumible', 'seguimiento' => 'cantidad', 'unidad_medida' => 'bolsa', 'descripcion' => 'Solucion salina de un litro para equipo de primera respuesta.'],
            ['categoria' => 'Accesorios tacticos', 'nombre' => 'Chaleco tactico porta municion', 'prefijo' => 'CTP', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Chaleco tactico para portar municion, agentes quimicos y accesorios de dotacion.'],
            ['categoria' => 'Accesorios tacticos', 'nombre' => 'Linterna tactica', 'prefijo' => 'LNT', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Linterna de dotacion para armadura policial y apoyo operativo.'],
            ['categoria' => 'Accesorios tacticos', 'nombre' => 'Navaja multiuso', 'prefijo' => 'NVJ', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Herramienta multiuso para armadura policial.'],
            ['categoria' => 'Accesorios tacticos', 'nombre' => 'Manillas policiales', 'prefijo' => 'MNL', 'tipo' => 'reutilizable', 'seguimiento' => 'serie', 'unidad_medida' => 'unidad', 'descripcion' => 'Elemento de sujecion para procedimientos de arresto o aprehension.'],
        ];

        $unidades = Unidad::query()->orderBy('id')->get();

        foreach ($articulos as $a) {
            $categoriaId = $categorias->get($a['categoria']);

            if (! $categoriaId) {
                throw new \RuntimeException("Categoria no encontrada para articulo: {$a['nombre']}");
            }

            $art = Articulo::updateOrCreate(
                ['categoria_id' => $categoriaId, 'nombre' => $a['nombre']],
                [
                    'unidad_medida' => $a['unidad_medida'],
                    'descripcion' => $a['descripcion'],
                    'tipo' => $a['tipo'],
                    'seguimiento' => $a['seguimiento'],
                ]
            );

            if ($a['seguimiento'] === 'serie') {
                foreach ($unidades as $unidad) {
                    for ($i = 1; $i <= 4; $i++) {
                        ArticuloSerie::updateOrCreate(
                            ['codigo_serie' => $a['prefijo'].'-'.$unidad->sigla.'-'.str_pad($i, 3, '0', STR_PAD_LEFT)],
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
