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

    public function test_obtiene_resumen_general_filtrado_por_unidad(): void
    {
        config()->set('services.prediccion_api.url', 'http://127.0.0.1:8002');
        config()->set('services.prediccion_api.timeout', 30);

        Http::fake([
            'http://127.0.0.1:8002/predictions/armamento/summary*' => Http::response([
                'unidad_id' => 82,
                'total' => 1200,
                'riesgo' => ['alto' => 10, 'medio' => 90, 'bajo' => 1100],
                'estado' => ['operativo' => 1160, 'inoperativo' => 40],
                'page' => 2,
                'per_page' => 10,
                'last_page' => 120,
                'items' => [],
            ], 200),
        ]);

        $result = app(PrediccionApiService::class)
            ->resumenPrediccionesArmamento(82, 2, 10);

        $this->assertSame(1200, $result['total']);
        $this->assertSame(40, $result['estado']['inoperativo']);

        Http::assertSent(fn ($request) => $request->url()
            === 'http://127.0.0.1:8002/predictions/armamento/summary?unidad_id=82&page=2&per_page=10');
    }

    public function test_obtiene_explicabilidad_global_shap(): void
    {
        config()->set('services.prediccion_api.url', 'http://127.0.0.1:8002');

        Http::fake([
            'http://127.0.0.1:8002/explainability/armamento/global*' => Http::response([
                'unidad_id' => 82,
                'total_records' => 326,
                'sample_size' => 200,
                'importance' => [],
            ], 200),
        ]);

        $result = app(PrediccionApiService::class)
            ->explicabilidadGlobalArmamento(82, 200);

        $this->assertSame(326, $result['total_records']);

        Http::assertSent(fn ($request) => $request->url()
            === 'http://127.0.0.1:8002/explainability/armamento/global?unidad_id=82&sample_size=200');
    }

    public function test_obtiene_explicacion_individual_shap(): void
    {
        config()->set('services.prediccion_api.url', 'http://127.0.0.1:8002');

        Http::fake([
            'http://127.0.0.1:8002/explainability/armamento/41556' => Http::response([
                'serie_id' => 41556,
                'codigo_serie' => 'TEST-33-A052-00018',
                'contributions' => [],
            ], 200),
        ]);

        $result = app(PrediccionApiService::class)
            ->explicarSerieArmamento(41556);

        $this->assertSame(41556, $result['serie_id']);
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
