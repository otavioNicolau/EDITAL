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
            $table->enum('kind', ['SUMMARY', 'QUESTION', 'LAW', 'VIDEO', 'OTHER']);
            $table->string('url')->nullable();
            $table->text('notes')->nullable();
            $table->string('tags')->nullable();
            $table->enum('status', ['TO_STUDY', 'PENDING', 'COMPLETED', 'REVIEWING'])->default('TO_STUDY');
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
