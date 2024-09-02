<?php

namespace App\Http\Controllers;

use App\Models\Pokemon;
use Illuminate\Http\JsonResponse;

class PokemonController extends Controller
{
    public function getPokemon(){
        return response(Pokemon::all());
    }

    public function getPokemonById(Pokemon $pokemon): JsonResponse
    {
        return response()->json($pokemon);
    }

    public function getPokemonByName(Pokemon $pokemon): JsonResponse
    {
        return response()->json($pokemon);
    }
}
