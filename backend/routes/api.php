<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AppointmentTypeController;
use App\Http\Controllers\Api\DocumentTypeController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecordController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\IncidentController;

// Ruta de prueba para verificar que la API funciona
Route::get('test', function () {
    return response()->json([
        'message' => 'API INEBEX funcionando correctamente',
        'version' => '1.0.0',
        'timestamp' => now()
    ]);
});

// Endpoint simple de login (dev/demo - usa md5 como el resto del proyecto)
Route::post('login', [UserController::class, 'login']);

// ============= USUARIOS =============
Route::prefix('usuarios')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::put('{id}', [UserController::class, 'update']);
    Route::delete('{id}', [UserController::class, 'destroy']);
});

// ============= ROLES =============
Route::prefix('roles')->group(function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::post('/', [RoleController::class, 'store']);
    Route::get('{id}', [RoleController::class, 'show']);
    Route::put('{id}', [RoleController::class, 'update']);
    Route::delete('{id}', [RoleController::class, 'destroy']);
});

// ============= CITAS =============
Route::prefix('citas')->group(function () {
    Route::get('/', [AppointmentController::class, 'index']);
    Route::post('/', [AppointmentController::class, 'store']);
    Route::get('hoy', [AppointmentController::class, 'today']); // Debe ir antes de {id}
    Route::get('{id}', [AppointmentController::class, 'show']);
    Route::put('{id}', [AppointmentController::class, 'update']);
    Route::delete('{id}', [AppointmentController::class, 'destroy']);
});

// ============= TIPOS DE CITAS =============
Route::prefix('tipos-citas')->group(function () {
    Route::get('/', [AppointmentTypeController::class, 'index']);
    Route::post('/', [AppointmentTypeController::class, 'store']);
    Route::get('{id}', [AppointmentTypeController::class, 'show']);
    Route::put('{id}', [AppointmentTypeController::class, 'update']);
    Route::delete('{id}', [AppointmentTypeController::class, 'destroy']);
});

// ============= TIPOS DE DOCUMENTOS =============
Route::prefix('tipos-documentos')->group(function () {
    Route::get('/', [DocumentTypeController::class, 'index']);
    Route::post('/', [DocumentTypeController::class, 'store']);
    Route::get('{id}', [DocumentTypeController::class, 'show']);
    Route::put('{id}', [DocumentTypeController::class, 'update']);
    Route::delete('{id}', [DocumentTypeController::class, 'destroy']);
});

// ============= PERFILES =============
Route::prefix('perfiles')->group(function () {
    Route::get('/', [ProfileController::class, 'index']);
    Route::post('/', [ProfileController::class, 'store']);
    Route::get('{id}', [ProfileController::class, 'show']);
    Route::put('{id}', [ProfileController::class, 'update']);
    Route::delete('{id}', [ProfileController::class, 'destroy']);
});

// ============= EXPEDIENTES =============
Route::prefix('expedientes')->group(function () {
    Route::get('/', [RecordController::class, 'index']);
    Route::post('/', [RecordController::class, 'store']);
    Route::get('estado/{status}', [RecordController::class, 'byStatus']);
    Route::get('{id}', [RecordController::class, 'show']);
    Route::put('{id}', [RecordController::class, 'update']);
    Route::delete('{id}', [RecordController::class, 'destroy']);
});

// ============= DOCUMENTOS =============
Route::prefix('documentos')->group(function () {
    Route::get('/', [DocumentController::class, 'index']);
    Route::post('/', [DocumentController::class, 'store']); // Upload PDF
    Route::get('expediente/{recordId}', [DocumentController::class, 'byRecord']);
    Route::get('{id}', [DocumentController::class, 'show']);
    Route::get('{id}/descargar', [DocumentController::class, 'download']);
    Route::put('{id}', [DocumentController::class, 'update']);
    Route::delete('{id}', [DocumentController::class, 'destroy']);
});

// ============= NOTIFICACIONES =============
Route::prefix('notificaciones')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/', [NotificationController::class, 'store']);
    Route::get('usuario/{userId}/no-leidas', [NotificationController::class, 'unreadCount']);
    Route::put('marcar-todas-leidas', [NotificationController::class, 'markAllAsRead']);
    Route::get('{id}', [NotificationController::class, 'show']);
    Route::put('{id}/leer', [NotificationController::class, 'markAsRead']);
    Route::delete('{id}', [NotificationController::class, 'destroy']);
});

// ============= INCIDENCIAS =============
Route::prefix('incidencias')->group(function () {
    Route::get('/', [IncidentController::class, 'index']);
    Route::post('/', [IncidentController::class, 'store']);
    Route::get('prioridad/{priority}', [IncidentController::class, 'byPriority']);
    Route::get('vencidas', [IncidentController::class, 'overdue']);
    Route::get('{id}', [IncidentController::class, 'show']);
    Route::put('{id}', [IncidentController::class, 'update']);
    Route::delete('{id}', [IncidentController::class, 'destroy']);
});

// Ruta para usuarios autenticados (comentada hasta implementar auth)
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');