<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('game_id');
            $table->string('game_name');
            $table->unsignedTinyInteger('rating');
            $table->text('body')->nullable();
            $table->json('categories')->nullable();
            $table->boolean('is_detailed')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'game_id']); // 1 review per user per game
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
