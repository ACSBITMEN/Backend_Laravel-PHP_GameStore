<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// RUTAS PÚBLICAS (Sin autenticación)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// RUTAS PROTEGIDAS (Requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // User routes
    Route::prefix('user')->group(function () {
        // Rutas para el usuario actual (su propio perfil)
        Route::get('/profile', [UserController::class, 'showProfile']); // Ver perfil propio
        Route::put('/profile', [UserController::class, 'updateProfile']); // Actualizar perfil propio
    });

    // User management routes
    Route::prefix('users')->group(function () {
        
        // Rutas solo para admin y manager
        Route::middleware('role:admin,manager')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'createUser']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{user}', [UserController::class, 'updateUser']); // Admin/Manager editan otros usuarios
            Route::patch('/{user}/activate', [UserController::class, 'activate']);
            Route::patch('/{user}/deactivate', [UserController::class, 'deactivate']);
        });
        
        // Rutas solo para manager
        Route::middleware('role:manager')->group(function () {
            Route::delete('/{user}', [UserController::class, 'destroy']);
        });
    });
    
    // Rutas de ejemplo para futuros módulos
    Route::middleware('role:manager')->group(function () {
        Route::get('/business-stats', function () {
            return response()->json([
                'message' => 'Estadísticas de negocio - Solo Manager',
                'stats' => [
                    'total_users' => \App\Models\User::count(),
                    'active_users' => \App\Models\User::active()->count(),
                    'total_orders' => 0, // Para implementar después
                    'revenue' => 0, // Para implementar después
                ]
            ]);
        });
    });
});

// RUTAS DE PRUEBA (Puedes eliminarlas después)
Route::get('/test-public', function () {
    return response()->json([
        'message' => 'Esta es una ruta pública de prueba',
        'status' => 'success'
    ]);
});

Route::get('/test-protected', function () {
    return response()->json([
        'message' => 'Esta es una ruta protegida de prueba - Debes estar autenticado',
        'status' => 'success'
    ]);
})->middleware('auth:sanctum');

Route::get('/test-admin', function () {
    return response()->json([
        'message' => 'Esta es una ruta solo para Admin/Manager',
        'user' => auth()->user(),
        'role' => auth()->user()->role->name
    ]);
})->middleware('auth:sanctum', 'role:admin,manager');