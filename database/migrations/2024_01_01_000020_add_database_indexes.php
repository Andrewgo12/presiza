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
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email', 'is_active']);
            $table->index(['role', 'is_active']);
            $table->index(['department', 'is_active']);
            $table->index(['created_at']);
            $table->index(['last_login']);
        });

        // Projects table indexes
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['status', 'priority']);
            $table->index(['created_by']);
            $table->index(['project_manager_id']);
            $table->index(['start_date', 'end_date']);
            $table->index(['created_at']);
            $table->index(['name']); // For search
        });

        // Evidences table indexes
        Schema::table('evidences', function (Blueprint $table) {
            $table->index(['status', 'priority']);
            $table->index(['submitted_by']);
            $table->index(['assigned_to']);
            $table->index(['project_id']);
            $table->index(['category']);
            $table->index(['created_at']);
            $table->index(['case_number']); // For search
            $table->index(['title']); // For search
        });

        // Groups table indexes
        Schema::table('groups', function (Blueprint $table) {
            $table->index(['leader_id']);
            $table->index(['created_at']);
            $table->index(['name']); // For search
        });

        // Project milestones table indexes
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->index(['project_id', 'status']);
            $table->index(['assigned_to']);
            $table->index(['start_date', 'end_date']);
            $table->index(['created_by']);
            $table->index(['created_at']);
        });

        // Time logs table indexes
        Schema::table('time_logs', function (Blueprint $table) {
            $table->index(['user_id', 'date']);
            $table->index(['project_id', 'date']);
            $table->index(['milestone_id']);
            $table->index(['status']);
            $table->index(['is_billable']);
            $table->index(['created_at']);
        });

        // Files table indexes
        Schema::table('files', function (Blueprint $table) {
            $table->index(['fileable_type', 'fileable_id']);
            $table->index(['uploaded_by']);
            $table->index(['file_type']);
            $table->index(['created_at']);
            $table->index(['original_name']); // For search
        });

        // Messages table indexes
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['sender_id']);
            $table->index(['receiver_id']);
            $table->index(['project_id']);
            $table->index(['group_id']);
            $table->index(['is_read']);
            $table->index(['created_at']);
        });

        // Notifications table indexes
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index(['read_at']);
            $table->index(['created_at']);
            $table->index(['type']);
        });

        // Evidence evaluations table indexes
        Schema::table('evidence_evaluations', function (Blueprint $table) {
            $table->index(['evidence_id']);
            $table->index(['evaluator_id']);
            $table->index(['status']);
            $table->index(['created_at']);
        });

        // Evidence history table indexes
        Schema::table('evidence_history', function (Blueprint $table) {
            $table->index(['evidence_id']);
            $table->index(['user_id']);
            $table->index(['action']);
            $table->index(['created_at']);
        });

        // Group members table indexes
        Schema::table('group_members', function (Blueprint $table) {
            $table->index(['group_id', 'user_id']);
            $table->index(['role']);
            $table->index(['joined_at']);
        });

        // Project users table indexes
        Schema::table('project_users', function (Blueprint $table) {
            $table->index(['project_id', 'user_id']);
            $table->index(['role']);
            $table->index(['joined_at']);
        });

        // Activity logs table indexes (if exists)
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->index(['user_id']);
                $table->index(['action']);
                $table->index(['model_type', 'model_id']);
                $table->index(['created_at']);
                $table->index(['ip_address']);
            });
        }

        // Sessions table indexes (if using database sessions)
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->index(['user_id']);
                $table->index(['last_activity']);
            });
        }

        // Failed jobs table indexes (if exists)
        if (Schema::hasTable('failed_jobs')) {
            Schema::table('failed_jobs', function (Blueprint $table) {
                $table->index(['failed_at']);
                $table->index(['queue']);
            });
        }

        // Jobs table indexes (if using database queue)
        if (Schema::hasTable('jobs')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->index(['queue']);
                $table->index(['available_at']);
                $table->index(['created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email', 'is_active']);
            $table->dropIndex(['role', 'is_active']);
            $table->dropIndex(['department', 'is_active']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['last_login']);
        });

        // Projects table indexes
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['status', 'priority']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['project_manager_id']);
            $table->dropIndex(['start_date', 'end_date']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['name']);
        });

        // Evidences table indexes
        Schema::table('evidences', function (Blueprint $table) {
            $table->dropIndex(['status', 'priority']);
            $table->dropIndex(['submitted_by']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['category']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['case_number']);
            $table->dropIndex(['title']);
        });

        // Groups table indexes
        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['leader_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['name']);
        });

        // Project milestones table indexes
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'status']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['start_date', 'end_date']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['created_at']);
        });

        // Time logs table indexes
        Schema::table('time_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'date']);
            $table->dropIndex(['project_id', 'date']);
            $table->dropIndex(['milestone_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['is_billable']);
            $table->dropIndex(['created_at']);
        });

        // Files table indexes
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['fileable_type', 'fileable_id']);
            $table->dropIndex(['uploaded_by']);
            $table->dropIndex(['file_type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['original_name']);
        });

        // Messages table indexes
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['sender_id']);
            $table->dropIndex(['receiver_id']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['group_id']);
            $table->dropIndex(['is_read']);
            $table->dropIndex(['created_at']);
        });

        // Notifications table indexes
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_type', 'notifiable_id']);
            $table->dropIndex(['read_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['type']);
        });

        // Evidence evaluations table indexes
        Schema::table('evidence_evaluations', function (Blueprint $table) {
            $table->dropIndex(['evidence_id']);
            $table->dropIndex(['evaluator_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });

        // Evidence history table indexes
        Schema::table('evidence_history', function (Blueprint $table) {
            $table->dropIndex(['evidence_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['action']);
            $table->dropIndex(['created_at']);
        });

        // Group members table indexes
        Schema::table('group_members', function (Blueprint $table) {
            $table->dropIndex(['group_id', 'user_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['joined_at']);
        });

        // Project users table indexes
        Schema::table('project_users', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'user_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['joined_at']);
        });

        // Activity logs table indexes (if exists)
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
                $table->dropIndex(['action']);
                $table->dropIndex(['model_type', 'model_id']);
                $table->dropIndex(['created_at']);
                $table->dropIndex(['ip_address']);
            });
        }

        // Sessions table indexes (if using database sessions)
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
                $table->dropIndex(['last_activity']);
            });
        }

        // Failed jobs table indexes (if exists)
        if (Schema::hasTable('failed_jobs')) {
            Schema::table('failed_jobs', function (Blueprint $table) {
                $table->dropIndex(['failed_at']);
                $table->dropIndex(['queue']);
            });
        }

        // Jobs table indexes (if using database queue)
        if (Schema::hasTable('jobs')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->dropIndex(['queue']);
                $table->dropIndex(['available_at']);
                $table->dropIndex(['created_at']);
            });
        }
    }
};
