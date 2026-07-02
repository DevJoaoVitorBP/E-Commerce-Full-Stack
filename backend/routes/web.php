<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Documentação da API
Route::get('/api/documentation', function () {
    return view('swagger-ui');
});

// Serve a especificação OpenAPI
Route::get('/api/openapi.json', function () {
    return response()->file(public_path('openapi.json'), ['Content-Type' => 'application/json']);
});
