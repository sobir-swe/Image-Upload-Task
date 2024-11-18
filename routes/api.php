<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::resource('/images', ImageController::class);

//Route::resource('/sites', SiteController::class);

Route::post('/sites/upload', [SiteController::class, 'upload']);
