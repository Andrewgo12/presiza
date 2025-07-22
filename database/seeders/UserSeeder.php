<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuarios principales de prueba
        $users = [
            // Administrador principal
            [
                'first_name' => 'Admin',
                'last_name' => 'Hospital',
                'email' => 'admin@hospital.gov.co',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'department' => 'Administración',
                'position' => 'Administrador del Sistema',
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_settings' => [
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'evidence_notifications' => true,
                    'message_notifications' => true,
                    'project_notifications' => true,
                ],
                'privacy_settings' => [
                    'profile_visibility' => 'public',
                    'show_email' => true,
                    'show_department' => true,
                    'show_position' => true,
                    'allow_messages' => true,
                ],
            ],
            // Médico
            [
                'first_name' => 'Dr. Carlos',
                'last_name' => 'Médico',
                'email' => 'medico@hospital.gov.co',
                'password' => Hash::make('password'),
                'role' => 'investigator',
                'department' => 'Medicina',
                'position' => 'Médico Especialista',
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_settings' => [
                    'email_notifications' => true,
                    'push_notifications' => false,
                    'evidence_notifications' => true,
                    'message_notifications' => true,
                    'project_notifications' => false,
                ],
                'privacy_settings' => [
                    'profile_visibility' => 'team',
                    'show_email' => false,
                    'show_department' => true,
                    'show_position' => true,
                    'allow_messages' => true,
                ],
            ],
            // EPS
            [
                'first_name' => 'Ana',
                'last_name' => 'EPS',
                'email' => 'eps@hospital.gov.co',
                'password' => Hash::make('password'),
                'role' => 'analyst',
                'department' => 'EPS',
                'position' => 'Analista EPS',
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_settings' => [
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'evidence_notifications' => true,
                    'message_notifications' => false,
                    'project_notifications' => true,
                ],
                'privacy_settings' => [
                    'profile_visibility' => 'team',
                    'show_email' => true,
                    'show_department' => true,
                    'show_position' => true,
                    'allow_messages' => true,
                ],
            ],
            // Sistema
            [
                'first_name' => 'Sistema',
                'last_name' => 'Técnico',
                'email' => 'sistema@hospital.gov.co',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'department' => 'Sistemas',
                'position' => 'Técnico de Sistemas',
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_settings' => [
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'evidence_notifications' => true,
                    'message_notifications' => true,
                    'project_notifications' => true,
                ],
                'privacy_settings' => [
                    'profile_visibility' => 'public',
                    'show_email' => true,
                    'show_department' => true,
                    'show_position' => true,
                    'allow_messages' => true,
                ],
            ],
            [
                'first_name' => 'María',
                'last_name' => 'González',
                'email' => 'analyst@company.com',
                'password' => Hash::make('analyst123'),
                'role' => 'analyst',
                'department' => 'Auditoría',
                'position' => 'Analista Senior',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Rodríguez',
                'email' => 'investigator@company.com',
                'password' => Hash::make('investigator123'),
                'role' => 'investigator',
                'department' => 'Seguridad',
                'position' => 'Investigador',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Martínez',
                'email' => 'ana.martinez@company.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'department' => 'Recursos Humanos',
                'position' => 'Coordinadora',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Luis',
                'last_name' => 'Fernández',
                'email' => 'luis.fernandez@company.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'department' => 'Finanzas',
                'position' => 'Contador',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Sofia',
                'last_name' => 'López',
                'email' => 'sofia.lopez@company.com',
                'password' => Hash::make('password123'),
                'role' => 'analyst',
                'department' => 'Calidad',
                'position' => 'Analista de Calidad',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Diego',
                'last_name' => 'Morales',
                'email' => 'diego.morales@company.com',
                'password' => Hash::make('password123'),
                'role' => 'investigator',
                'department' => 'Seguridad',
                'position' => 'Especialista en Seguridad',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Crear usuarios adicionales con factory
        User::factory(15)->create();

        $this->command->info('Usuarios creados exitosamente.');
        $this->command->info('Credenciales de acceso:');
        $this->command->info('Admin: admin@company.com / admin123');
        $this->command->info('Usuario: user@company.com / user123');
        $this->command->info('Analista: analyst@company.com / analyst123');
        $this->command->info('Investigador: investigator@company.com / investigator123');
    }
}
