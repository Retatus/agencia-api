<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaisController;

Route::group([], function () {
    Route::apiResource('paises', PaisController::class);
});
