<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    // Routes publiques : inscription et connexion
    Route::post('/register', [AdminController::class, 'register']);
    Route::post('/login', [AdminController::class, 'login']);

    // Routes protégées par Sanctum (authentification requise)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AdminController::class, 'me']);
        Route::put('/update', [AdminController::class, 'update']);
        Route::post('/logout', [AdminController::class, 'logout']);
        Route::delete('/delete', [AdminController::class, 'deleteSelf']);
    });
});
