<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\TimeLog;
use App\Models\User;
use App\Models\Group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios para asignar como project managers
        $admins = User::where('role', 'admin')->get();
        $analysts = User::where('role', 'analyst')->get();
        $managers = $admins->merge($analysts);

        // Obtener todos los usuarios para asignar como miembros
        $allUsers = User::all();
        $developers = User::whereIn('role', ['investigator', 'user'])->get();

        // Crear proyectos específicos con datos realistas
        $specificProjects = [
            [
                'name' => 'Sistema de Gestión de Evidencias Forenses',
                'description' => 'Desarrollo de plataforma integral para la gestión, análisis y seguimiento de evidencias digitales en investigaciones forenses. Incluye módulos de autenticación, gestión de archivos, flujos de aprobación y reportería avanzada.',
                'status' => 'in_progress',
                'priority' => 'high',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addMonths(2),
                'deadline' => now()->addMonth(),
                'progress_percentage' => 75,
                'budget' => 85000.00,
                'client_name' => 'Ministerio Público - Fiscalía Nacional',
                'repository_url' => 'https://github.com/company/evidence-management',
                'documentation_url' => 'https://docs.company.com/evidence-system',
            ],
            [
                'name' => 'Portal Web Corporativo Institucional',
                'description' => 'Desarrollo de portal web institucional con gestión de contenidos, sistema de noticias, galería multimedia y módulo de contacto. Incluye panel administrativo completo y optimización SEO.',
                'status' => 'completed',
                'priority' => 'medium',
                'start_date' => now()->subMonths(6),
                'end_date' => now()->subMonth(),
                'deadline' => now()->subWeeks(2),
                'progress_percentage' => 100,
                'budget' => 35000.00,
                'client_name' => 'Universidad Nacional del Centro',
                'repository_url' => 'https://github.com/company/corporate-portal',
                'documentation_url' => 'https://docs.company.com/corporate-portal',
            ],
            [
                'name' => 'API de Integración Bancaria',
                'description' => 'Desarrollo de API REST para integración con servicios bancarios, procesamiento de transacciones, consulta de saldos y generación de reportes financieros. Incluye autenticación OAuth2 y encriptación de datos.',
                'status' => 'planning',
                'priority' => 'critical',
                'start_date' => now()->addWeeks(2),
                'end_date' => now()->addMonths(4),
                'deadline' => now()->addMonths(3),
                'progress_percentage' => 5,
                'budget' => 120000.00,
                'client_name' => 'Banco Central de Reserva',
                'repository_url' => null,
                'documentation_url' => null,
            ],
            [
                'name' => 'App Móvil de Delivery y Logística',
                'description' => 'Desarrollo de aplicación móvil nativa para iOS y Android para servicios de delivery. Incluye geolocalización, seguimiento en tiempo real, sistema de pagos y panel de administración web.',
                'status' => 'in_progress',
                'priority' => 'high',
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonths(3),
                'deadline' => now()->addMonths(2),
                'progress_percentage' => 45,
                'budget' => 95000.00,
                'client_name' => 'LogiExpress S.A.C.',
                'repository_url' => 'https://github.com/company/delivery-app',
                'documentation_url' => 'https://docs.company.com/delivery-app',
            ],
            [
                'name' => 'Dashboard de Analytics Empresarial',
                'description' => 'Plataforma de business intelligence con dashboards interactivos, reportes personalizables y análisis de datos en tiempo real. Integración con múltiples fuentes de datos y exportación avanzada.',
                'status' => 'on_hold',
                'priority' => 'medium',
                'start_date' => now()->subMonth(),
                'end_date' => now()->addMonths(4),
                'deadline' => now()->addMonths(3),
                'progress_percentage' => 25,
                'budget' => 75000.00,
                'client_name' => 'Corporación Minera del Sur',
                'repository_url' => 'https://github.com/company/analytics-dashboard',
                'documentation_url' => null,
            ],
        ];

        foreach ($specificProjects as $projectData) {
            $project = Project::create([
                ...$projectData,
                'project_manager_id' => $managers->random()->id,
                'group_id' => Group::inRandomOrder()->first()?->id,
                'is_active' => true,
            ]);

            // Agregar miembros al proyecto
            $projectMembers = $developers->random(rand(3, 8));
            foreach ($projectMembers as $member) {
                $roles = ['senior_developer', 'developer', 'designer', 'tester', 'analyst'];
                $project->addMember(
                    $member,
                    fake()->randomElement($roles),
                    fake()->randomFloat(2, 20, 80)
                );
            }

            // Crear milestones para cada proyecto
            $this->createMilestonesForProject($project, $projectMembers);

            // Crear time logs para proyectos en progreso o completados
            if (in_array($project->status, ['in_progress', 'completed'])) {
                $this->createTimeLogsForProject($project, $projectMembers);
            }
        }

        // Crear proyectos adicionales usando factory
        Project::factory()
            ->count(15)
            ->create()
            ->each(function ($project) use ($developers) {
                // Agregar miembros
                $members = $developers->random(rand(2, 6));
                foreach ($members as $member) {
                    $roles = ['senior_developer', 'developer', 'designer', 'tester'];
                    $project->addMember(
                        $member,
                        fake()->randomElement($roles),
                        fake()->randomFloat(2, 15, 75)
                    );
                }

                // Crear milestones
                $this->createMilestonesForProject($project, $members);

                // Crear time logs si el proyecto está activo
                if (in_array($project->status, ['in_progress', 'completed'])) {
                    $this->createTimeLogsForProject($project, $members);
                }
            });

        // Crear algunos proyectos con estados específicos
        Project::factory()->inProgress()->count(8)->create()->each(function ($project) use ($developers) {
            $members = $developers->random(rand(3, 7));
            foreach ($members as $member) {
                $project->addMember($member, 'developer', fake()->randomFloat(2, 20, 70));
            }
            $this->createMilestonesForProject($project, $members);
            $this->createTimeLogsForProject($project, $members);
        });

        Project::factory()->completed()->count(5)->create()->each(function ($project) use ($developers) {
            $members = $developers->random(rand(2, 5));
            foreach ($members as $member) {
                $project->addMember($member, 'developer', fake()->randomFloat(2, 25, 80));
            }
            $this->createMilestonesForProject($project, $members, true);
            $this->createTimeLogsForProject($project, $members);
        });

        Project::factory()->overdue()->count(3)->create()->each(function ($project) use ($developers) {
            $members = $developers->random(rand(2, 4));
            foreach ($members as $member) {
                $project->addMember($member, 'developer', fake()->randomFloat(2, 20, 60));
            }
            $this->createMilestonesForProject($project, $members);
            $this->createTimeLogsForProject($project, $members);
        });
    }

    /**
     * Create milestones for a project.
     */
    private function createMilestonesForProject(Project $project, $members, bool $allCompleted = false): void
    {
        $milestoneCount = rand(3, 8);
        
        for ($i = 1; $i <= $milestoneCount; $i++) {
            $status = $allCompleted ? 'completed' : 
                     ($i <= $milestoneCount * ($project->progress_percentage / 100) ? 
                      fake()->randomElement(['completed', 'in_progress']) : 'pending');

            ProjectMilestone::factory()->create([
                'project_id' => $project->id,
                'assigned_to' => $members->random()->id,
                'status' => $status,
                'order' => $i,
                'due_date' => $project->start_date?->addWeeks($i * 2),
            ]);
        }
    }

    /**
     * Create time logs for a project.
     */
    private function createTimeLogsForProject(Project $project, $members): void
    {
        $milestones = $project->milestones;
        $timeLogCount = rand(20, 100);

        for ($i = 0; $i < $timeLogCount; $i++) {
            TimeLog::factory()->create([
                'user_id' => $members->random()->id,
                'project_id' => $project->id,
                'milestone_id' => $milestones->random()?->id,
                'date' => fake()->dateTimeBetween(
                    $project->start_date ?? now()->subMonths(3),
                    $project->status === 'completed' ? $project->end_date : now()
                ),
            ]);
        }
    }
}
