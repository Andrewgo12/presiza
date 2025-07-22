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
        Schema::create('evidence_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_id')->constrained()->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating')->nullable();
            $table->text('comment');
            $table->enum('recommendation', ['approve', 'reject', 'needs_revision', 'escalate']);
            $table->timestamps();
            
            // Índices
            $table->index(['evidence_id', 'created_at']);
            $table->index('evaluator_id');
            $table->index('recommendation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence_evaluations');
    }
};
