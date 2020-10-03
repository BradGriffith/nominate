<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $position_id = App\Models\Position::where('is_default',1)->first()->id;
    return Inertia\Inertia::render('DashboardPage', compact(['position_id']));
})->name('dashboard');

Route::get('/vote', function () {
    $position_id = App\Models\Position::where('is_default',1)->first()->id;
    return Inertia\Inertia::render('VotePage', compact(['position_id']));
})->name('vote');

Route::get('/rank', function () {
    $position_id = App\Models\Position::where('is_default',1)->first()->id;
    return Inertia\Inertia::render('RankPage', compact(['position_id']));
})->name('rank');

Route::get('/results', function () {
    $position_id = App\Models\Position::where('is_default',1)->first()->id;
    $nominees = App\Models\Ranking::getWinners($position_id);
    return Inertia\Inertia::render('ResultsPage', compact(['position_id','nominees']));
})->name('results');

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return Inertia\Inertia::render('Dashboard');
})->name('dashboard');
