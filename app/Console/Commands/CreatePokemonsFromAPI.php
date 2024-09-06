<?php

namespace App\Console\Commands;

use App\Models\Pokemon;
use App\Models\Region;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class CreatePokemonsFromAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-pokemons-from-a-p-i {limit}';

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

        $regions = ["national", "kanto", "original-johto", "hoenn"];

        $limit = $this->argument('limit');

        $response = Http::get(
            'https://pokeapi.co/api/v2/pokemon',
            [
                'limit' => $limit,
            ]
        );


        if ($response->successful()) {

            $pokemons = ($response->json())['results'];

            foreach ($pokemons as $data) {
                $pokemon = [];

                if (Pokemon::where('name', $data['name'])->exists()) {
                    echo $data['name'] . " exists" . PHP_EOL;
                    continue;
                }

                $pokemon_data = getDataFromPokeApi($data['url']);

                $pokemon_extra_data = getDataFromPokeApi($pokemon_data["species"]["url"]);

                $pokemon["name"] = $data['name'];

                foreach ($pokemon_extra_data['flavor_text_entries'] as $pokedex_entry) {
                    if ($pokedex_entry["version"]["name"] == "emerald") {
                        $pokemon["pokedex_entry"] = $pokedex_entry["flavor_text"];
                    }
                }

                foreach ($pokemon_data["stats"] as $stat) {
                    $pokemon[$stat["stat"]["name"]] = $stat["base_stat"];
                }

                $pokemon = Pokemon::create($pokemon);

                foreach ($pokemon_extra_data["pokedex_numbers"] as $pokedex_number) {

                    $region_name = $pokedex_number["pokedex"]["name"];

                    if (in_array($region_name, $regions)) {

                        $region = Region::where('name', $region_name);

                        if (!($region->exists())) {

                            $region = Region::create(["name" => $region_name]);
                        } else {

                            $region = $region->first();
                        }

                        $region->pokemons()->attach(
                            $pokemon->id,
                            ["pokedex_number" => $pokedex_number["entry_number"]]
                        );

                        if ($region->name == "national") {

                            $sprite = sprintf('%04d', $pokedex_number["entry_number"]) . " " . ucfirst($pokemon->name) . ".png";

                            echo $sprite . PHP_EOL;

                            if (Storage::disk('sprites')->exists($sprite)) {

                                $pokemon->update(["sprite" => Storage::disk('sprites')->get($sprite)]);
                                $pokemon->save();
                            }
                        }
                    }
                }
            }
        } else {
            logger()->error('API call failed', ['status' => $response->status(), 'body' => $response->body()]);
        }
    }
}
