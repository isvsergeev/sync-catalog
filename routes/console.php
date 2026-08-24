<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:import-categories')
    ->everyMinute()
    ->withoutOverlapping(3600);
