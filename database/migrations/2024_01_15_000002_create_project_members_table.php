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
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['project_manager', 'senior_developer', 'developer', 'designer', 'tester', 'analyst'])
                  ->default('developer');
            $table->decimal('hourly_rate', 8, 2)->default(0);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            // Unique constraint
            $table->unique(['project_id', 'user_id']);
            
            // Indexes
            $table->index(['project_id', 'role']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
