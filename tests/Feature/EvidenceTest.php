<?php

namespace Tests\Feature;

use App\Models\Evidence;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EvidenceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_authenticated_user_can_view_evidences_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('evidences.index'));

        $response->assertStatus(200);
        $response->assertViewIs('evidences.index');
    }

    public function test_user_can_create_evidence(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['uploaded_by' => $user->id]);

        $response = $this->actingAs($user)->post(route('evidences.store'), [
            'title' => 'Test Evidence',
            'description' => 'This is a test evidence description.',
            'category' => 'security',
            'priority' => 'high',
            'status' => 'pending',
            'files' => [$file->id],
            'location' => 'Test Location',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evidences', [
            'title' => 'Test Evidence',
            'submitted_by' => $user->id,
            'category' => 'security',
            'priority' => 'high',
            'status' => 'pending',
        ]);
    }

    public function test_user_can_view_own_evidence(): void
    {
        $user = User::factory()->create();
        $evidence = Evidence::factory()->create(['submitted_by' => $user->id]);

        $response = $this->actingAs($user)->get(route('evidences.show', $evidence));

        $response->assertStatus(200);
        $response->assertViewIs('evidences.show');
        $response->assertViewHas('evidence', $evidence);
    }

    public function test_assigned_user_can_view_evidence(): void
    {
        $user = User::factory()->create();
        $assignedUser = User::factory()->analyst()->create();
        $evidence = Evidence::factory()->create([
            'submitted_by' => $user->id,
            'assigned_to' => $assignedUser->id,
        ]);

        $response = $this->actingAs($assignedUser)->get(route('evidences.show', $evidence));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_any_evidence(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $evidence = Evidence::factory()->create(['submitted_by' => $user->id]);

        $response = $this->actingAs($admin)->get(route('evidences.show', $evidence));

        $response->assertStatus(200);
    }

    public function test_user_can_update_own_pending_evidence(): void
    {
        $user = User::factory()->create();
        $evidence = Evidence::factory()->pending()->create(['submitted_by' => $user->id]);

        $response = $this->actingAs($user)->patch(route('evidences.update', $evidence), [
            'title' => 'Updated Evidence Title',
            'description' => 'Updated description',
            'category' => 'investigation',
            'priority' => 'critical',
        ]);

        $response->assertRedirect(route('evidences.show', $evidence));
        $this->assertDatabaseHas('evidences', [
            'id' => $evidence->id,
            'title' => 'Updated Evidence Title',
            'category' => 'investigation',
            'priority' => 'critical',
        ]);
    }

    public function test_user_cannot_update_approved_evidence(): void
    {
        $user = User::factory()->create();
        $evidence = Evidence::factory()->approved()->create(['submitted_by' => $user->id]);

        $response = $this->actingAs($user)->patch(route('evidences.update', $evidence), [
            'title' => 'Updated Evidence Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_analyst_can_change_evidence_status(): void
    {
        $analyst = User::factory()->analyst()->create();
        $evidence = Evidence::factory()->pending()->create();

        $response = $this->actingAs($analyst)->patch(route('evidences.status', $evidence), [
            'status' => 'under_review',
            'notes' => 'Starting review process',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evidences', [
            'id' => $evidence->id,
            'status' => 'under_review',
        ]);
    }

    public function test_regular_user_cannot_change_evidence_status(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $evidence = Evidence::factory()->pending()->create();

        $response = $this->actingAs($user)->patch(route('evidences.status', $evidence), [
            'status' => 'approved',
        ]);

        $response->assertStatus(403);
    }

    public function test_analyst_can_assign_evidence(): void
    {
        $analyst = User::factory()->analyst()->create();
        $investigator = User::factory()->investigator()->create();
        $evidence = Evidence::factory()->pending()->create();

        $response = $this->actingAs($analyst)->post(route('evidences.assign', $evidence), [
            'assigned_to' => $investigator->id,
            'notes' => 'Assigning to investigator',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evidences', [
            'id' => $evidence->id,
            'assigned_to' => $investigator->id,
        ]);
    }

    public function test_investigator_can_evaluate_evidence(): void
    {
        $investigator = User::factory()->investigator()->create();
        $evidence = Evidence::factory()->underReview()->create();

        $response = $this->actingAs($investigator)->post(route('evidences.evaluate', $evidence), [
            'rating' => 4,
            'comment' => 'Good evidence with solid documentation',
            'recommendation' => 'approve',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evidence_evaluations', [
            'evidence_id' => $evidence->id,
            'evaluator_id' => $investigator->id,
            'rating' => 4,
            'recommendation' => 'approve',
        ]);
    }

    public function test_user_cannot_evaluate_own_evidence(): void
    {
        $user = User::factory()->analyst()->create();
        $evidence = Evidence::factory()->underReview()->create(['submitted_by' => $user->id]);

        $response = $this->actingAs($user)->post(route('evidences.evaluate', $evidence), [
            'rating' => 5,
            'comment' => 'Self evaluation',
            'recommendation' => 'approve',
        ]);

        $response->assertStatus(403);
    }

    public function test_evidence_history_is_created_on_status_change(): void
    {
        $analyst = User::factory()->analyst()->create();
        $evidence = Evidence::factory()->pending()->create();

        $this->actingAs($analyst)->patch(route('evidences.status', $evidence), [
            'status' => 'under_review',
            'notes' => 'Starting review',
        ]);

        $this->assertDatabaseHas('evidence_history', [
            'evidence_id' => $evidence->id,
            'user_id' => $analyst->id,
            'action' => 'status_changed',
        ]);
    }

    public function test_evidence_filtering_by_status_works(): void
    {
        $user = User::factory()->create();
        $pendingEvidence = Evidence::factory()->pending()->create(['submitted_by' => $user->id]);
        $approvedEvidence = Evidence::factory()->approved()->create(['submitted_by' => $user->id]);

        $response = $this->actingAs($user)->get(route('evidences.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee($pendingEvidence->title);
        $response->assertDontSee($approvedEvidence->title);
    }

    public function test_evidence_search_works(): void
    {
        $user = User::factory()->create();
        $evidence1 = Evidence::factory()->create([
            'submitted_by' => $user->id,
            'title' => 'Security Incident Report',
        ]);
        $evidence2 = Evidence::factory()->create([
            'submitted_by' => $user->id,
            'title' => 'Compliance Audit',
        ]);

        $response = $this->actingAs($user)->get(route('evidences.index', ['search' => 'Security']));

        $response->assertStatus(200);
        $response->assertSee('Security Incident Report');
        $response->assertDontSee('Compliance Audit');
    }

    public function test_evidence_can_be_associated_with_files(): void
    {
        $user = User::factory()->create();
        $file1 = File::factory()->create(['uploaded_by' => $user->id]);
        $file2 = File::factory()->create(['uploaded_by' => $user->id]);

        $response = $this->actingAs($user)->post(route('evidences.store'), [
            'title' => 'Evidence with Files',
            'description' => 'This evidence has attached files.',
            'category' => 'investigation',
            'priority' => 'medium',
            'status' => 'pending',
            'files' => [$file1->id, $file2->id],
        ]);

        $response->assertRedirect();
        
        $evidence = Evidence::where('title', 'Evidence with Files')->first();
        $this->assertCount(2, $evidence->files);
        $this->assertTrue($evidence->files->contains($file1));
        $this->assertTrue($evidence->files->contains($file2));
    }
}
