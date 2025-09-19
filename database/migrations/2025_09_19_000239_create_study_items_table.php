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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_items');
    }
};
