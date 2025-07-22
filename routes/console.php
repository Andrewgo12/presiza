<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('files:cleanup', function () {
    $this->info('Cleaning up temporary files...');
    // Lógica para limpiar archivos temporales
})->purpose('Clean up temporary files');

Artisan::command('reports:weekly', function () {
    $this->info('Generating weekly reports...');
    // Lógica para generar reportes semanales
})->purpose('Generate weekly reports');

Artisan::command('projects:notify-deadlines', function () {
    $this->info('Notifying about upcoming project deadlines...');
    // Lógica para notificar deadlines próximos
})->purpose('Notify about upcoming project deadlines');

Artisan::command('notifications:cleanup', function () {
    $this->info('Cleaning up old notifications...');
    // Lógica para limpiar notificaciones antiguas
})->purpose('Clean up old notifications');
