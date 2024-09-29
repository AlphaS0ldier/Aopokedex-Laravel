<?php

namespace App\Http\Controllers;

use App\Models\Pokemon;

class PokemonController extends Controller
{

    private static function showPokemonExtraDataAll($pokemons)
    {


        foreach ($pokemons as $pokemon) {
            $pokemon->regions;
            $pokemon->types;
        }
    }


    private static function showPokemonExtraData($pokemon)
    {
        $pokemon->regions;
        $pokemon->types;
    }

    public static function getPokemon()
    {
        $pokemons = Pokemon::all();
        PokemonController::showPokemonExtraDataAll($pokemons);
        return response($pokemons);
    }

    public static function getPokemonByName(Pokemon $pokemon)
    {
        PokemonController::showPokemonExtraData($pokemon);
        return response($pokemon);
    }

    public static function getPokemonSpriteByName(Pokemon $pokemon)
    {
        return response()->file($pokemon->sprite);
    }
}
