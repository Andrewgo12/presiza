<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Evidence;
use App\Models\Group;
use App\Models\TimeLog;
use App\Models\ProjectMilestone;
use App\Models\File;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function test_user_relationships()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['created_by' => $user->id]);
        $evidence = Evidence::factory()->create(['submitted_by' => $user->id]);
        $group = Group::factory()->create(['leader_id' => $user->id]);
        $timeLog = TimeLog::factory()->create(['user_id' => $user->id]);

        // Test user has many evidences
        $this->assertTrue($user->evidences->contains($evidence));
        
        // Test user has many projects
        $this->assertTrue($user->createdProjects->contains($project));
        
        // Test user has many time logs
        $this->assertTrue($user->timeLogs->contains($timeLog));
        
        // Test user has many led groups
        $this->assertTrue($user->ledGroups->contains($group));
    }

    /** @test */
    public function test_project_relationships()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'created_by' => $user->id,
            'project_manager_id' => $user->id
        ]);
        $evidence = Evidence::factory()->create(['project_id' => $project->id]);
        $milestone = ProjectMilestone::factory()->create(['project_id' => $project->id]);
        $timeLog = TimeLog::factory()->create(['project_id' => $project->id]);

        // Test project belongs to creator
        $this->assertEquals($user->id, $project->creator->id);
        
        // Test project belongs to project manager
        $this->assertEquals($user->id, $project->projectManager->id);
        
        // Test project has many evidences
        $this->assertTrue($project->evidences->contains($evidence));
        
        // Test project has many milestones
        $this->assertTrue($project->milestones->contains($milestone));
        
        // Test project has many time logs
        $this->assertTrue($project->timeLogs->contains($timeLog));
    }

    /** @test */
    public function test_evidence_relationships()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $evidence = Evidence::factory()->create([
            'submitted_by' => $user->id,
            'assigned_to' => $user->id,
            'project_id' => $project->id
        ]);
        $file = File::factory()->create([
            'fileable_type' => Evidence::class,
            'fileable_id' => $evidence->id
        ]);

        // Test evidence belongs to submitter
        $this->assertEquals($user->id, $evidence->submittedBy->id);
        
        // Test evidence belongs to assigned user
        $this->assertEquals($user->id, $evidence->assignedTo->id);
        
        // Test evidence belongs to project
        $this->assertEquals($project->id, $evidence->project->id);
        
        // Test evidence has many files
        $this->assertTrue($evidence->files->contains($file));
    }

    /** @test */
    public function test_group_relationships()
    {
        $leader = User::factory()->create();
        $member = User::factory()->create();
        $group = Group::factory()->create(['leader_id' => $leader->id]);
        
        // Add member to group
        $group->members()->attach($member->id, [
            'role' => 'member',
            'joined_at' => now()
        ]);

        // Test group belongs to leader
        $this->assertEquals($leader->id, $group->leader->id);
        
        // Test group has many members
        $this->assertTrue($group->members->contains($member));
        
        // Test member belongs to group
        $this->assertTrue($member->groups->contains($group));
    }

    /** @test */
    public function test_time_log_relationships()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $milestone = ProjectMilestone::factory()->create(['project_id' => $project->id]);
        $timeLog = TimeLog::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'milestone_id' => $milestone->id
        ]);

        // Test time log belongs to user
        $this->assertEquals($user->id, $timeLog->user->id);
        
        // Test time log belongs to project
        $this->assertEquals($project->id, $timeLog->project->id);
        
        // Test time log belongs to milestone
        $this->assertEquals($milestone->id, $timeLog->milestone->id);
    }

    /** @test */
    public function test_file_polymorphic_relationships()
    {
        $user = User::factory()->create();
        $evidence = Evidence::factory()->create();
        $project = Project::factory()->create();
        
        $evidenceFile = File::factory()->create([
            'fileable_type' => Evidence::class,
            'fileable_id' => $evidence->id,
            'uploaded_by' => $user->id
        ]);
        
        $projectFile = File::factory()->create([
            'fileable_type' => Project::class,
            'fileable_id' => $project->id,
            'uploaded_by' => $user->id
        ]);

        // Test file belongs to uploader
        $this->assertEquals($user->id, $evidenceFile->uploadedBy->id);
        $this->assertEquals($user->id, $projectFile->uploadedBy->id);
        
        // Test polymorphic relationship
        $this->assertEquals($evidence->id, $evidenceFile->fileable->id);
        $this->assertEquals($project->id, $projectFile->fileable->id);
        
        // Test reverse polymorphic relationship
        $this->assertTrue($evidence->files->contains($evidenceFile));
        $this->assertTrue($project->files->contains($projectFile));
    }

    /** @test */
    public function test_message_relationships()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $project = Project::factory()->create();
        $group = Group::factory()->create();
        
        $directMessage = Message::factory()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id
        ]);
        
        $projectMessage = Message::factory()->create([
            'sender_id' => $sender->id,
            'project_id' => $project->id
        ]);
        
        $groupMessage = Message::factory()->create([
            'sender_id' => $sender->id,
            'group_id' => $group->id
        ]);

        // Test message belongs to sender
        $this->assertEquals($sender->id, $directMessage->sender->id);
        $this->assertEquals($sender->id, $projectMessage->sender->id);
        $this->assertEquals($sender->id, $groupMessage->sender->id);
        
        // Test message belongs to receiver
        $this->assertEquals($receiver->id, $directMessage->receiver->id);
        
        // Test message belongs to project
        $this->assertEquals($project->id, $projectMessage->project->id);
        
        // Test message belongs to group
        $this->assertEquals($group->id, $groupMessage->group->id);
    }

    /** @test */
    public function test_notification_polymorphic_relationships()
    {
        $user = User::factory()->create();
        
        $notification = $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\TestNotification',
            'data' => ['message' => 'Test notification'],
            'read_at' => null
        ]);

        // Test notification belongs to user
        $this->assertEquals($user->id, $notification->notifiable->id);
        
        // Test user has notifications
        $this->assertTrue($user->notifications->contains($notification));
        
        // Test unread notifications
        $this->assertTrue($user->unreadNotifications->contains($notification));
        
        // Mark as read and test
        $notification->markAsRead();
        $this->assertFalse($user->fresh()->unreadNotifications->contains($notification));
    }

    /** @test */
    public function test_project_milestone_relationships()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $milestone = ProjectMilestone::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $user->id,
            'created_by' => $user->id
        ]);

        // Test milestone belongs to project
        $this->assertEquals($project->id, $milestone->project->id);
        
        // Test milestone belongs to assigned user
        $this->assertEquals($user->id, $milestone->assignedTo->id);
        
        // Test milestone belongs to creator
        $this->assertEquals($user->id, $milestone->createdBy->id);
        
        // Test project has milestones
        $this->assertTrue($project->milestones->contains($milestone));
    }

    /** @test */
    public function test_user_full_name_accessor()
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    /** @test */
    public function test_user_avatar_url_accessor()
    {
        $user = User::factory()->create(['avatar' => null]);
        
        // Test default avatar URL
        $this->assertStringContainsString('ui-avatars.com', $user->avatar_url);
        
        // Test custom avatar
        $user->avatar = 'avatars/test.jpg';
        $user->save();
        
        $this->assertStringContainsString('storage/avatars/test.jpg', $user->avatar_url);
    }

    /** @test */
    public function test_user_is_admin_method()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function test_project_progress_percentage_calculation()
    {
        $project = Project::factory()->create();
        
        // Create milestones with different progress
        ProjectMilestone::factory()->create([
            'project_id' => $project->id,
            'progress_percentage' => 100
        ]);
        
        ProjectMilestone::factory()->create([
            'project_id' => $project->id,
            'progress_percentage' => 50
        ]);
        
        ProjectMilestone::factory()->create([
            'project_id' => $project->id,
            'progress_percentage' => 0
        ]);

        // Test project progress calculation
        $expectedProgress = (100 + 50 + 0) / 3; // 50%
        $this->assertEquals($expectedProgress, $project->progress_percentage);
    }

    /** @test */
    public function test_evidence_status_scopes()
    {
        $pendingEvidence = Evidence::factory()->create(['status' => 'pending']);
        $approvedEvidence = Evidence::factory()->create(['status' => 'approved']);
        $rejectedEvidence = Evidence::factory()->create(['status' => 'rejected']);

        // Test pending scope
        $this->assertTrue(Evidence::pending()->get()->contains($pendingEvidence));
        $this->assertFalse(Evidence::pending()->get()->contains($approvedEvidence));
        
        // Test approved scope
        $this->assertTrue(Evidence::approved()->get()->contains($approvedEvidence));
        $this->assertFalse(Evidence::approved()->get()->contains($pendingEvidence));
    }
}
