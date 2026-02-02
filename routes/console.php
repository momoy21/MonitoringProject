<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| SAP Auto Import - Berjalan setiap 1 jam untuk import file dari FTP
| Untuk mengaktifkan scheduler, tambahkan cron job:
| * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
| Atau di Windows Task Scheduler, jalankan setiap menit:
| php artisan schedule:run
|
*/

Schedule::command('sap:auto-import')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/sap-auto-import.log'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('SAP Auto Import scheduled task completed successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('SAP Auto Import scheduled task failed');
    });
