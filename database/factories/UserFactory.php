<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement(['user', 'analyst', 'investigator']),
            'department' => fake()->randomElement([
                'Tecnología', 'Recursos Humanos', 'Finanzas', 'Operaciones',
                'Marketing', 'Ventas', 'Legal', 'Auditoría', 'Seguridad'
            ]),
            'position' => fake()->jobTitle(),
            'is_active' => fake()->boolean(90), // 90% activos
            'last_login' => fake()->optional(0.8)->dateTimeBetween('-30 days', 'now'),
            'notification_settings' => [
                'email_notifications' => fake()->boolean(80),
                'push_notifications' => fake()->boolean(70),
                'sms_notifications' => fake()->boolean(30),
            ],
            'privacy_settings' => [
                'profile_visibility' => fake()->randomElement(['public', 'internal', 'private']),
                'show_online_status' => fake()->boolean(60),
                'allow_direct_messages' => fake()->boolean(85),
            ],
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'department' => 'Tecnología',
            'position' => 'Administrador del Sistema',
        ]);
    }

    /**
     * Indicate that the user is an analyst.
     */
    public function analyst(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'analyst',
            'department' => fake()->randomElement(['Auditoría', 'Calidad', 'Operaciones']),
            'position' => fake()->randomElement(['Analista Senior', 'Analista', 'Especialista']),
        ]);
    }

    /**
     * Indicate that the user is an investigator.
     */
    public function investigator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'investigator',
            'department' => 'Seguridad',
            'position' => fake()->randomElement(['Investigador', 'Especialista en Seguridad', 'Auditor']),
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'last_login' => fake()->optional(0.3)->dateTimeBetween('-90 days', '-30 days'),
        ]);
    }

    /**
     * Indicate that the user has recent activity.
     */
    public function recentlyActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login' => fake()->dateTimeBetween('-7 days', 'now'),
            'is_active' => true,
        ]);
    }
}
