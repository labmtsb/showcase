<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['guest']], function () {
    Route::get('/', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('attempt_login');
});

Route::group(['middleware' => ['auth']], function () {
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    Route::get('/projects', function () {
        return view('project.projects');
    })->name('projects');

    Route::get('/projects/manage', function () {
        return view('project.manage');
    })->name('manage');

    // logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});
