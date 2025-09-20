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

        $columns = DB::select("PRAGMA table_info('reviews')");
        $dueAfterColumn = collect($columns)->firstWhere('name', 'due_after');

        $needsUpdate = $dueAfterColumn && $dueAfterColumn->notnull === 1;

        if (! $needsUpdate) {
            return;
        }

        Schema::rename('reviews', 'reviews_tmp');

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

        $reviews = DB::table('reviews_tmp')->get();

        foreach ($reviews as $review) {
            DB::table('reviews')->insert((array) $review);
        }

        $maxId = DB::table('reviews')->max('id');
        if ($maxId) {
            DB::statement("INSERT OR REPLACE INTO sqlite_sequence(name, seq) VALUES ('reviews', ?)", [$maxId]);
        }

        Schema::drop('reviews_tmp');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no-op; this migration only relaxes constraints
    }
};
