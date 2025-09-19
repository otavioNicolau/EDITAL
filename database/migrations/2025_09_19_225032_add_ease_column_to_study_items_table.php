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
        if (! Schema::hasColumn('study_items', 'ease')) {
            Schema::table('study_items', function (Blueprint $table) {
                $table->decimal('ease', 3, 2)->default(2.50);
            });

            DB::table('study_items')->update(['ease' => 2.50]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank to avoid dropping the column when it already existed.
    }
};
