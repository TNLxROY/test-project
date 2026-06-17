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
        Schema::create('rulesets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->unsignedInteger('rawg_id');
        $table->string('game_name');
        $table->string('game_image')->nullable();
        $table->string('title', 120);
        $table->string('description', 1000);
        $table->json('rules');             // ["Rule one", "Rule two", …]
        $table->string('mod_url')->nullable();
        $table->boolean('is_public')->default(true);
        $table->timestamps();

        $table->index('rawg_id');
        $table->index(['user_id', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rulesets');
    }
};
