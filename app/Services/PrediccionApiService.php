<?php

namespace App\Services;

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

    protected function request(string $method, string $uri, array $query = []): array
    {
        $response = Http::baseUrl($this->baseUrl())
            ->timeout($this->timeout())
            ->acceptJson()
            ->send(strtoupper($method), $uri, [
                'query' => $query,
            ]);

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
