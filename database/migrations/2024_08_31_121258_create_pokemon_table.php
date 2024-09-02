<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pokemon', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            //$table->foreign('regions');
            //$table->foreign('types');
            //$table->foreign('species');
            //$table->foreign('abilities');
            //$table->foreign('evolutions');
            $table->string('sprite')->nullable();
            $table->string('pokedex_entry')->nullable();
            $table->integer('hp')->nullable();
            $table->integer('attack')->nullable();
            $table->integer('defense')->nullable();
            $table->integer('special-attack')->nullable();
            $table->integer('special-defense')->nullable();
            $table->integer('speed')->nullable();
            //$table->foreign('moves');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pokemon');
    }
};
