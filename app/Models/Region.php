<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable=["name"];

    public function pokemons()
    {
        return $this->belongsToMany(Pokemon::class)->as("region")->withPivot("pokedex_number");
    }
}
