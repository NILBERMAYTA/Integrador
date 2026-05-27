<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeteccionApiRouteTest extends TestCase
{
    public function test_envia_imagen_a_api_de_deteccion_configurada(): void
    {
        config()->set('services.deteccion_api.url', 'http://127.0.0.1:8001');
        config()->set('services.deteccion_api.timeout', 30);

        Http::fake([
            'http://127.0.0.1:8001/detect' => Http::response([
                'summary' => ['pistola' => 1],
                'detections' => [
                    ['label' => 'pistola', 'confidence' => 0.93],
                ],
                'processing_time' => 0.12,
            ], 200),
        ]);

        $response = $this->postJson('/api/detecciones', [
            'imagen' => $this->fakePng(),
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('summary.pistola', 1);

        Http::assertSent(fn ($request) => $request->url() === 'http://127.0.0.1:8001/detect'
            && $request->method() === 'POST');
    }

    public function test_devuelve_error_claro_si_falla_api_de_deteccion(): void
    {
        config()->set('services.deteccion_api.url', 'http://127.0.0.1:8001');

        Http::fake([
            'http://127.0.0.1:8001/detect' => Http::response([
                'detail' => 'No se pudo leer la imagen',
            ], 400),
        ]);

        $response = $this->postJson('/api/detecciones', [
            'imagen' => $this->fakePng(),
        ]);

        $response
            ->assertStatus(400)
            ->assertJsonPath('message', 'Error en la API de deteccion.')
            ->assertJsonPath('detail', 'No se pudo leer la imagen');
    }

    private function fakePng(): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        );

        return UploadedFile::fake()->createWithContent('frame.png', $png);
    }
}
