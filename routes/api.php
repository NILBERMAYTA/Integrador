<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::post('/detecciones', function (Request $request) {
    $request->validate([
        'imagen' => 'required|image|max:5120',
    ]);

    $file = $request->file('imagen');

    $response = Http::attach(
        'file',
        file_get_contents($file->getRealPath()),
        $file->getClientOriginalName()
    )->post('http://127.0.0.1:8001/detect'); 

    if ($response->failed()) {
        return response()->json(['message' => 'Error YOLO'], 500);
    }

    return $response->json(); 
})->name('api.detecciones');
