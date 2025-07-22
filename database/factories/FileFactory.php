<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extensions = ['pdf', 'docx', 'xlsx', 'pptx', 'jpg', 'png', 'mp4', 'txt', 'zip'];
        $extension = fake()->randomElement($extensions);
        $filename = Str::uuid() . '.' . $extension;
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'mp4' => 'video/mp4',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
        ];

        $categories = [
            'pdf' => 'document',
            'docx' => 'document',
            'xlsx' => 'document',
            'pptx' => 'document',
            'jpg' => 'image',
            'png' => 'image',
            'mp4' => 'video',
            'txt' => 'document',
            'zip' => 'archive',
        ];

        $tags = [
            'importante', 'confidencial', 'público', 'interno', 'borrador',
            'final', 'revisión', 'aprobado', 'pendiente', 'urgente'
        ];

        return [
            'filename' => $filename,
            'original_name' => fake()->words(3, true) . '.' . $extension,
            'path' => 'files/' . $filename,
            'disk' => 'public',
            'size' => fake()->numberBetween(1024, 50 * 1024 * 1024), // 1KB a 50MB
            'mime_type' => $mimeTypes[$extension],
            'extension' => $extension,
            'category' => $categories[$extension],
            'tags' => fake()->optional(0.7)->randomElements($tags, fake()->numberBetween(1, 4)),
            'description' => fake()->optional(0.6)->paragraph(),
            'uploaded_by' => User::factory(),
            'is_public' => fake()->boolean(20), // 20% públicos
            'access_level' => fake()->randomElement(['public', 'internal', 'restricted', 'confidential']),
            'download_count' => fake()->numberBetween(0, 100),
            'view_count' => fake()->numberBetween(0, 500),
            'thumbnail_path' => $extension === 'jpg' || $extension === 'png' 
                ? 'thumbnails/' . pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg'
                : null,
            'metadata' => fake()->optional(0.3)->randomElements([
                'author' => fake()->name(),
                'created_with' => fake()->randomElement(['Microsoft Word', 'Adobe Acrobat', 'LibreOffice']),
                'version' => fake()->randomElement(['1.0', '1.1', '2.0']),
                'keywords' => implode(', ', fake()->words(3)),
            ]),
            'expires_at' => fake()->optional(0.1)->dateTimeBetween('now', '+1 year'),
        ];
    }

    /**
     * Indicate that the file is an image.
     */
    public function image(): static
    {
        $extension = fake()->randomElement(['jpg', 'png', 'gif', 'webp']);
        $filename = Str::uuid() . '.' . $extension;
        
        return $this->state(fn (array $attributes) => [
            'filename' => $filename,
            'original_name' => fake()->words(2, true) . '.' . $extension,
            'path' => 'files/' . $filename,
            'mime_type' => 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension),
            'extension' => $extension,
            'category' => 'image',
            'size' => fake()->numberBetween(100 * 1024, 10 * 1024 * 1024), // 100KB a 10MB
            'thumbnail_path' => 'thumbnails/' . pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg',
        ]);
    }

    /**
     * Indicate that the file is a document.
     */
    public function document(): static
    {
        $extension = fake()->randomElement(['pdf', 'docx', 'xlsx', 'pptx', 'txt']);
        $filename = Str::uuid() . '.' . $extension;
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
        ];

        return $this->state(fn (array $attributes) => [
            'filename' => $filename,
            'original_name' => fake()->words(4, true) . '.' . $extension,
            'path' => 'files/' . $filename,
            'mime_type' => $mimeTypes[$extension],
            'extension' => $extension,
            'category' => 'document',
            'size' => fake()->numberBetween(10 * 1024, 5 * 1024 * 1024), // 10KB a 5MB
        ]);
    }

    /**
     * Indicate that the file is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'access_level' => 'public',
        ]);
    }

    /**
     * Indicate that the file is confidential.
     */
    public function confidential(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
            'access_level' => 'confidential',
            'tags' => ['confidencial', 'restringido'],
        ]);
    }

    /**
     * Indicate that the file has high activity.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'download_count' => fake()->numberBetween(50, 200),
            'view_count' => fake()->numberBetween(200, 1000),
        ]);
    }

    /**
     * Indicate that the file expires soon.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('now', '+30 days'),
        ]);
    }
}
