<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('image_upload');
});

Route::post('/images', [ImageController::class, 'store']);

Route::get('/images', [ImageController::class, 'index']);
