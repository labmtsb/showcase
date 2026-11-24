<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/home', function () {
    return view('home');

});

Route::get('/projects', function () {
    return view('project.projects');
});

Route::get('/projects/manage', function () {
    return view('project.manage');
});
