<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bio', 500)->nullable()->after('avatar');
            $table->unsignedBigInteger('favourite_game_id')->nullable()->after('bio');
            $table->string('favourite_game_name')->nullable()->after('favourite_game_id');
            $table->string('favourite_game_cover')->nullable()->after('favourite_game_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio',
                'favourite_game_id',
                'favourite_game_name',
                'favourite_game_cover',
            ]);
        });
    }
};
