<?php

namespace App\Console\Commands;

use App\Models\Ability;
use App\Models\Pokemon;
use App\Models\Region;
use App\Models\Type;
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

        $url = env("POKEMON_URL_GENERATION");
        $num_generation = env("POKEMON_GENERATION");
        $last_generation = getDataFromPokeApi($url)["count"];
        $main_generation = getDataFromPokeApi($url . $num_generation)["main_region"]["name"];

        if ($last_generation > $num_generation) {
            $data = getDataFromPokeApi($url . ($num_generation + 1))["pokemon_species"][0];
            $limit = getDataFromPokeApi($data["url"])["id"] - 1;
        } else {
            $limit = getDataFromPokeApi("https://pokeapi.co/api/v2/pokemon-species")["count"];
        }

        $games = getDataFromPokeApi($url . $num_generation)["version_groups"];

        $options = [];

        foreach ($games as $game) {
            $game_name = $game["name"];
            $game_data = getDataFromPokeApi($game["url"])["pokedexes"];
            if (!empty($game_data)) {
                $options[] = $game_name;
            }
        }

        $game = $this->choice(
            'Please select a game',
            $options,
            0
        );

        //$game = "emerald";

        $this->info('You selected: ' . $game);

        $pokedexes = ["national"];

        $pokedexes_data = getDataFromPokeApi($url)["results"];

        foreach ($pokedexes_data as $key => $data) {
            if ($num_generation > $key) {
                $region = getDataFromPokeApi($data["url"]);
                if (($key + 1) == $num_generation) {
                    $versions = $region["version_groups"];

                    foreach ($versions as $version) {

                        if ($version["name"] == $game) {
                            $pokedex_name = getDataFromPokeApi($version["url"])["pokedexes"][0]["name"];
                            break;
                        }
                    }
                } else {
                    $main_game = $region["version_groups"][0];
                    $pokedex_name = getDataFromPokeApi($main_game["url"])["pokedexes"][0]["name"];
                }

                $pokedexes[] = $pokedex_name;

            } else {
                break;
            }
        }

        dd($pokedexes);

        dd($options);

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

                $pokemon_name = $pokemon_extra_data["species"]["name"];

                $pokemon["name"] = $pokemon_name;

                foreach ($pokemon_extra_data['genera'] as $text) {
                    if ($text["language"]["name"] == "en") {
                        $pokemon["specie"] = $text['genus'];
                    }
                }

                foreach ($pokemon_extra_data['flavor_text_entries'] as $pokedex_entry) {
                    if ($pokedex_entry["version"]["name"] == $game) {
                        $pokemon["pokedex_entry"] = $pokedex_entry["flavor_text"];
                        break;
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

                            $sprite = sprintf('%04d', $pokedex_number["entry_number"]) . " " . $pokemon_name . ".png";

                            if (Storage::disk('sprites')->exists($sprite)) {
                                $pokemon->update(["sprite" => Storage::disk('sprites')->path($sprite)]);
                                $pokemon->save();
                            }
                        }
                    }
                }

                if (empty($pokemon_data["past_types"])) {
                    $pokemon_types = $pokemon_data["types"];
                } else {
                    $pokemon_types = $pokemon_data["past_types"]["types"];
                }

                foreach ($pokemon_types as $type) {
                    $type_name = $type["type"]["name"];
                    $type = Type::where('name', $type_name);
                    if (!($type->exists())) {
                        $type = Type::create(["name" => $type_name]);
                    } else {
                        $type = $type->first();
                    }

                    $type->pokemons()->attach(
                        $pokemon->id
                    );
                }

                foreach ($pokemon_data["abilities"] as $ability) {


                    $ability_data = getDataFromPokeApi($ability["ability"]["url"]);

                    foreach ($ability_data['flavor_text_entries'] as $text) {
                        if ($text["version_group"]["name"] == $game && $text["language"]["name"] == "en") {
                            $ability_flavour_text = $text["flavor_text"];
                            break;
                        }
                    }

                    if (!empty($ability_flavour_text)) {
                        foreach ($ability_data['effect_entries'] as $text) {
                            if ($text["language"]["name"] == "en") {
                                $ability_effect_entry_full = $text["effect"];
                                $ability_effect_entry_short = $text["short_effect"];
                                break;
                            }
                        }

                        $ability_name = $ability_data["name"];

                        $ability = Ability::where('name', $ability_name);

                        if (!$ability->exists()) {
                            $ability = Ability::create(
                                [
                                    "name" => $ability_name,
                                    "flavour_text" => $ability_flavour_text,
                                    "effect_full" => $ability_effect_entry_full,
                                    "effect_short" => $ability_effect_entry_short
                                ]
                            );
                        } else {
                            $ability = $ability->first();
                        }
                    }
                }

                echo "Created " . $pokemon_name . PHP_EOL;
            }
        } else {
            logger()->error('API call failed', ['status' => $response->status(), 'body' => $response->body()]);
        }
    }
}
