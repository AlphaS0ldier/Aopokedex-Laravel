<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ability extends Model
{
    use HasFactory;

    protected $fillable = ["name", "flavour_text"];

    public function pokemons()
    {
        return $this->belongsToMany(Pokemon::class)->as("ability")->withPivot("hidden");
    }
}
