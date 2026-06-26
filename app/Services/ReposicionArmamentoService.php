<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ReposicionArmamentoService
{
    public function __construct(
        protected PrediccionApiService $prediccionApi,
    ) {}

    public function calcular(?int $unidadId = null): array
    {
        return $this->prediccionApi->recomendacionesReposicion($unidadId);
    }

    public function resumenGeneral(?int $unidadId = null): array
    {
        return $this->calcular($unidadId)['resumen'] ?? [];
    }

    public function recomendaciones(?int $unidadId = null): Collection
    {
        return collect($this->calcular($unidadId)['recomendaciones'] ?? []);
    }
}
