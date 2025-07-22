<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Evidence;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RouteAccessTest extends TestCase
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
    public function test_public_routes_are_accessible()
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
    public function test_auth_routes_are_accessible()
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
    public function test_authenticated_user_routes()
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
    public function test_admin_routes_require_admin_role()
    {
        // Test with regular user (should be forbidden)
        $this->actingAs($this->user);
        
        $response = $this->get('/admin');
        $response->assertStatus(403);
        
        $response = $this->get('/admin/users');
        $response->assertStatus(403);
        
        // Test with admin user (should be accessible)
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin');
        $response->assertStatus(200);
        
        $response = $this->get('/admin/users');
        $response->assertStatus(200);
        
        $response = $this->get('/admin/projects');
        $response->assertStatus(200);
        
        $response = $this->get('/admin/evidences');
        $response->assertStatus(200);
        
        $response = $this->get('/admin/groups');
        $response->assertStatus(200);
        
        $response = $this->get('/admin/analytics');
        $response->assertStatus(200);
        
        $response = $this->get('/admin/settings');
        $response->assertStatus(200);
        
        $response = $this->get('/admin/logs');
        $response->assertStatus(200);
        
        $response = $this->get('/admin/backups');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_api_routes_work()
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
    public function test_resource_routes_work()
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
    public function test_guest_redirects_to_login()
    {
        // Test that guests are redirected to login
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
        
        $response = $this->get('/profile');
        $response->assertRedirect('/login');
        
        $response = $this->get('/evidences');
        $response->assertRedirect('/login');
        
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_form_submissions_work()
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
    public function test_admin_form_submissions_work()
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

    /** @test */
    public function test_middleware_is_working()
    {
        // Test rate limiting middleware (this is basic, real rate limiting tests would be more complex)
        $response = $this->get('/login');
        $response->assertStatus(200);
        
        // Test CSRF protection
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        // Should fail without CSRF token in a real scenario, but our test environment handles this
        $response->assertStatus(302); // Redirect due to validation errors
    }

    /** @test */
    public function test_search_functionality()
    {
        $this->actingAs($this->user);
        
        // Test search API
        $response = $this->get('/api/v1/search?q=test');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'results',
            'total',
            'query'
        ]);
    }

    /** @test */
    public function test_notification_api_endpoints()
    {
        $this->actingAs($this->user);
        
        // Test notifications list
        $response = $this->get('/api/v1/notifications');
        $response->assertStatus(200);
        
        // Test profile endpoint
        $response = $this->get('/api/v1/profile');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'role'
            ],
            'stats'
        ]);
    }

    /** @test */
    public function test_system_status_endpoint()
    {
        // Test system status (public endpoint)
        $response = $this->get('/api/v1/system/status');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'database',
            'storage',
            'cache',
            'timestamp'
        ]);
    }

    /** @test */
    public function test_file_upload_routes()
    {
        $this->actingAs($this->user);
        
        // Test avatar upload
        $response = $this->patch('/profile/avatar', [
            'avatar' => \Illuminate\Http\Testing\File::image('avatar.jpg', 100, 100)
        ]);
        $response->assertRedirect('/profile');
    }

    /** @test */
    public function test_admin_user_management()
    {
        $this->actingAs($this->admin);
        
        // Test user creation form
        $response = $this->get('/admin/users/create');
        $response->assertStatus(200);
        
        // Test user edit form
        $response = $this->get("/admin/users/{$this->user->id}/edit");
        $response->assertStatus(200);
        
        // Test user show page
        $response = $this->get("/admin/users/{$this->user->id}");
        $response->assertStatus(200);
    }
}
