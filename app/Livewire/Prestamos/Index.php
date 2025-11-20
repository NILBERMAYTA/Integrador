<?php

namespace App\Livewire\Prestamos;

use App\Models\Evento;
use App\Models\Operacion;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $eventoId = '';
    public $estado = ''; // pendiente | concluido
    public $perPage = 15;

    public function render()
    {
        $baseQuery = Operacion::query()
            ->with([
                'policia',
                'evento',
                'detalles.articulo',
                'detalles.series.serie',
                'devoluciones.detalles',
            ])
            ->where('tipo', 'asignacion')
            ->when($this->search, function ($query) {
                $query->whereHas('policia', fn ($u) => $u->where('name', 'like', "%{$this->search}%"));
            })
            ->when($this->eventoId, fn ($q) => $q->where('evento_id', $this->eventoId))
            ->latest();

        $collection = $baseQuery->get();

        $filtered = $collection->filter(function ($op) {
            $estado = $this->estadoPrestamo($op);
            if ($this->estado) {
                return $estado === $this->estado;
            }
            return true;
        })->values();

        $page = $this->page ?? 1;
        $items = $filtered->forPage($page, $this->perPage);
        $operaciones = new LengthAwarePaginator(
            $items,
            $filtered->count(),
            $this->perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $eventos = Evento::orderBy('id', 'desc')->get();

        return view('livewire.prestamos.index', compact('operaciones', 'eventos'));
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingEventoId() { $this->resetPage(); }
    public function updatingEstado() { $this->resetPage(); }

    public function estadoPrestamo(Operacion $op): string
    {
        $devueltosCantidad = [];
        foreach ($op->devoluciones as $dev) {
            foreach ($dev->detalles as $detDev) {
                if (optional($detDev->articulo)->seguimiento === 'cantidad') {
                    $devueltosCantidad[$detDev->articulo_id] = ($devueltosCantidad[$detDev->articulo_id] ?? 0) + (int) $detDev->cantidad;
                }
            }
        }

        foreach ($op->detalles as $detOp) {
            if (optional($detOp->articulo)->seguimiento === 'serie') {
                $asignadas = $detOp->series->filter(fn($s) => optional($s->serie)->operacion_detalle_id_actual === $detOp->id);
                if ($asignadas->count() > 0) {
                    return 'pendiente';
                }
            } else {
                $dev = $devueltosCantidad[$detOp->articulo_id] ?? 0;
                if ($detOp->cantidad > $dev) {
                    return 'pendiente';
                }
            }
        }

        return 'concluido';
    }

}
