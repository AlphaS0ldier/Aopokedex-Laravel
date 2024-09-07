<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pokemon;

use App\Models\Region;

class RegionController extends Controller
{
    public static function getPokemonByNationalNumber($num)
    {

        $regions=(Region::where("name", "National")->first())::with("pokemons")->get();

        $response="";

        foreach($regions as $region){
            if($region->name=="national"){
                foreach($region->pokemons as $pokemon){
                    if($pokemon->region->pokedex_number==$num){
                        $response=$pokemon;
                        break;
                    }
                }
                break;
            }
        }

        return response($response);
    }
}
