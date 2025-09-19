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
        Schema::table('topics', function (Blueprint $table) {
            if (! Schema::hasColumn('topics', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            if (! Schema::hasColumn('topics', 'tags')) {
                $table->string('tags')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            if (Schema::hasColumn('topics', 'tags')) {
                $table->dropColumn('tags');
            }

            if (Schema::hasColumn('topics', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
