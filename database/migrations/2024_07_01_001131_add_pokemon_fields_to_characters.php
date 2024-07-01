<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('characters', function (Blueprint $table) {
            $table->string('pokemon_species', 191)->nullable();
            $table->string('pokemon_types', 191)->nullable();
            $table->string('pokemon_team', 191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('characters', function (Blueprint $table) {
            $table->string('pokemon_species', 191)->nullable();
            $table->string('pokemon_types', 191)->nullable();
            $table->string('pokemon_team', 191)->nullable();
        });
    }
};
