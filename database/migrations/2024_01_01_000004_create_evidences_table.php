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
        Schema::create('evidences', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['security', 'investigation', 'compliance', 'audit', 'incident', 'other'])->default('other');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'archived'])->default('pending');
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamp('incident_date')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'priority']);
            $table->index(['submitted_by', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('category');
            $table->fullText(['title', 'description']);
        });

        Schema::create('evidence_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_id')->constrained()->onDelete('cascade');
            $table->foreignId('file_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->unique(['evidence_id', 'file_id']);
            $table->index(['evidence_id', 'order']);
        });

        Schema::create('evidence_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_id')->constrained()->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating')->nullable();
            $table->text('comment')->nullable();
            $table->enum('recommendation', ['approve', 'reject', 'needs_revision', 'escalate'])->nullable();
            $table->timestamps();
            
            $table->index(['evidence_id', 'evaluator_id']);
            $table->index('rating');
        });

        Schema::create('evidence_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['evidence_id', 'created_at']);
            $table->index(['user_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence_history');
        Schema::dropIfExists('evidence_evaluations');
        Schema::dropIfExists('evidence_files');
        Schema::dropIfExists('evidences');
    }
};
