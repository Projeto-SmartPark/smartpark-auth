<?php
use Illuminate\Support\Facades\File;

Route::get('/docs', function () {
    $path = storage_path('api-docs/api-docs.json');
    if (! File::exists($path)) {
        return response()->json(['error' => 'Swagger JSON not found'], 404);
    }
    return response()->file($path);
});