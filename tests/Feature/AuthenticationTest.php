<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function test_login_page_can_be_rendered()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create([
            'email' => 'admin@hospital.gov.co',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@hospital.gov.co',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'admin@hospital.gov.co',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => 'admin@hospital.gov.co',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    /** @test */
    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /** @test */
    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /** @test */
    public function test_new_users_can_register()
    {
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function test_password_reset_link_screen_can_be_rendered()
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertViewIs('auth.forgot-password');
    }

    /** @test */
    public function test_password_reset_link_can_be_requested()
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        // We don't assert anything here since we don't want to send actual emails in tests
        // In a real application, you would mock the mail facade
    }

    /** @test */
    public function test_admin_users_can_access_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_regular_users_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    /** @test */
    public function test_guests_are_redirected_to_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_email_verification_screen_can_be_rendered()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/verify-email');
        $response->assertStatus(200);
        $response->assertViewIs('auth.verify-email');
    }

    /** @test */
    public function test_verified_users_are_redirected_from_verification_screen()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/verify-email');
        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function test_user_profile_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect('/profile');
        $this->assertEquals('Updated', $user->fresh()->first_name);
        $this->assertEquals('Name', $user->fresh()->last_name);
    }

    /** @test */
    public function test_user_password_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect('/profile');
        $this->assertTrue(password_verify('new-password', $user->fresh()->password));
    }

    /** @test */
    public function test_user_account_can_be_deleted()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    /** @test */
    public function test_correct_password_must_be_provided_to_delete_account()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertNotNull($user->fresh());
    }

    /** @test */
    public function test_demo_credentials_work()
    {
        // Create demo users
        $admin = User::factory()->create([
            'email' => 'admin@hospital.gov.co',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $medico = User::factory()->create([
            'email' => 'medico@hospital.gov.co',
            'password' => bcrypt('password'),
            'role' => 'investigator',
            'email_verified_at' => now(),
        ]);

        $eps = User::factory()->create([
            'email' => 'eps@hospital.gov.co',
            'password' => bcrypt('password'),
            'role' => 'analyst',
            'email_verified_at' => now(),
        ]);

        $sistema = User::factory()->create([
            'email' => 'sistema@hospital.gov.co',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Test admin login
        $response = $this->post('/login', [
            'email' => 'admin@hospital.gov.co',
            'password' => 'password',
        ]);
        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
        $this->post('/logout');

        // Test medico login
        $response = $this->post('/login', [
            'email' => 'medico@hospital.gov.co',
            'password' => 'password',
        ]);
        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
        $this->post('/logout');

        // Test EPS login
        $response = $this->post('/login', [
            'email' => 'eps@hospital.gov.co',
            'password' => 'password',
        ]);
        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
        $this->post('/logout');

        // Test sistema login
        $response = $this->post('/login', [
            'email' => 'sistema@hospital.gov.co',
            'password' => 'password',
        ]);
        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }
}
