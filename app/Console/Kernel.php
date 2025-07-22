<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        // Limpiar archivos temporales diariamente
        $schedule->command('files:cleanup')->daily();
        
        // Generar reportes semanales
        $schedule->command('reports:weekly')->weekly();
        
        // Backup de base de datos
        $schedule->command('backup:run')->daily()->at('02:00');
        
        // Notificar deadlines próximos
        $schedule->command('projects:notify-deadlines')->daily()->at('09:00');
        
        // Limpiar notificaciones antiguas
        $schedule->command('notifications:cleanup')->weekly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
