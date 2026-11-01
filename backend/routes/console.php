<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Each module registers its scheduled tasks and closure commands in routes/schedule/{module}.php
foreach (glob(__DIR__.'/schedule/*.php') ?: [] as $file) {
    require $file;
}
