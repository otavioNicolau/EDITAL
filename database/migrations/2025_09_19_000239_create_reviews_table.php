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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_item_id')->constrained()->onDelete('cascade');
            $table->integer('grade');
            $table->decimal('ease_before', 3, 2)->nullable();
            $table->decimal('ease_after', 3, 2)->nullable();
            $table->integer('interval_before')->default(0);
            $table->integer('interval_after')->default(0);
            $table->timestamp('due_before')->nullable();
            $table->timestamp('due_after')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
