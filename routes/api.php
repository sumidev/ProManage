<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);

// // Protected routes
// Route::middleware('auth:sanctum')->group(function () {
//     // Auth routes
//     Route::post('/logout', [AuthController::class, 'logout']);
//     Route::get('/user', [AuthController::class, 'user']);

//     // Project routes
//     Route::apiResource('projects', ProjectController::class);

//     // Task routes
//     Route::post('projects/{project}/tasks', [TaskController::class, 'store']);
//     Route::put('tasks/{task}', [TaskController::class, 'update']);
//     Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus']);
//     Route::delete('tasks/{task}', [TaskController::class, 'destroy']);
// });
