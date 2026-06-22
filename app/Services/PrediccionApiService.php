<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PrediccionApiService
{
    public function health(): array
    {
        return $this->request('get', '/health');
    }

    public function listarPrediccionesArmamento(int $limit = 100): array
    {
        return $this->request('get', '/predictions/armamento', [
            'limit' => max(1, min($limit, 500)),
        ]);
    }

    public function resumenPrediccionesArmamento(
        ?int $unidadId = null,
        int $page = 1,
        int $perPage = 10,
    ): array
    {
        return $this->request('get', '/predictions/armamento/summary', array_filter([
            'unidad_id' => $unidadId,
            'page' => max(1, $page),
            'per_page' => max(1, min($perPage, 50)),
        ], fn ($value) => $value !== null));
    }

    public function listarPrediccionesConflicto(int $limit = 100): array
    {
        $payload = $this->request('get', '/predictions/conflicto', [
            'limit' => max(1, min($limit, 500)),
        ]);

        return is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
    }

    public function entrenarArmamento(): array
    {
        return $this->request('post', '/train/armamento');
    }

    public function explicabilidadGlobalArmamento(
        ?int $unidadId = null,
        int $sampleSize = 500,
    ): array {
        return $this->request('get', '/explainability/armamento/global', array_filter([
            'unidad_id' => $unidadId,
            'sample_size' => max(50, min($sampleSize, 1000)),
        ], fn ($value) => $value !== null));
    }

    public function explicarSerieArmamento(int $serieId): array
    {
        return $this->request('get', "/explainability/armamento/{$serieId}");
    }

    protected function request(string $method, string $uri, array $query = []): array
    {
        try {
            $response = Http::baseUrl($this->baseUrl())
                ->timeout($this->timeout())
                ->acceptJson()
                ->send(strtoupper($method), $uri, [
                    'query' => $query,
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'El servicio de predicción no está disponible. Inicia ml_service en el puerto 8002.',
                previous: $exception
            );
        }

        $this->throwIfFailed($response);

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('La API de prediccion devolvio una respuesta invalida.');
        }

        return $payload;
    }

    protected function throwIfFailed(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $payload = $response->json();
        $message = is_array($payload)
            ? ($payload['detail'] ?? $payload['message'] ?? 'Error desconocido')
            : $response->body();

        throw new RuntimeException("Error HTTP {$response->status()}: {$message}");
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.prediccion_api.url'), '/');
    }

    protected function timeout(): int
    {
        return max(1, (int) config('services.prediccion_api.timeout', 30));
    }
}
