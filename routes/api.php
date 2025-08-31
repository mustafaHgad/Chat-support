<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

// Public Chat Routes (accessible by guests and authenticated users)
Route::prefix('chat')->group(function () {
    Route::post('/start', [ChatController::class, 'startChat'])->middleware('throttle:60,1');
    Route::get('/{sessionId}/messages', [ChatController::class, 'getChatMessages']);
});

// Message Routes
Route::prefix('messages')->group(function () {
    Route::post('/send', [MessageController::class, 'sendMessage']);
    Route::patch('/{messageId}/read', [MessageController::class, 'markAsRead']);
});

// Agent-only Routes
Route::prefix('agent')
    ->middleware(['auth:sanctum', 'role:agent'])
    ->group(function () {
        Route::get('/chats/waiting', [AgentController::class, 'getWaitingChats']);
        Route::get('/chats/active', [AgentController::class, 'getActiveChats']);
        Route::post('/chats/{chatId}/assign', [AgentController::class, 'assignChat']);
        Route::post('/chats/{chatId}/close', [AgentController::class, 'closeChat']);
        Route::get('/statistics', [AgentController::class, 'getStatistics']);
    });

// User Profile Routes
Route::prefix('user')->
    middleware('auth:sanctum')
    ->group(function () {
    //Route::get('/chats', [ChatController::class, 'getUserChats']);
    Route::get('/profile', [AuthController::class, 'user']);
    //Route::patch('/profile', [AuthController::class, 'updateProfile']);
    });

// Health Check Route
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});
