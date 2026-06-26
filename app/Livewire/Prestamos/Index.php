<?php

namespace App\Livewire\Prestamos;

use App\Models\Evento;
use App\Models\Operacion;
use App\Models\Unidad;
use Illuminate\Database\Eloquent\Builder;
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
        $baseQuery = $this->baseQuery();
        $filteredQuery = (clone $baseQuery)
            ->with([
                'policia',
                'evento',
                'unidad',
                'detalles.articulo',
                'detalles.series.serie',
                'devoluciones.detalles',
            ])
            ->when($this->estado === 'pendiente', fn (Builder $query) => $this->wherePrestamoPendiente($query))
            ->when($this->estado === 'concluido', fn (Builder $query) => $this->wherePrestamoConcluido($query))
            ->latest();

        $operaciones = $filteredQuery->paginate($this->perPage);

        $eventos = Evento::orderBy('id', 'desc')->get();
        $unidades = Unidad::orderBy('nombre')->get(['id', 'nombre', 'sigla']);

        $totalPrestamos = (clone $baseQuery)->count();
        $pendientes = $this->wherePrestamoPendiente(clone $baseQuery)->count();
        $stats = [
            'total' => $totalPrestamos,
            'pendientes' => $pendientes,
            'concluidos' => max(0, $totalPrestamos - $pendientes),
            'pct_pendientes' => $totalPrestamos > 0 ? (int) round($pendientes / $totalPrestamos * 100) : 0,
        ];

        return view('livewire.prestamos.index', compact('operaciones', 'eventos', 'unidades', 'stats'));
    }

    private function baseQuery(): Builder
    {
        $unidadId = $this->unidadId !== '' ? $this->unidadId : null;

        return Operacion::query()
            ->where('tipo', 'asignacion')
            ->when(! auth()->user()?->isAdministradorGeneral(), fn ($query) => $query->where('unidad_id', $unidadId))
            ->when(auth()->user()?->isPolicia(), function ($query) {
                $query->where('usuario_destino_id', auth()->id());
            })
            ->when($this->search, function ($query) {
                $query->whereHas('policia', fn ($u) => $u->where('name', 'like', "%{$this->search}%"));
            })
            ->when($this->eventoId, fn ($q) => $q->where('evento_id', $this->eventoId));
    }

    private function wherePrestamoPendiente(Builder $query): Builder
    {
        return $query->whereExists(fn ($subquery) => $this->prestamoPendienteSubquery($subquery));
    }

    private function wherePrestamoConcluido(Builder $query): Builder
    {
        return $query->whereNotExists(fn ($subquery) => $this->prestamoPendienteSubquery($subquery));
    }

    private function prestamoPendienteSubquery($subquery): void
    {
        $subquery
            ->selectRaw('1')
            ->from('operacion_detalles as od')
            ->join('articulos as a', 'a.id', '=', 'od.articulo_id')
            ->whereColumn('od.operacion_id', 'operaciones.id')
            ->whereNull('od.deleted_at')
            ->where(function ($pending) {
                $pending
                    ->where(function ($serializados) {
                        $serializados
                            ->whereRaw("a.tipo::text = 'reutilizable'")
                            ->whereExists(function ($series) {
                                $series
                                    ->selectRaw('1')
                                    ->from('operacion_detalle_series as ods')
                                    ->join('articulo_series as s', 's.id', '=', 'ods.serie_id')
                                    ->whereColumn('ods.operacion_detalle_id', 'od.id')
                                    ->whereColumn('s.operacion_detalle_id_actual', 'od.id')
                                    ->whereNull('ods.deleted_at')
                                    ->whereNull('s.deleted_at');
                            });
                    })
                    ->orWhere(function ($cantidad) {
                        $cantidad
                            ->whereRaw("a.tipo::text = 'consumible'")
                            ->whereRaw('od.cantidad > COALESCE((
                                SELECT SUM(odd.cantidad)
                                FROM operaciones dev
                                JOIN operacion_detalles odd ON odd.operacion_id = dev.id
                                WHERE dev.operacion_padre_id = operaciones.id
                                    AND dev.deleted_at IS NULL
                                    AND odd.deleted_at IS NULL
                                    AND odd.articulo_id = od.articulo_id
                            ), 0)');
                    });
            });
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
