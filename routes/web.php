<?php

use App\Http\Controllers\ProjectPreviewController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['guest']], function () {
    Route::livewire('/', 'pages::auth.login')->name('login');
});

Route::group(['middleware' => ['auth']], function () {

    Route::livewire('/home', 'pages::home.show-home')->name('home');

    Route::livewire('/projects', 'pages::project.show-project')->name('projects');

    Route::livewire('/projects/{id}/manage', 'pages::project.manage.manage-project')->name('manage');

    Route::livewire('/projects/{id}/preview', 'pages::project.preview.preview-project')->name('preview');

    Route::livewire('/profile', 'pages::profile.show-profile')->name('profile');

});

Route::get('/{user}/{project}/{path?}', [ProjectPreviewController::class, 'show'])
    ->where([
        'user'    => '[0-9]+',
        'project' => '[a-zA-Z0-9_-]+',
        'path'    => '.*',
    ])->name('project.preview');
