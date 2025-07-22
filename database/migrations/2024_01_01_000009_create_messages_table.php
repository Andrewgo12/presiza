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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('content');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['direct', 'group', 'system'])->default('direct');
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['sender_id', 'created_at']);
            $table->index(['group_id', 'created_at']);
            $table->index('type');
            $table->index('priority');
            $table->fullText(['subject', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
