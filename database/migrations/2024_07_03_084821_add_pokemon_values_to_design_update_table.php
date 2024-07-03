<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('design_updates', function (Blueprint $table) {
            $table->string('pokemonSpecies', 191)->nullable();
            $table->string('pokemonTypes', 191)->nullable();
            $table->string('pokemonTeam', 191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('design_updates', function (Blueprint $table) {
            $table->dropColumn('pokemonSpecies', 191)->nullable();
            $table->dropColumn('pokemonTypes', 191)->nullable();
            $table->dropColumn('pokemonTeam', 191)->nullable();
        });
    }
};
