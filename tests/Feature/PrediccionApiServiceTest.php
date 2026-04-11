<?php

namespace Tests\Feature;

use App\Services\PrediccionApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PrediccionApiServiceTest extends TestCase
{
    public function test_lista_predicciones_de_armamento(): void
    {
        config()->set('services.prediccion_api.url', 'http://127.0.0.1:8002');
        config()->set('services.prediccion_api.timeout', 30);

        Http::fake([
            'http://127.0.0.1:8002/predictions/armamento*' => Http::response([
                [
                    'id' => 1,
                    'serie_id' => 'SR-100',
                    'estado_predicho' => 'observado',
                    'probabilidad' => 0.91,
                    'nivel_riesgo' => 'alto',
                    'recomendacion' => 'Inspeccion inmediata',
                    'fecha_prediccion' => '2026-03-27T10:00:00Z',
                    'modelo_version' => 'v1',
                ],
            ], 200),
        ]);

        $service = app(PrediccionApiService::class);
        $result = $service->listarPrediccionesArmamento(25);

        $this->assertCount(1, $result);
        $this->assertSame('SR-100', $result[0]['serie_id']);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:8002/predictions/armamento?limit=25'
                && $request->method() === 'GET';
        });
    }

    public function test_lista_predicciones_de_conflicto_desde_data(): void
    {
        config()->set('services.prediccion_api.url', 'http://127.0.0.1:8002');
        config()->set('services.prediccion_api.timeout', 30);

        Http::fake([
            'http://127.0.0.1:8002/predictions/conflicto*' => Http::response([
                'data' => [
                    [
                        'id' => 9,
                        'unidad_id' => 3,
                        'nivel_conflicto_predicho' => 'medio',
                        'probabilidad' => 0.65,
                        'total_incidencias_base' => 12,
                        'armas_observadas' => 4,
                        'recomendacion' => 'Refuerzo preventivo',
                        'fecha_prediccion' => '2026-03-27T11:00:00Z',
                        'modelo_version' => 'v2',
                    ],
                ],
            ], 200),
        ]);

        $service = app(PrediccionApiService::class);
        $result = $service->listarPrediccionesConflicto();

        $this->assertCount(1, $result);
        $this->assertSame(3, $result[0]['unidad_id']);
    }

    public function test_lanza_error_claro_cuando_falla_la_api(): void
    {
        config()->set('services.prediccion_api.url', 'http://127.0.0.1:8002');
        config()->set('services.prediccion_api.timeout', 30);

        Http::fake([
            'http://127.0.0.1:8002/health' => Http::response([
                'message' => 'Servicio no disponible',
            ], 503),
        ]);

        $service = app(PrediccionApiService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error HTTP 503: Servicio no disponible');

        $service->health();
    }
}
