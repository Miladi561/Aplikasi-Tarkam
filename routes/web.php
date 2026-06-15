<?php

use App\Http\Controllers\TournamentBracketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tournaments/{tournament}/bracket', [TournamentBracketController::class, 'show'])
    ->name('tournaments.bracket');
