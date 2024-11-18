<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::resource('/images', ImageController::class);
Route::get('/gallery', [ImageController::class, 'showGallery']);

Route::resource('/sites', SiteController::class);
