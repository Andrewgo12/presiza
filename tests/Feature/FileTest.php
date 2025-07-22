<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_authenticated_user_can_view_files_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('files.index'));

        $response->assertStatus(200);
        $response->assertViewIs('files.index');
    }

    public function test_guest_cannot_view_files_index(): void
    {
        $response = $this->get(route('files.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_upload_file(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($user)->post(route('files.store'), [
            'files' => [$file],
            'category' => 'document',
            'access_level' => 'internal',
            'description' => 'Test file upload',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('files', [
            'original_name' => 'test.pdf',
            'uploaded_by' => $user->id,
            'category' => 'document',
            'access_level' => 'internal',
        ]);
    }

    public function test_user_can_view_own_file(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['uploaded_by' => $user->id]);

        $response = $this->actingAs($user)->get(route('files.show', $file));

        $response->assertStatus(200);
        $response->assertViewIs('files.show');
        $response->assertViewHas('file', $file);
    }

    public function test_user_cannot_view_confidential_file_of_others(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create();
        $file = File::factory()->confidential()->create(['uploaded_by' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('files.show', $file));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_file(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $file = File::factory()->confidential()->create(['uploaded_by' => $user->id]);

        $response = $this->actingAs($admin)->get(route('files.show', $file));

        $response->assertStatus(200);
    }

    public function test_user_can_download_accessible_file(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['uploaded_by' => $user->id]);

        $response = $this->actingAs($user)->get(route('files.download', $file));

        $response->assertStatus(200);
    }

    public function test_user_can_update_own_file(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['uploaded_by' => $user->id]);

        $response = $this->actingAs($user)->patch(route('files.update', $file), [
            'category' => 'image',
            'access_level' => 'public',
            'description' => 'Updated description',
        ]);

        $response->assertRedirect(route('files.show', $file));
        $this->assertDatabaseHas('files', [
            'id' => $file->id,
            'category' => 'image',
            'access_level' => 'public',
            'description' => 'Updated description',
        ]);
    }

    public function test_user_can_delete_own_non_confidential_file(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create([
            'uploaded_by' => $user->id,
            'access_level' => 'internal',
        ]);

        $response = $this->actingAs($user)->delete(route('files.destroy', $file));

        $response->assertRedirect(route('files.index'));
        $this->assertSoftDeleted('files', ['id' => $file->id]);
    }

    public function test_user_cannot_delete_confidential_file(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $file = File::factory()->confidential()->create(['uploaded_by' => $user->id]);

        $response = $this->actingAs($user)->delete(route('files.destroy', $file));

        $response->assertStatus(403);
        $this->assertDatabaseHas('files', ['id' => $file->id]);
    }

    public function test_file_search_works(): void
    {
        $user = User::factory()->create();
        $file1 = File::factory()->create([
            'uploaded_by' => $user->id,
            'original_name' => 'important_document.pdf',
        ]);
        $file2 = File::factory()->create([
            'uploaded_by' => $user->id,
            'original_name' => 'random_file.txt',
        ]);

        $response = $this->actingAs($user)->get(route('files.index', ['search' => 'important']));

        $response->assertStatus(200);
        $response->assertSee('important_document.pdf');
        $response->assertDontSee('random_file.txt');
    }

    public function test_file_filtering_by_category_works(): void
    {
        $user = User::factory()->create();
        $imageFile = File::factory()->image()->create(['uploaded_by' => $user->id]);
        $documentFile = File::factory()->document()->create(['uploaded_by' => $user->id]);

        $response = $this->actingAs($user)->get(route('files.index', ['category' => 'image']));

        $response->assertStatus(200);
        $response->assertSee($imageFile->original_name);
        $response->assertDontSee($documentFile->original_name);
    }

    public function test_file_view_count_increments_on_view(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create([
            'uploaded_by' => $user->id,
            'view_count' => 0,
        ]);

        $this->actingAs($user)->get(route('files.show', $file));

        $file->refresh();
        $this->assertEquals(1, $file->view_count);
    }

    public function test_file_download_count_increments_on_download(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create([
            'uploaded_by' => $user->id,
            'download_count' => 0,
        ]);

        $this->actingAs($user)->get(route('files.download', $file));

        $file->refresh();
        $this->assertEquals(1, $file->download_count);
    }
}
