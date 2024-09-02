<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PokemonController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('pokemon', [PokemonController::class, 'getPokemon']);

Route::get('pokemon/id/{pokemon}', [PokemonController::class, 'getPokemonById']);

Route::get('pokemon/name/{pokemon:name}', [PokemonController::class, 'getPokemonByName']);
