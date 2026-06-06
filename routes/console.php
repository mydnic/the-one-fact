<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('fact:generate')
    ->dailyAt('06:00')
    ->withoutOverlapping();
