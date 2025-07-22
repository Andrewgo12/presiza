<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios existentes
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios disponibles. Ejecuta UserSeeder primero.');
            return;
        }

        // Crear grupos específicos
        $groups = [
            [
                'name' => 'Equipo de Seguridad',
                'description' => 'Grupo dedicado a la gestión de incidentes de seguridad y análisis de amenazas.',
                'type' => 'restricted',
                'created_by' => $users->where('role', 'admin')->first()->id,
            ],
            [
                'name' => 'Analistas de Evidencias',
                'description' => 'Grupo de trabajo para analistas encargados de la revisión y evaluación de evidencias.',
                'type' => 'private',
                'created_by' => $users->where('role', 'analyst')->first()?->id ?? $users->first()->id,
            ],
            [
                'name' => 'Investigadores',
                'description' => 'Equipo especializado en investigaciones internas y análisis forense.',
                'type' => 'private',
                'created_by' => $users->where('role', 'investigator')->first()?->id ?? $users->first()->id,
            ],
            [
                'name' => 'Comunicaciones Generales',
                'description' => 'Canal abierto para comunicaciones generales de la organización.',
                'type' => 'public',
                'created_by' => $users->where('role', 'admin')->first()->id,
            ],
            [
                'name' => 'Soporte Técnico',
                'description' => 'Grupo de soporte técnico para resolver dudas sobre el sistema.',
                'type' => 'public',
                'created_by' => $users->where('role', 'admin')->first()->id,
            ],
            [
                'name' => 'Auditoría Interna',
                'description' => 'Equipo de auditoría interna para revisión de procesos y cumplimiento.',
                'type' => 'restricted',
                'created_by' => $users->where('role', 'analyst')->first()?->id ?? $users->first()->id,
            ],
            [
                'name' => 'Gestión de Calidad',
                'description' => 'Grupo enfocado en la mejora continua y control de calidad.',
                'type' => 'private',
                'created_by' => $users->random()->id,
            ],
            [
                'name' => 'Capacitación y Desarrollo',
                'description' => 'Espacio para compartir recursos de capacitación y desarrollo profesional.',
                'type' => 'public',
                'created_by' => $users->random()->id,
            ],
        ];

        $createdGroups = collect();

        foreach ($groups as $groupData) {
            $group = Group::create($groupData);
            $createdGroups->push($group);
            
            // Agregar al creador como admin del grupo
            $group->addMember(User::find($groupData['created_by']), 'admin');
        }

        // Agregar miembros a los grupos
        foreach ($createdGroups as $group) {
            $memberCount = match ($group->type) {
                'public' => rand(8, 15),
                'private' => rand(4, 10),
                'restricted' => rand(3, 8),
                default => rand(5, 12),
            };

            // Seleccionar miembros apropiados según el tipo de grupo
            $potentialMembers = match ($group->name) {
                'Equipo de Seguridad' => $users->whereIn('role', ['admin', 'investigator', 'analyst']),
                'Analistas de Evidencias' => $users->whereIn('role', ['analyst', 'investigator']),
                'Investigadores' => $users->where('role', 'investigator'),
                'Auditoría Interna' => $users->whereIn('role', ['admin', 'analyst']),
                default => $users,
            };

            $selectedMembers = $potentialMembers->random(min($memberCount, $potentialMembers->count()));

            foreach ($selectedMembers as $member) {
                if (!$group->hasMember($member)) {
                    $role = match (true) {
                        $member->role === 'admin' => 'admin',
                        $member->role === 'analyst' && rand(1, 3) === 1 => 'moderator',
                        $member->role === 'investigator' && rand(1, 4) === 1 => 'moderator',
                        default => 'member',
                    };
                    
                    $group->addMember($member, $role);
                }
            }
        }

        // Crear algunos mensajes de ejemplo en los grupos
        foreach ($createdGroups as $group) {
            $messageCount = rand(5, 15);
            
            for ($i = 0; $i < $messageCount; $i++) {
                $sender = $group->members->random();
                $recipients = $group->members->where('id', '!=', $sender->id)->random(rand(1, 3));
                
                $subjects = [
                    'Actualización de procedimientos',
                    'Reunión semanal programada',
                    'Nuevo protocolo de seguridad',
                    'Revisión de casos pendientes',
                    'Capacitación disponible',
                    'Reporte de actividades',
                    'Consulta técnica',
                    'Propuesta de mejora',
                    'Alerta de seguridad',
                    'Documentación actualizada',
                ];

                $message = Message::create([
                    'subject' => fake()->randomElement($subjects),
                    'content' => fake()->paragraphs(rand(1, 3), true),
                    'sender_id' => $sender->id,
                    'type' => 'group',
                    'group_id' => $group->id,
                    'priority' => fake()->randomElement(['normal', 'high']),
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ]);

                // Agregar destinatarios
                foreach ($recipients as $recipient) {
                    $message->addRecipient($recipient);
                    
                    // Marcar algunos mensajes como leídos
                    if (fake()->boolean(70)) {
                        $message->markAsReadBy($recipient);
                    }
                }
            }
        }

        // Crear algunos grupos adicionales aleatorios
        for ($i = 0; $i < 5; $i++) {
            $group = Group::create([
                'name' => fake()->words(2, true),
                'description' => fake()->paragraph(),
                'type' => fake()->randomElement(['public', 'private', 'restricted']),
                'created_by' => $users->random()->id,
                'is_active' => fake()->boolean(90),
            ]);

            // Agregar miembros aleatorios
            $memberCount = rand(3, 8);
            $members = $users->random($memberCount);
            
            foreach ($members as $member) {
                $role = fake()->randomElement(['member', 'member', 'member', 'moderator']);
                $group->addMember($member, $role);
            }
        }

        $this->command->info('Grupos de ejemplo creados exitosamente.');
        $this->command->info('Total de grupos: ' . Group::count());
        
        // Mostrar estadísticas
        $this->command->info('Estadísticas de grupos:');
        $this->command->info('- Públicos: ' . Group::where('type', 'public')->count());
        $this->command->info('- Privados: ' . Group::where('type', 'private')->count());
        $this->command->info('- Restringidos: ' . Group::where('type', 'restricted')->count());
        $this->command->info('- Activos: ' . Group::where('is_active', true)->count());
        $this->command->info('- Total de mensajes: ' . Message::where('type', 'group')->count());
        
        // Mostrar información de grupos específicos
        foreach ($createdGroups->take(3) as $group) {
            $this->command->info("- {$group->name}: {$group->member_count} miembros");
        }
    }
}
