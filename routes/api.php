<?php

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::post('/detecciones', function (Request $request) {
    $request->validate([
        'imagen' => 'required|image|max:5120',
    ]);

    $file = $request->file('imagen');

    $baseUrl = rtrim((string) config('services.deteccion_api.url'), '/');
    $timeout = max(1, (int) config('services.deteccion_api.timeout', 30));

    try {
        $response = Http::baseUrl($baseUrl)
            ->timeout($timeout)
            ->acceptJson()
            ->attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )
            ->post('/detect');
    } catch (ConnectionException $exception) {
        return response()->json([
            'message' => 'No se pudo conectar con la API de deteccion.',
            'detail' => $exception->getMessage(),
        ], 503);
    }

    if ($response->failed()) {
        return response()->json([
            'message' => 'Error en la API de deteccion.',
            'detail' => $response->json('detail') ?? $response->json('message') ?? $response->body(),
        ], $response->status() >= 500 ? 502 : $response->status());
    }

    return $response->json();
})->name('api.detecciones');
