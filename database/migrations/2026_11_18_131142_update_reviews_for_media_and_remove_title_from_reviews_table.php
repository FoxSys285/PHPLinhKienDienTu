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
        DB::table('reviews')
            ->select(['id', 'title', 'body'])
            ->whereNotNull('title')
            ->orderBy('id')
            ->chunkById(100, function (Collection $reviewRows): void {
                foreach ($reviewRows as $reviewRow) {
                    $title = trim((string) $reviewRow->title);

                    if ($title === '') {
                        continue;
                    }

                    $body = trim((string) $reviewRow->body);

                    DB::table('reviews')
                        ->where('id', $reviewRow->id)
                        ->update(['body' => $body === '' ? $title : $title."\n\n".$body]);
                }
            });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropColumn('title');
            $table->string('image_url', 2048)->nullable();
            $table->string('video_url', 2048)->nullable();
        });
    }

    /**
     * Restore the legacy columns; merged title text stays in the body and media URLs are removed.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->string('title')->nullable();
            $table->dropColumn(['image_url', 'video_url']);
        });
    }
};
