<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('migrate:fresh --seed --force')->cron('0 */4 * * *');
Schedule::call(function () {
    // Clear the 'local' bucket (MinIO S3)
    \Illuminate\Support\Facades\Storage::disk('s3')->deleteDirectory('/');
})->daily();
Schedule::command('telescope:prune --hours=24')->daily();
