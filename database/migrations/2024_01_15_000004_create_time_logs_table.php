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
        Schema::create('time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('milestone_id')->nullable()->constrained('project_milestones')->onDelete('set null');
            $table->string('task_description');
            $table->decimal('hours', 8, 2);
            $table->date('date');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->decimal('hourly_rate', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'date']);
            $table->index(['project_id', 'date']);
            $table->index(['date', 'is_billable']);
            $table->index('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_logs');
    }
};
