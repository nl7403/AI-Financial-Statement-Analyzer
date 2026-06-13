<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/analyses', [App\Http\Controllers\AnalysisController::class, 'index'])->name('analyses.index');
    Route::get('/analyses/create', [App\Http\Controllers\AnalysisController::class, 'create'])->name('analyses.create');
    Route::post('/analyses', [App\Http\Controllers\AnalysisController::class, 'store'])->name('analyses.store');
    Route::get('/analyses/{analysis}', [App\Http\Controllers\AnalysisController::class, 'show'])->name('analyses.show');
});

require __DIR__.'/auth.php';
