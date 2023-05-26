<?php

use App\Http\Controllers\API\FootballCompetitionOutcomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/football-competition-outcome', [FootballCompetitionOutcomeController::class, 'generate'])
    ->name('api.football-competition-outcome.generate');
