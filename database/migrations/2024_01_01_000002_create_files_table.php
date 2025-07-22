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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('original_name');
            $table->string('path');
            $table->string('disk')->default('local');
            $table->bigInteger('size');
            $table->string('mime_type');
            $table->string('extension');
            $table->enum('category', ['document', 'image', 'video', 'audio', 'archive', 'other'])->default('other');
            $table->json('tags')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_public')->default(false);
            $table->enum('access_level', ['public', 'internal', 'restricted', 'confidential'])->default('internal');
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->string('thumbnail_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['uploaded_by', 'is_public']);
            $table->index(['category', 'access_level']);
            $table->index('mime_type');
            $table->fullText(['original_name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
