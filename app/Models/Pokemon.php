<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model
{
    use HasFactory;

    protected $fillable = ["name", "pokedex_entry", "hp", "attack", "defense", "special-attack", "special-defense", "speed", "sprite"];

    protected $casts = [
        'sprite' => 'binary',
    ];

    public function regions()
    {
        return $this->belongsToMany(Region::class);
    }
}
