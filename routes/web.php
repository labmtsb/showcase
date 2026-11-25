<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['guest']], function () {
    Route::livewire('/', 'pages::auth.login')->name('login');
});

Route::group(['middleware' => ['auth']], function () {

    Route::livewire('/home', 'pages::home.show-home')->name('home');

    Route::livewire('/projects', 'pages::project.show-project')->name('projects');
});
