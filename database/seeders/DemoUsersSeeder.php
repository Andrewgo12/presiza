<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo users for each role
        $demoUsers = [
            [
                'name' => 'Administrador del Sistema',
                'email' => 'admin@hospital.gov.co',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'department' => 'Administración',
                'position' => 'Administrador General',
                'phone' => '+57 1 234 5678',
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_preferences' => [
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'evidence_updates' => true,
                    'project_updates' => true,
                    'system_alerts' => true,
                    'marketing' => false,
                ],
                'preferences' => [
                    'theme' => 'admin',
                    'language' => 'es',
                    'timezone' => 'America/Bogota',
                    'dashboard_layout' => 'admin',
                ],
            ],
            [
                'name' => 'Dr. María Elena Rodríguez',
                'email' => 'medico@hospital.gov.co',
                'password' => Hash::make('password'),
                'role' => 'investigator',
                'department' => 'Medicina Interna',
                'position' => 'Médico Especialista',
                'phone' => '+57 1 234 5679',
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_preferences' => [
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'evidence_updates' => true,
                    'project_updates' => true,
                    'system_alerts' => false,
                    'marketing' => false,
                ],
                'preferences' => [
                    'theme' => 'medical',
                    'language' => 'es',
                    'timezone' => 'America/Bogota',
                    'dashboard_layout' => 'medical',
                ],
            ],
            [
                'name' => 'Carlos Andrés Gómez',
                'email' => 'eps@hospital.gov.co',
                'password' => Hash::make('password'),
                'role' => 'analyst',
                'department' => 'Análisis y Estadísticas',
                'position' => 'Analista Senior EPS',
                'phone' => '+57 1 234 5680',
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_preferences' => [
                    'email_notifications' => true,
                    'push_notifications' => false,
                    'evidence_updates' => true,
                    'project_updates' => true,
                    'system_alerts' => false,
                    'marketing' => false,
                ],
                'preferences' => [
                    'theme' => 'eps',
                    'language' => 'es',
                    'timezone' => 'America/Bogota',
                    'dashboard_layout' => 'analytics',
                ],
            ],
            [
                'name' => 'Ing. Roberto Silva',
                'email' => 'sistemas@hospital.gov.co',
                'password' => Hash::make('password'),
                'role' => 'admin', // Using admin for systems role for now
                'department' => 'Tecnología e Informática',
                'position' => 'Administrador de Sistemas',
                'phone' => '+57 1 234 5681',
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_preferences' => [
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'evidence_updates' => false,
                    'project_updates' => true,
                    'system_alerts' => true,
                    'marketing' => false,
                ],
                'preferences' => [
                    'theme' => 'systems',
                    'language' => 'es',
                    'timezone' => 'America/Bogota',
                    'dashboard_layout' => 'technical',
                ],
            ],
        ];

        foreach ($demoUsers as $userData) {
            // Check if user already exists
            $existingUser = User::where('email', $userData['email'])->first();
            
            if (!$existingUser) {
                $user = User::create($userData);
                $this->command->info("Created demo user: {$user->name} ({$user->email})");
            } else {
                // Update existing user with new fields
                $existingUser->update([
                    'role' => $userData['role'],
                    'department' => $userData['department'],
                    'position' => $userData['position'],
                    'phone' => $userData['phone'],
                    'notification_preferences' => $userData['notification_preferences'],
                    'preferences' => $userData['preferences'],
                ]);
                $this->command->info("Updated existing user: {$existingUser->name} ({$existingUser->email})");
            }
        }

        // Create additional medical staff
        $additionalMedicalStaff = [
            [
                'name' => 'Dra. Ana Patricia Morales',
                'email' => 'ana.morales@hospital.gov.co',
                'role' => 'investigator',
                'department' => 'Cardiología',
                'position' => 'Cardióloga',
            ],
            [
                'name' => 'Dr. Luis Fernando Castro',
                'email' => 'luis.castro@hospital.gov.co',
                'role' => 'investigator',
                'department' => 'Neurología',
                'position' => 'Neurólogo',
            ],
            [
                'name' => 'Dra. Carmen Lucía Herrera',
                'email' => 'carmen.herrera@hospital.gov.co',
                'role' => 'investigator',
                'department' => 'Pediatría',
                'position' => 'Pediatra',
            ],
        ];

        foreach ($additionalMedicalStaff as $staffData) {
            $userData = array_merge([
                'password' => Hash::make('password'),
                'phone' => '+57 1 234 ' . rand(5000, 9999),
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_preferences' => [
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'evidence_updates' => true,
                    'project_updates' => true,
                    'system_alerts' => false,
                    'marketing' => false,
                ],
                'preferences' => [
                    'theme' => 'medical',
                    'language' => 'es',
                    'timezone' => 'America/Bogota',
                    'dashboard_layout' => 'medical',
                ],
            ], $staffData);

            $existingUser = User::where('email', $userData['email'])->first();
            
            if (!$existingUser) {
                $user = User::create($userData);
                $this->command->info("Created additional medical staff: {$user->name}");
            }
        }

        // Create additional EPS analysts
        $additionalEPSStaff = [
            [
                'name' => 'Liliana Vargas',
                'email' => 'liliana.vargas@hospital.gov.co',
                'role' => 'analyst',
                'department' => 'Análisis y Estadísticas',
                'position' => 'Analista Junior EPS',
            ],
            [
                'name' => 'Miguel Ángel Torres',
                'email' => 'miguel.torres@hospital.gov.co',
                'role' => 'analyst',
                'department' => 'Análisis y Estadísticas',
                'position' => 'Especialista en Datos',
            ],
        ];

        foreach ($additionalEPSStaff as $staffData) {
            $userData = array_merge([
                'password' => Hash::make('password'),
                'phone' => '+57 1 234 ' . rand(5000, 9999),
                'is_active' => true,
                'email_verified_at' => now(),
                'notification_preferences' => [
                    'email_notifications' => true,
                    'push_notifications' => false,
                    'evidence_updates' => true,
                    'project_updates' => true,
                    'system_alerts' => false,
                    'marketing' => false,
                ],
                'preferences' => [
                    'theme' => 'eps',
                    'language' => 'es',
                    'timezone' => 'America/Bogota',
                    'dashboard_layout' => 'analytics',
                ],
            ], $staffData);

            $existingUser = User::where('email', $userData['email'])->first();
            
            if (!$existingUser) {
                $user = User::create($userData);
                $this->command->info("Created additional EPS staff: {$user->name}");
            }
        }

        $this->command->info('Demo users seeding completed!');
        $this->command->info('');
        $this->command->info('Demo Credentials:');
        $this->command->info('==================');
        $this->command->info('Admin: admin@hospital.gov.co / password');
        $this->command->info('Medical: medico@hospital.gov.co / password');
        $this->command->info('EPS: eps@hospital.gov.co / password');
        $this->command->info('Systems: sistemas@hospital.gov.co / password');
    }
}
