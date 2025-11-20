<?php

namespace App\Livewire\Articulos;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Articulo;

class AjusteStock extends Component
{
    // ID del artículo que se va a ajustar (pasado al componente)
    public Articulo $articulo;
    
    // Datos del formulario
    public string $tipo_ajuste = 'positivo'; // 'positivo' o 'negativo'
    public ?float $cantidad = null;
    public ?string $codigo_serie = null;
    public string $observaciones = '';
    public string $fecha_ajuste;

    protected function rules() 
    {
        $rules = [
            'tipo_ajuste'   => ['required', 'in:positivo,negativo'],
            'observaciones' => ['required', 'string', 'max:500'],
            'fecha_ajuste'  => ['required', 'date'],
        ];

        if ($this->articulo->seguimiento === 'cantidad') {
            $rules['cantidad'] = ['required', 'numeric', 'gt:0'];
        } else {
            // Requerir la serie si el modo es 'serie'
            $rules['codigo_serie'] = ['required', 'string', 'max:100'];
        }

        return $rules;
    }

    public function mount(Articulo $articulo)
    {
        $this->articulo = $articulo;
        $this->fecha_ajuste = now()->format('Y-m-d H:i'); // Inicializar con fecha actual
    }

    public function saveAjuste()
    {
        $this->validate();
        
        // La lógica de la PROMPT va aquí
        DB::transaction(function () {
            // Operación de ajuste (usamos el tipo 'ajuste' definido en los enums)
            $now = now();
            $fecha = $this->fecha_ajuste ? \Carbon\Carbon::parse($this->fecha_ajuste) : $now;

            $opId = DB::table('operaciones')->insertGetId([
                'tipo' => 'ajuste',
                'evento_id' => null,
                'policia_id' => null,
                'actor_id' => Auth::id(),
                'fecha' => $fecha,
                'observaciones' => $this->observaciones ?: 'Ajuste manual',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Si el artículo es por cantidad, insertamos el detalle con cantidad positiva/negativa
            if ($this->articulo->seguimiento === 'cantidad') {
                $cantidad = (float) $this->cantidad;
                if ($this->tipo_ajuste === 'negativo') {
                    $cantidad = -1 * abs($cantidad);
                }

                DB::table('operacion_detalles')->insert([
                    'operacion_id' => $opId,
                    'articulo_id'  => $this->articulo->id,
                    'cantidad'     => $cantidad,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);

            } else {
                // Seguimiento por serie
                if ($this->tipo_ajuste === 'positivo') {
                    // Crear serie y asociarla al detalle
                    $detId = DB::table('operacion_detalles')->insertGetId([
                        'operacion_id' => $opId,
                        'articulo_id'  => $this->articulo->id,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);

                    // Crear la serie
                    $serieId = DB::table('articulo_series')->insertGetId([
                        'articulo_id'   => $this->articulo->id,
                        'codigo_serie'  => trim($this->codigo_serie),
                        'observaciones' => null,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ]);

                    // Vincular serie al detalle
                    DB::table('operacion_detalle_series')->insert([
                        'operacion_detalle_id' => $detId,
                        'serie_id'             => $serieId,
                        'created_at'           => $now,
                        'updated_at'           => $now,
                    ]);

                } else {
                    // negativo: buscar la serie y marcarla como eliminada (soft-delete)
                    $serie = DB::table('articulo_series')
                        ->where('articulo_id', $this->articulo->id)
                        ->where('codigo_serie', trim($this->codigo_serie))
                        ->whereNull('deleted_at')
                        ->first();

                    if (!$serie) {
                        // Lanzar excepción para hacer rollback
                        throw new \Exception('No se encontró la serie especificada para este artículo.');
                    }

                    // Crear detalle (sin cantidad) para registrar el movimiento
                    DB::table('operacion_detalles')->insert([
                        'operacion_id' => $opId,
                        'articulo_id'  => $this->articulo->id,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);

                    // Soft-delete de la serie (marcar como retirada)
                    DB::table('articulo_series')
                        ->where('id', $serie->id)
                        ->update(['deleted_at' => $now, 'updated_at' => $now]);
                }
            }
        });

        session()->flash('success', 'Ajuste de inventario registrado con éxito.');
        return redirect()->route('articulos.index');
    }

    public function render()
    {
        return view('livewire.articulos.ajuste-stock');
    }
}