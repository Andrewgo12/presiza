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
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'on_hold'])
                  ->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])
                  ->default('medium');
            $table->integer('order')->default(0);
            $table->integer('progress_percentage')->default(0);
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->json('deliverables')->nullable();
            $table->json('acceptance_criteria')->nullable();
            $table->json('dependencies')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])
                  ->default('low');
            $table->decimal('budget_allocated', 10, 2)->nullable();
            $table->decimal('budget_used', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'order']);
            $table->index(['assigned_to', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('priority');
            $table->index('progress_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
