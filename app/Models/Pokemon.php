<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model
{
    use HasFactory;

    protected $fillable = ["name", "specie","pokedex_entry", "hp", "attack", "defense", "special-attack", "special-defense", "speed", "sprite"];

    public function regions()
    {
        return $this->belongsToMany(Region::class)->as("region")->withPivot("pokedex_number")->select("name");
    }

    public function types()
    {
        return $this->belongsToMany(Type::class)->as("type")->select("name","image");
    }

    public function abilities()
    {
        return $this->belongsToMany(Ability::class)->as("ability")->withPivot("hidden")->select("name","flavour_text");
    }

}
