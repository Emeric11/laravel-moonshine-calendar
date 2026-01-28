<?php
use App\Http\Controllers\CalendarEventController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rutas API del calendario
Route::prefix('api')->group(function () {
    Route::get('/calendar/events', [CalendarEventController::class, 'index']);
    Route::post('/calendar/events', [CalendarEventController::class, 'store']);
    Route::patch('/calendar/events/{id}', [CalendarEventController::class, 'update']);
    Route::patch('/calendar/events/{id}/datetime', [CalendarEventController::class, 'updateDateTime']);
    Route::delete('/calendar/events/{id}', [CalendarEventController::class, 'destroy']);
    Route::get('/calendar/events/{id}', [CalendarEventController::class, 'show']);
    
    // Rutas para carga de PDFs
    Route::post('/calendar/events/{event}/pdf', [CalendarEventController::class, 'guardarPdf']);
});

// Ruta del calendario (fuera del admin de MoonShine)
Route::get('/calendar', function () {
    return view('calendar');
});