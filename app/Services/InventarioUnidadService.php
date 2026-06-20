<?php

namespace App\Services;

use App\Models\InventarioUnidadArticulo;
use App\Models\Articulo;
use RuntimeException;

class InventarioUnidadService
{
    public function ensure(int $unidadId, int $articuloId): InventarioUnidadArticulo
    {
        return InventarioUnidadArticulo::query()->firstOrCreate(
            [
                'unidad_id' => $unidadId,
                'articulo_id' => $articuloId,
            ],
            [
                'cantidad_disponible' => 0,
                'cantidad_asignada' => 0,
                'cantidad_mantenimiento' => 0,
                'stock_minimo' => 0,
            ]
        );
    }

    public function addInitialStock(int $unidadId, int $articuloId, float $cantidad): InventarioUnidadArticulo
    {
        $inventario = $this->ensure($unidadId, $articuloId);
        $inventario->increment('cantidad_disponible', $cantidad);

        return $inventario->refresh();
    }

    public function setMinimumStock(int $unidadId, int $articuloId, float $stockMinimo): InventarioUnidadArticulo
    {
        $inventario = $this->ensure($unidadId, $articuloId);
        $inventario->stock_minimo = max(0, $stockMinimo);
        $inventario->save();

        return $inventario->refresh();
    }

    public function assign(int $unidadId, Articulo $articulo, float $cantidad): InventarioUnidadArticulo
    {
        $inventario = $this->ensure($unidadId, $articulo->id);

        if ((float) $inventario->cantidad_disponible < $cantidad) {
            throw new RuntimeException('Stock insuficiente en la unidad para el articulo seleccionado.');
        }

        $inventario->cantidad_disponible = (float) $inventario->cantidad_disponible - $cantidad;
        $inventario->cantidad_asignada = (float) $inventario->cantidad_asignada + $cantidad;
        $inventario->save();

        return $inventario;
    }

    public function returnAssigned(int $unidadId, Articulo $articulo, float $cantidad): InventarioUnidadArticulo
    {
        $inventario = $this->ensure($unidadId, $articulo->id);

        $inventario->cantidad_asignada = max(0, (float) $inventario->cantidad_asignada - $cantidad);
        $inventario->cantidad_disponible = (float) $inventario->cantidad_disponible + $cantidad;
        $inventario->save();

        return $inventario;
    }

    public function consume(int $unidadId, Articulo $articulo, float $cantidad): InventarioUnidadArticulo
    {
        $inventario = $this->ensure($unidadId, $articulo->id);

        if ((float) $inventario->cantidad_disponible < $cantidad) {
            throw new RuntimeException('No existe stock suficiente para consumir la cantidad solicitada.');
        }

        $inventario->cantidad_disponible = (float) $inventario->cantidad_disponible - $cantidad;
        $inventario->save();

        return $inventario;
    }

    public function moveToMaintenance(int $unidadId, Articulo $articulo, float $cantidad): InventarioUnidadArticulo
    {
        $inventario = $this->ensure($unidadId, $articulo->id);

        if ((float) $inventario->cantidad_disponible < $cantidad) {
            throw new RuntimeException('Stock insuficiente para enviar a mantenimiento.');
        }

        $inventario->cantidad_disponible = (float) $inventario->cantidad_disponible - $cantidad;
        $inventario->cantidad_mantenimiento = (float) $inventario->cantidad_mantenimiento + $cantidad;
        $inventario->save();

        return $inventario;
    }

    public function returnFromMaintenance(int $unidadId, Articulo $articulo, float $cantidad): InventarioUnidadArticulo
    {
        $inventario = $this->ensure($unidadId, $articulo->id);

        $inventario->cantidad_mantenimiento = max(0, (float) $inventario->cantidad_mantenimiento - $cantidad);
        $inventario->cantidad_disponible = (float) $inventario->cantidad_disponible + $cantidad;
        $inventario->save();

        return $inventario;
    }
}
