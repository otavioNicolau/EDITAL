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
            $table->decimal('ease_before', 3, 2);
            $table->decimal('ease_after', 3, 2);
            $table->integer('interval_before');
            $table->integer('interval_after');
            $table->timestamp('due_before');
            $table->timestamp('due_after');
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
