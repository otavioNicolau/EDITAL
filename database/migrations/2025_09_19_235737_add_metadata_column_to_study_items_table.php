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
        if (! Schema::hasColumn('study_items', 'metadata')) {
            Schema::table('study_items', function (Blueprint $table) {
                $table->json('metadata')->nullable()->after('status');
            });

            DB::table('study_items')->update(['metadata' => null]);
        }

        if (! Schema::hasColumn('study_items', 'interval')) {
            Schema::table('study_items', function (Blueprint $table) {
                $table->integer('interval')->default(0)->after('ease');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('study_items', 'metadata')) {
            Schema::table('study_items', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });
        }
    }
};
