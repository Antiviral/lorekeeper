<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPronounsToUserProfilesTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->text('pronouns', 50)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('pronouns');
        });
    }
}
