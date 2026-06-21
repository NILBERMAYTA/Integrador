<?php

namespace App\Livewire\Prestamos;

use App\Models\Evento;
use App\Models\Operacion;
use App\Models\Unidad;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $eventoId = '';
    public $estado = ''; // pendiente | concluido
    public $unidadId = '';
    public $perPage = 15;

    public function mount(): void
    {
        $this->unidadId = auth()->user()?->unidad_id ?? '';
    }

    public function render()
    {
        $unidadId = $this->unidadId !== '' ? $this->unidadId : null;

        $baseQuery = Operacion::query()
            ->with([
                'policia',
                'evento',
                'unidad',
                'detalles.articulo',
                'detalles.series.serie',
                'devoluciones.detalles',
            ])
            ->where('tipo', 'asignacion')
            ->when(! auth()->user()?->isAdministradorGeneral(), fn ($query) => $query->where('unidad_id', $unidadId))
            ->when(auth()->user()?->isPolicia(), function ($query) {
                $query->where('usuario_destino_id', auth()->id());
            })
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
        $unidades = Unidad::orderBy('nombre')->get(['id', 'nombre', 'sigla']);

        $totalPrestamos = $collection->count();
        $pendientes = $collection->filter(fn ($op) => $this->estadoPrestamo($op) === 'pendiente')->count();
        $stats = [
            'total' => $totalPrestamos,
            'pendientes' => $pendientes,
            'concluidos' => max(0, $totalPrestamos - $pendientes),
            'pct_pendientes' => $totalPrestamos > 0 ? (int) round($pendientes / $totalPrestamos * 100) : 0,
        ];

        return view('livewire.prestamos.index', compact('operaciones', 'eventos', 'unidades', 'stats'));
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingEventoId() { $this->resetPage(); }
    public function updatingEstado() { $this->resetPage(); }
    public function updatingUnidadId() { $this->resetPage(); }

    public function estadoPrestamo(Operacion $op): string
    {
        $devueltosCantidad = [];
        foreach ($op->devoluciones as $dev) {
            foreach ($dev->detalles as $detDev) {
                if (optional($detDev->articulo)?->isCantidad()) {
                    $devueltosCantidad[$detDev->articulo_id] = ($devueltosCantidad[$detDev->articulo_id] ?? 0) + (int) $detDev->cantidad;
                }
            }
        }

        foreach ($op->detalles as $detOp) {
            if (optional($detOp->articulo)?->isSerializado()) {
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
