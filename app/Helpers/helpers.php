<?php

use Illuminate\Support\Facades\Http;

function getDataFromPokeApi($url)
{
    $data = Http::get($url);

    if ($data->successful()) {
        return $data->json();
    } else {
        logger()->error('API call failed', ['status' => $data->status(), 'body' => $data->body()]);
    }
}
