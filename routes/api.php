<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PokemonController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\TypeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('pokemon', [PokemonController::class, 'getPokemon']);

Route::get('pokemon/name/{pokemon:name}', [PokemonController::class, 'getPokemonByName']);

Route::get('pokemon/sprite/{pokemon:name}', [PokemonController::class, 'getPokemonSpriteByName']);

Route::get('pokemon/national_number/{pokemon}', [PokemonController::class, 'getPokemonByNationalNumber']);

Route::get('region/{region:name}', [RegionController::class, 'getPokemonByRegion']);

Route::get('type/image/{type:name}',[TypeController::class,'getTypeImageByName']);
