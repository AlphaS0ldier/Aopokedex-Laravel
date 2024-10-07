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
            $game_data = getDataFromPokeApi($game["url"]);
            $pokedex = collect($game_data["pokedexes"]);
            $game_region = collect($game_data["regions"]);
            if (!$pokedex->isEmpty() && $game_region->contains("name", $main_generation)) {
                foreach ($game_data["versions"] as $version) {
                    if (
                        !($pokedex->filter(function ($item) use ($main_generation) {
                            return str_contains($item['name'], $main_generation);
                        })->isEmpty())
                    ) {
                        $options[] = $version["name"];
                    }
                }
            }
        }
        $game = $this->choice(
            'Please select a game',
            $options,
            0
        );

        //$game = "ultra-sun";

        $this->info('You selected: ' . $game);

        $pokedexes = [getDataFromPokeApi("https://pokeapi.co/api/v2/pokedex")["results"][0]["name"]];

        $pokedexes_data = getDataFromPokeApi($url)["results"];

        foreach ($pokedexes_data as $key => $data) {
            $pokedex_name = "";
            if ($num_generation > $key) {

                $region = getDataFromPokeApi($data["url"]);

                $games_by_region = collect($region["version_groups"]);

                $pokedex = $games_by_region->filter(function ($item) use ($game) {
                    return str_contains($item['name'], $game);
                });
                if ($key == 8) {
                    foreach ($region["version_groups"] as $item) {
                        $pokedexes[] = getDataFromPokeApi($item["url"])["pokedexes"][0]["name"];
                    }
                } else if ($key == 5 || $key > 6) {
                    foreach (getDataFromPokeApi(($region["version_groups"][0])["url"])["pokedexes"] as $item) {
                        $pokedexes[] = $item["name"];
                    }
                } elseif ($key < 5 || $key == 6) {
                    if (!$pokedex->isEmpty() && $region["main_region"]["name"] == $main_generation) {
                        $pokedex_name = getDataFromPokeApi(($pokedex->first())["url"])["pokedexes"][0]["name"];
                    } else {
                        $pokedex_name = getDataFromPokeApi(($games_by_region->first())["url"])["pokedexes"][0]["name"];
                    }
                    $pokedexes[] = $pokedex_name;
                }
            } else {
                break;
            }
        }

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

                $pokemon_name = $pokemon_data["species"]["name"];

                $pokemon["name"] = $pokemon_name;

                foreach ($pokemon_extra_data['genera'] as $text) {
                    if ($text["language"]["name"] == "en") {
                        $pokemon["specie"] = $text['genus'];
                    }
                }

                $pokemon_description = "";

                $collection_flavor_texts = collect($pokemon_extra_data['flavor_text_entries'])->where("language.name", "en");

                foreach ($collection_flavor_texts as $pokedex_entry) {
                    if ($pokedex_entry["version"]["name"] == $game) {
                        $pokemon_description = $pokedex_entry["flavor_text"];
                    }
                }

                if (empty($pokemon_description)) {
                    $pokemon_description = ($collection_flavor_texts->last())["flavor_text"];
                }

                $pokemon["pokedex_entry"] = $pokemon_description;

                foreach ($pokemon_data["stats"] as $stat) {
                    $pokemon[$stat["stat"]["name"]] = $stat["base_stat"];
                }

                $pokemon = Pokemon::create($pokemon);

                foreach ($pokemon_extra_data["pokedex_numbers"] as $pokedex_number) {

                    $region_name = $pokedex_number["pokedex"]["name"];

                    if (in_array($region_name, $pokedexes)) {

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

                        if ($region->name == $pokedexes[0]) {

                            $sprite = $pokedex_number["entry_number"] . ".png";

                            if (Storage::disk('sprites')->exists($sprite)) {
                                $pokemon->update(["sprite" => Storage::disk('sprites')->path($sprite)]);
                                $pokemon->save();
                            }
                        }
                    }
                }


                if (
                    !empty($pokemon_data["past_types"])
                    &&
                    $num_generation
                    <=
                    getDataFromPokeApi($pokemon_data["past_types"][0]["generation"]["url"])["id"]
                ) {
                    $pokemon_types = $pokemon_data["past_types"][0]["types"];
                } else {
                    $pokemon_types = $pokemon_data["types"];
                }


                foreach ($pokemon_types as $type) {
                    $type_name = $type["type"]["name"];
                    $type = Type::where('name', $type_name);
                    if (!($type->exists())) {

                        $image = "";

                        if (Storage::disk('types')->exists($image)) {
                            $image = Storage::disk('types')->path($type_name . ".png");
                        }

                        $type = Type::create(["name" => $type_name, "image" => $image]);
                    } else {
                        $type = $type->first();
                    }

                    $type->pokemons()->attach(
                        $pokemon->id
                    );
                }

                foreach ($pokemon_data["abilities"] as $ability) {

                    $ability_data = getDataFromPokeApi($ability["ability"]["url"]);

                    $ability_flavour_text = "";

                    $collection_flavor_texts = collect($ability_data['flavor_text_entries'])->where("language.name", "en");

                    foreach ($collection_flavor_texts as $text) {
                        if ($text["version_group"]["name"] == $game) {
                            $ability_flavour_text = $text["flavor_text"];
                            break;
                        }
                    }

                    if (empty($ability_flavour_text)) {
                        $ability_flavour_text = ($collection_flavor_texts->last())["flavor_text"];
                    }

                    $ability_name = $ability_data["name"];

                    $hidden = $ability["is_hidden"];

                    $ability = Ability::where('name', $ability_name);

                    if (!$ability->exists()) {
                        $ability = Ability::create(
                            [
                                "name" => $ability_name,
                                "flavour_text" => $ability_flavour_text,
                            ]
                        );
                    } else {
                        $ability = $ability->first();
                    }

                    $ability->pokemons()->attach(
                        $pokemon->id,
                        ["hidden" => $hidden]
                    );
                }

                echo "Created " . $pokemon_name . PHP_EOL;
            }
        } else {
            logger()->error('API call failed', ['status' => $response->status(), 'body' => $response->body()]);
        }
    }
}
