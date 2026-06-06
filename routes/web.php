<?php

use App\Http\Controllers\FactController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FactController::class, 'show'])->name('fact.show');
Route::get('/api/fact', [FactController::class, 'json'])->name('fact.json');
