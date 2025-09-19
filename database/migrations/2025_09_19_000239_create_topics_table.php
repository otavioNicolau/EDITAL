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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained()->onDelete('cascade');
            $table->foreignId('discipline_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('tags')->nullable();
            $table->integer('weight')->default(1);
            $table->enum('status', ['PLANNED', 'STUDYING', 'REVIEW', 'COMPLETED'])->default('PLANNED');
            $table->timestamp('next_review_at')->nullable();
            $table->decimal('ease_factor', 3, 2)->default(2.50);
            $table->integer('interval_days')->default(1);
            $table->integer('lapses')->default(0);
            $table->timestamps();

            $table->unique(['name', 'block_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
