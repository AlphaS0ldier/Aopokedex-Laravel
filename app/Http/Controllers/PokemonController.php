<?php

namespace App\Http\Controllers;

use App\Models\Pokemon;
use Illuminate\Http\JsonResponse;

class PokemonController extends Controller
{
    public static function getPokemon(){
        return response(Pokemon::all());
    }

    public static function getPokemonById(Pokemon $pokemon)
    {
        return response($pokemon);
    }

    public static function getPokemonByName(Pokemon $pokemon)
    {
        return response($pokemon);
    }
}
