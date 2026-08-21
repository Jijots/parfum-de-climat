<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fragrance_similarities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fragrance_id')
                  ->constrained('fragrances')
                  ->cascadeOnDelete();

            $table->foreignId('similar_fragrance_id')
                  ->constrained('fragrances')
                  ->cascadeOnDelete();

            // Cosine similarity score: 0.0000 – 1.0000
            $table->decimal('score', 5, 4);

            // Position in the top-10 list (1 = most similar)
            $table->unsignedTinyInteger('rank');

            $table->timestamps();

            // One row per (fragrance, rank) pair — re-runs replace, not duplicate
            $table->unique(['fragrance_id', 'rank']);

            // Fast lookup: "give me all similar fragrances for fragrance #42"
            $table->index(['fragrance_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fragrance_similarities');
    }
};
