<?php

namespace App\Http\Controllers;

use App\Models\Pokemon;

class PokemonController extends Controller
{
    public static function getPokemon(){
        return response(Pokemon::all());
    }

    public static function getPokemonByName(Pokemon $pokemon)
    {
        return response($pokemon);
    }

    public static function getPokemonSpriteByName(Pokemon $pokemon)
    {
        return response()->file((Pokemon::where('name',$pokemon)->first())->sprite);
    }
}
