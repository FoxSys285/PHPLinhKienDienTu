<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->json('images')->nullable();
        });

        DB::table('reviews')
            ->select(['id', 'image_url'])
            ->whereNotNull('image_url')
            ->where('image_url', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function (Collection $reviewRows): void {
                foreach ($reviewRows as $reviewRow) {
                    DB::table('reviews')
                        ->where('id', $reviewRow->id)
                        ->update(['images' => json_encode([$reviewRow->image_url], JSON_THROW_ON_ERROR)]);
                }
            });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropColumn('image_url');
        });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->decimal('rating', 2, 1)->change();
        });
    }

    /**
     * Restore the prior image column from the first image; extra images are discarded and rating stays decimal.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->string('image_url', 2048)->nullable();
        });

        DB::table('reviews')
            ->select(['id', 'images'])
            ->whereNotNull('images')
            ->orderBy('id')
            ->chunkById(100, function (Collection $reviewRows): void {
                foreach ($reviewRows as $reviewRow) {
                    $images = json_decode((string) $reviewRow->images, true, 512, JSON_THROW_ON_ERROR);
                    $firstImage = is_array($images) && is_string($images[0] ?? null) ? $images[0] : null;

                    DB::table('reviews')
                        ->where('id', $reviewRow->id)
                        ->update(['image_url' => $firstImage]);
                }
            });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropColumn('images');
        });
    }
};
