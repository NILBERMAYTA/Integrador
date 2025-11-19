<?php

namespace App\Livewire\Articulos;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Articulo;
use App\Models\Categoria;

class Inventario extends Component
{
    use WithPagination;

    // Filtros
    public ?int $categoria_id = null;
    public string $search = '';
    public string $sortField = 'articulos.nombre';
    public string $sortDirection = 'asc';

    // Paginación
    public int $perPage = 15;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoria()
    {
        $this->resetPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        // Query base: artículos activos
        $query = Articulo::query()
            ->with(['categoria'])
            ->when($this->categoria_id, fn($q) => $q->where('categoria_id', $this->categoria_id))
            ->when($this->search, fn($q) => 
                $q->where('nombre', 'ilike', "%{$this->search}%")
                  ->orWhere('descripcion', 'ilike', "%{$this->search}%")
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Mapear artículos con cálculo consolidado de inventario
        $articulos = $query->through(function ($articulo) {
            return [
                'articulo' => $articulo,
                'entrada' => $this->calcularEntrada($articulo),
                'salida' => $this->calcularSalida($articulo),
                'total' => $this->calcularTotal($articulo),
                'ultimo_movimiento' => $this->obtenerUltimoMovimiento($articulo),
            ];
        });

        $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);

        return view('livewire.articulos.inventario', [
            'articulos' => $articulos,
            'categorias' => $categorias,
        ]);
    }

    /**
     * Calcula cantidad de ENTRADA (ajuste, devolucion, mantenimiento_retorno)
     */
    private function calcularEntrada(Articulo $articulo): int|float
    {
        return DB::table('operacion_detalles as od')
            ->join('operaciones as o', 'o.id', '=', 'od.operacion_id')
            ->whereNull('od.deleted_at')
            ->where('od.articulo_id', $articulo->id)
            ->whereIn('o.tipo', ['ajuste', 'devolucion', 'mantenimiento_retorno'])
            ->sum('od.cantidad') ?? 0;
    }

    /**
     * Calcula cantidad de SALIDA (asignacion, consumo, mantenimiento_salida)
     */
    private function calcularSalida(Articulo $articulo): int|float
    {
        return DB::table('operacion_detalles as od')
            ->join('operaciones as o', 'o.id', '=', 'od.operacion_id')
            ->whereNull('od.deleted_at')
            ->where('od.articulo_id', $articulo->id)
            ->whereIn('o.tipo', ['asignacion', 'consumo', 'mantenimiento_salida'])
            ->sum('od.cantidad') ?? 0;
    }

    /**
     * Calcula TOTAL NETO = entrada - salida
     */
    private function calcularTotal(Articulo $articulo): int|float
    {
        if ($articulo->seguimiento === 'cantidad') {
            return $this->calcularEntrada($articulo) - $this->calcularSalida($articulo);
        } else {
            // Para serie: contar series activas (no soft-deleted)
            return DB::table('articulo_series')
                ->whereNull('deleted_at')
                ->where('articulo_id', $articulo->id)
                ->count();
        }
    }

    /**
     * Obtiene la fecha del último movimiento (operación más reciente)
     */
    private function obtenerUltimoMovimiento(Articulo $articulo): ?string
    {
        $fecha = DB::table('operacion_detalles as od')
            ->join('operaciones as o', 'o.id', '=', 'od.operacion_id')
            ->whereNull('od.deleted_at')
            ->where('od.articulo_id', $articulo->id)
            ->max('o.fecha');
        
        return $fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y H:i') : null;
    }
}
