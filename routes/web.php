<?php

use App\Http\Controllers\AuthController;
// use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['guest']], function () {
    Route::get('/', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('attempt_login');
});

Route::group(['middleware' => ['auth']], function () {

    Route::livewire('/home', 'pages::home.show-home')->name('home');

    // Route::get('/projects', [ProjectController::class, 'index'])->name('projects');
    Route::livewire('/projects', 'pages::project.show-project')->name('projects');

    // logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});
