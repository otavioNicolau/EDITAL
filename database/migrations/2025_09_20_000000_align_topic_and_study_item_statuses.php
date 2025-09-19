<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $topicStatusMap = [
            'NEW' => 'PLANNED',
            'LEARNING' => 'STUDYING',
            'REVIEW' => 'REVIEW',
            'MATURE' => 'COMPLETED',
        ];

        foreach ($topicStatusMap as $old => $new) {
            DB::table('topics')->where('status', $old)->update(['status' => $new]);
        }

        DB::table('topics')->whereNull('status')->update(['status' => 'PLANNED']);

        $studyItemStatusMap = [
            'TO_STUDY' => 'NEW',
            'PENDING' => 'LEARNING',
            'COMPLETED' => 'MASTERED',
            'REVIEWING' => 'REVIEW',
        ];

        foreach ($studyItemStatusMap as $old => $new) {
            DB::table('study_items')->where('status', $old)->update(['status' => $new]);
        }

        DB::table('study_items')->whereNull('status')->update(['status' => 'NEW']);
    }

    public function down(): void
    {
        $topicStatusMap = [
            'PLANNED' => 'NEW',
            'STUDYING' => 'LEARNING',
            'REVIEW' => 'REVIEW',
            'COMPLETED' => 'MATURE',
        ];

        foreach ($topicStatusMap as $new => $old) {
            DB::table('topics')->where('status', $new)->update(['status' => $old]);
        }

        $studyItemStatusMap = [
            'NEW' => 'TO_STUDY',
            'LEARNING' => 'PENDING',
            'MASTERED' => 'COMPLETED',
            'REVIEW' => 'REVIEWING',
        ];

        foreach ($studyItemStatusMap as $new => $old) {
            DB::table('study_items')->where('status', $new)->update(['status' => $old]);
        }
    }
};
