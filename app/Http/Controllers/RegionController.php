<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pokemon;

use App\Models\Region;

class RegionController extends Controller
{
    public static function getPokemonByRegion(Region $region)
    {

        $response = $region->pokemons->sortBy('region.pokedex_number');

        return response($response);
    }
}
