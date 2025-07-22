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
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['member', 'lead', 'manager', 'observer'])
                  ->default('member');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->boolean('can_log_time')->default(true);
            $table->boolean('can_view_reports')->default(false);
            $table->boolean('can_manage_milestones')->default(false);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            // Unique constraint
            $table->unique(['project_id', 'user_id']);
            
            // Indexes
            $table->index(['project_id', 'role']);
            $table->index(['user_id', 'role']);
            $table->index('joined_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};
