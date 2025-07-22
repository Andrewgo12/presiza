<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Evidence;
use App\Models\Group;
use App\Models\TimeLog;
use App\Models\ProjectMilestone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RouteTestingScript extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;
    protected $project;
    protected $evidence;
    protected $group;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        
        $this->user = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        
        // Create test data
        $this->project = Project::factory()->create([
            'created_by' => $this->admin->id,
        ]);
        
        $this->evidence = Evidence::factory()->create([
            'submitted_by' => $this->user->id,
        ]);
        
        $this->group = Group::factory()->create([
            'leader_id' => $this->admin->id,
        ]);
    }

    /** @test */
    public function test_public_routes()
    {
        // Test root route
        $response = $this->get('/');
        $response->assertRedirect('/login');
        
        // Test health check
        $response = $this->get('/health');
        $response->assertStatus(200);
        
        // Test ping
        $response = $this->get('/ping');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_auth_routes()
    {
        // Test login page
        $response = $this->get('/login');
        $response->assertStatus(200);
        
        // Test register page
        $response = $this->get('/register');
        $response->assertStatus(200);
        
        // Test forgot password page
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_authenticated_routes()
    {
        $this->actingAs($this->user);
        
        // Test dashboard
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        
        // Test profile
        $response = $this->get('/profile');
        $response->assertStatus(200);
        
        // Test notifications
        $response = $this->get('/notifications');
        $response->assertStatus(200);
        
        // Test evidences
        $response = $this->get('/evidences');
        $response->assertStatus(200);
        
        // Test groups
        $response = $this->get('/groups');
        $response->assertStatus(200);
        
        // Test projects
        $response = $this->get('/projects');
        $response->assertStatus(200);
        
        // Test time logs
        $response = $this->get('/time-logs');
        $response->assertStatus(200);
        
        // Test analytics
        $response = $this->get('/analytics');
        $response->assertStatus(200);
        
        // Test search
        $response = $this->get('/search');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_admin_routes()
    {
        $this->actingAs($this->admin);
        
        // Test admin dashboard
        $response = $this->get('/admin');
        $response->assertStatus(200);
        
        // Test admin users
        $response = $this->get('/admin/users');
        $response->assertStatus(200);
        
        // Test admin projects
        $response = $this->get('/admin/projects');
        $response->assertStatus(200);
        
        // Test admin evidences
        $response = $this->get('/admin/evidences');
        $response->assertStatus(200);
        
        // Test admin groups
        $response = $this->get('/admin/groups');
        $response->assertStatus(200);
        
        // Test admin analytics
        $response = $this->get('/admin/analytics');
        $response->assertStatus(200);
        
        // Test admin settings
        $response = $this->get('/admin/settings');
        $response->assertStatus(200);
        
        // Test admin logs
        $response = $this->get('/admin/logs');
        $response->assertStatus(200);
        
        // Test admin backups
        $response = $this->get('/admin/backups');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_api_routes()
    {
        $this->actingAs($this->user);
        
        // Test API routes
        $response = $this->get('/api/v1/ping');
        $response->assertStatus(200);
        
        // Test notifications count
        $response = $this->get('/api/v1/notifications/count');
        $response->assertStatus(200);
        
        // Test dashboard stats
        $response = $this->get('/api/dashboard/stats');
        $response->assertStatus(200);
        
        // Test unread notifications count
        $response = $this->get('/api/notifications/unread-count');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_resource_routes()
    {
        $this->actingAs($this->user);
        
        // Test evidence show
        $response = $this->get("/evidences/{$this->evidence->id}");
        $response->assertStatus(200);
        
        // Test group show
        $response = $this->get("/groups/{$this->group->id}");
        $response->assertStatus(200);
        
        // Test project show
        $response = $this->get("/projects/{$this->project->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function test_unauthorized_admin_access()
    {
        $this->actingAs($this->user);
        
        // Test that regular users cannot access admin routes
        $response = $this->get('/admin/users');
        $response->assertStatus(403);
        
        $response = $this->get('/admin/settings');
        $response->assertStatus(403);
    }

    /** @test */
    public function test_guest_redirects()
    {
        // Test that guests are redirected to login
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
        
        $response = $this->get('/profile');
        $response->assertRedirect('/login');
        
        $response = $this->get('/evidences');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_form_submissions()
    {
        $this->actingAs($this->user);
        
        // Test profile update
        $response = $this->patch('/profile', [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => $this->user->email,
        ]);
        $response->assertRedirect('/profile');
        
        // Test evidence creation
        $response = $this->post('/evidences', [
            'title' => 'Test Evidence',
            'description' => 'Test Description',
            'category' => 'security',
            'priority' => 'medium',
        ]);
        $response->assertRedirect();
    }

    /** @test */
    public function test_admin_form_submissions()
    {
        $this->actingAs($this->admin);
        
        // Test user creation
        $response = $this->post('/admin/users', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'is_active' => true,
        ]);
        $response->assertRedirect('/admin/users');
    }
}
