<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/403', function () {
    abort(403);
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::patch(
        '/members/{member}/status',
        [MemberController::class, 'updateStatus']
    )->name('members.update-status');
    Route::resource('members', MemberController::class);

    Route::patch(
        '/ministries/{ministry}/status',
        [MinistryController::class, 'toggleStatus']
    )->name('ministries.toggle-status');
    Route::resource('ministries', MinistryController::class);

    Route::get('/schedules/{schedule}/poster', [ScheduleController::class, 'poster'])
        ->name('schedules.poster'); //For view
    Route::post('/schedules/{schedule}/poster/generate', [ScheduleController::class, 'generatePoster'])
    ->name('schedules.poster.generate'); //For generate poster
    Route::resource('schedules', ScheduleController::class);

    // Route::view('/poster/preview', 'poster.template')
    // ->name('poster.preview');
        
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
