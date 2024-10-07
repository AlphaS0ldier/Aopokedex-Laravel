<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public static function getTypeImageByName(Type $type)
    {
        return response()->file($type->image);
    }
}
