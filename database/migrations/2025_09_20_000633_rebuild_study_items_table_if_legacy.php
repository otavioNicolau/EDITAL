<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('study_items')) {
            return;
        }

        $tableSql = optional(DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'study_items'"))->sql;

        if (! $tableSql || ! Str::contains($tableSql, "'TO_STUDY'")) {
            return; // already using the new enum set
        }

        Schema::rename('study_items', 'study_items_legacy');

        Schema::create('study_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->enum('kind', ['QUESTION', 'CONCEPT', 'EXERCISE', 'VIDEO', 'ARTICLE']);
            $table->string('url')->nullable();
            $table->text('notes')->nullable();
            $table->string('tags')->nullable();
            $table->enum('status', ['NEW', 'LEARNING', 'REVIEW', 'MASTERED'])->default('NEW');
            $table->json('metadata')->nullable();
            $table->decimal('ease', 3, 2)->default(2.50);
            $table->integer('interval')->default(0);
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
        });

        $legacyItems = DB::table('study_items_legacy')->get();

        $statusMap = [
            'TO_STUDY' => 'NEW',
            'PENDING' => 'LEARNING',
            'COMPLETED' => 'MASTERED',
            'REVIEWING' => 'REVIEW',
        ];

        $kindMap = [
            'SUMMARY' => 'CONCEPT',
            'QUESTION' => 'QUESTION',
            'LAW' => 'ARTICLE',
            'VIDEO' => 'VIDEO',
            'OTHER' => 'EXERCISE',
        ];

        foreach ($legacyItems as $item) {
            DB::table('study_items')->insert([
                'id' => $item->id,
                'topic_id' => $item->topic_id,
                'title' => $item->title,
                'kind' => $kindMap[$item->kind] ?? 'CONCEPT',
                'url' => $item->url,
                'notes' => $item->notes,
                'tags' => $item->tags,
                'status' => $statusMap[$item->status] ?? 'NEW',
                'metadata' => $item->metadata,
                'ease' => $item->ease ?? 2.5,
                'interval' => $item->interval ?? 0,
                'due_at' => $item->due_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
        }

        $maxId = DB::table('study_items')->max('id');
        if ($maxId) {
            DB::statement("INSERT OR REPLACE INTO sqlite_sequence(name, seq) VALUES ('study_items', ?)", [$maxId]);
        }

        Schema::drop('study_items_legacy');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // intentionally left blank; this migration only normalises legacy tables
    }
};
