<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('fact:generate')
    ->daily()
    ->withoutOverlapping();
