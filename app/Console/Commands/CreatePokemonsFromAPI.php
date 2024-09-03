<?php

namespace App\Console\Commands;

use App\Models\Pokemon;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\Http;

class CreatePokemonsFromAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-pokemons-from-a-p-i';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill the database with pokemons';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::get(
            'https://pokeapi.co/api/v2/pokemon',
            [
                'limit' => '386',
            ]
        );


        if ($response->successful()) {
            $pokemons = collect(($response->json())['results']);

            foreach ($pokemons as $pokemon) {
                $exists = Pokemon::where('name', $pokemon['name'])->exists();
                if ($exists) {
                    echo "exists";
                    continue;
                }
                break;
            }
        } else {
            logger()->error('API call failed', ['status' => $response->status(), 'body' => $response->body()]);
        }
    }
}
