<?php

namespace App\Livewire\Prestamos;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Evento;
use App\Models\Operacion;
use App\Models\OperacionDetalle;
use App\Models\OperacionDetalleSerie;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Create extends Component
{
    public $evento_id;
    public $policia_id;
    public $observaciones;
    public $items = [];
    public $eventos = [];
    public $policias = [];
    public $articulos = [];
    public $seriesDisponibles = [];

    protected $rules = [
        'evento_id' => 'required|exists:eventos,id',
        'policia_id' => 'required|exists:users,id',
        'items' => 'required|array|min:1',
        'items.*.articulo_id' => 'required|exists:articulos,id',
        'items.*.cantidad' => 'required|integer|min:1',
        'items.*.seguimiento' => 'nullable|in:serie,cantidad',
        'items.*.series' => 'array',
        'items.*.series.*' => 'integer|exists:articulo_series,id',
    ];

    public function mount()
    {
        $this->eventos = Evento::orderBy('id', 'desc')->get();
        $this->policias = User::where('role', 'policia')->get();
        $this->articulos = Articulo::orderBy('nombre')->get();
        $this->seriesDisponibles = ArticuloSerie::select('id', 'articulo_id', 'codigo_serie')
            ->where('estado', 'disponible')
            ->orderBy('codigo_serie')
            ->get()
            ->groupBy('articulo_id')
            ->map(function ($group) {
                return $group->map(fn($serie) => [
                    'id' => $serie->id,
                    'codigo_serie' => $serie->codigo_serie,
                ])->values()->all();
            })
            ->toArray(); // Livewire no deshidrata colecciones Eloquent

        $this->addRow();
    }

    public function addRow()
    {
        $this->items[] = [
            'articulo_id' => '',
            'nombre' => '',
            'cantidad' => 1,
            'seguimiento' => '',
            'series' => [],
        ];
    }

    public function removeRow($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'articulo_id') {
            $index = (int) $parts[0];
            $articulo = $value ? $this->articulos->firstWhere('id', (int) $value) : null;

            $this->items[$index]['nombre'] = $articulo->nombre ?? '';
            $this->items[$index]['seguimiento'] = $articulo->seguimiento ?? '';
            $this->items[$index]['series'] = [];
        }
    }

    public function save()
    {
        $this->validate();

        foreach ($this->items as $idx => $item) {
            if (($item['seguimiento'] ?? '') === 'serie') {
                $cantidad = (int) ($item['cantidad'] ?? 0);
                $seleccionadas = is_array($item['series']) ? count($item['series']) : 0;

                if ($seleccionadas !== $cantidad) {
                    $this->addError("items.$idx.series", "Debes seleccionar exactamente {$cantidad} series.");
                    return;
                }
            }
        }

        DB::transaction(function () {
            $operacion = Operacion::create([
                'tipo' => 'asignacion',
                'evento_id' => $this->evento_id,
                'policia_id' => $this->policia_id,
                'actor_id' => Auth::id(),
                'fecha' => now(),
                'observaciones' => $this->observaciones,
            ]);

            foreach ($this->items as $item) {
                $detalle = OperacionDetalle::create([
                    'operacion_id' => $operacion->id,
                    'articulo_id' => $item['articulo_id'],
                    'cantidad' => $item['cantidad'],
                    'condicion' => 'bueno',
                ]);

                if (($item['seguimiento'] ?? '') === 'serie' && !empty($item['series'])) {
                    foreach ($item['series'] as $serieId) {
                        OperacionDetalleSerie::create([
                            'operacion_detalle_id' => $detalle->id,
                            'serie_id' => $serieId,
                        ]);

                        ArticuloSerie::where('id', $serieId)->update([
                            'estado' => 'asignado',
                            'operacion_detalle_id_actual' => $detalle->id,
                        ]);
                    }
                }
            }

            session()->flash('success', 'Prestamo registrado correctamente.');
        });

        return redirect()->route('prestamos.index');
    }

    public function render()
    {
        return view('livewire.prestamos.create');
    }
}
