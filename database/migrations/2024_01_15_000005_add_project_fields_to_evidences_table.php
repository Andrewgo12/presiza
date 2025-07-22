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
        Schema::table('evidences', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('assigned_to')
                  ->constrained('projects')->onDelete('cascade');
            $table->foreignId('milestone_id')->nullable()->after('project_id')
                  ->constrained('project_milestones')->onDelete('set null');
            $table->integer('progress_percentage')->nullable()->after('milestone_id');
            $table->decimal('time_spent', 8, 2)->nullable()->after('progress_percentage');
            $table->json('tags')->nullable()->after('time_spent');

            // Add indexes
            $table->index(['project_id', 'status']);
            $table->index(['milestone_id', 'status']);
            $table->index('progress_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['milestone_id']);
            $table->dropIndex(['project_id', 'status']);
            $table->dropIndex(['milestone_id', 'status']);
            $table->dropIndex(['progress_percentage']);
            $table->dropColumn(['project_id', 'milestone_id', 'progress_percentage', 'time_spent', 'tags']);
        });
    }
};
