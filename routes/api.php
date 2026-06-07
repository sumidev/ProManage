<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectInvitationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::post('/register', function () {
//     return response()->json([
//         'message' => 'Welcome to the ProManage API',
//     ]);
// });
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail']);

Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/dashboard/stats',[DashboardController::class, 'stats']);
    Route::get('/search', [GlobalSearchController::class, 'index']);
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Project routes
    Route::post('projects/invite',[ProjectController::class,'inviteMember']);
    Route::post('projects/search',[ProjectController::class,'searchProject']);
    Route::apiResource('projects', ProjectController::class);

    Route::get('/invitations/{token}', [ProjectInvitationController::class, 'show']);
    Route::post('/invitations/{token}/respond', [ProjectInvitationController::class, 'respond']);
    // Task routes
    Route::post('projects/{project}/tasks', [TaskController::class, 'store']);
    Route::put('tasks/{task}', [TaskController::class, 'update']);
    Route::put('tasks/{task}/move', [TaskController::class, 'moveTask']);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::delete('tasks/{task}', [TaskController::class, 'destroy']);

    Route::apiResource('comments', CommentController::class);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);

    Route::get('/admin/users',[UserController::class,'index']);
    Route::patch('/admin/users/{userId}/role',[UserController::class,'updateSystemRole']);
    Route::post('/user/updateProfile', [UserController::class, 'updateProfile']);
    Route::post('user/updatePassword',[UserController::class,'updatePassword']);

    Route::post('/ai/generate-project', [AiController::class, 'generateProject']);
});
