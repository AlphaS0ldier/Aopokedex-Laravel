<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ability extends Model
{
    use HasFactory;

    protected $fillable = ["name", "flavour_text", "effect_full", "effect_short"];

    public function abilities()
    {
        return $this->belongsToMany(Pokemon::class);
    }
}
