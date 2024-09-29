<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;

    protected $fillable=["name"];

    public function pokemons()
    {
        return $this->belongsToMany(Pokemon::class)->as("type");
    }

}
