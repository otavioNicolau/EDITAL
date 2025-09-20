<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        $foreignKeys = DB::select('PRAGMA foreign_key_list(reviews)');
        $referencesLegacy = collect($foreignKeys)->contains(function ($fk) {
            return $fk->table === 'study_items_legacy';
        });

        if (! $referencesLegacy) {
            return;
        }

        Schema::rename('reviews', 'reviews_legacy');

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_item_id')->constrained('study_items')->onDelete('cascade');
            $table->integer('grade');
            $table->decimal('ease_before', 3, 2)->nullable();
            $table->decimal('ease_after', 3, 2)->nullable();
            $table->integer('interval_before')->default(0);
            $table->integer('interval_after')->default(0);
            $table->timestamp('due_before')->nullable();
            $table->timestamp('due_after')->nullable();
            $table->timestamps();
        });

        $legacyReviews = DB::table('reviews_legacy')->get();

        foreach ($legacyReviews as $review) {
            DB::table('reviews')->insert((array) $review);
        }

        $maxId = DB::table('reviews')->max('id');
        if ($maxId) {
            DB::statement("INSERT OR REPLACE INTO sqlite_sequence(name, seq) VALUES ('reviews', ?)", [$maxId]);
        }

        Schema::drop('reviews_legacy');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback; this migration only normalises legacy tables.
    }
};
