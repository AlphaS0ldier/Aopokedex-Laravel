<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PokemonController;
use App\Http\Controllers\RegionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('pokemon', [PokemonController::class, 'getPokemon']);

Route::get('pokemon/national_number/{num}', [RegionController::class, 'getPokemonByNationalNumber']);

Route::get('pokemon/name/{pokemon:name}', [PokemonController::class, 'getPokemonByName']);

Route::get('pokemon/sprite/{pokemon:name}', [PokemonController::class, 'getPokemonSpriteByName']);
