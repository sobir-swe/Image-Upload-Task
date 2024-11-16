<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::resource('/images', ImageController::class);
