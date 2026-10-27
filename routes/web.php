<?php

use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\IngresoController;

// Route::get('/ingreso', [IngresoController::class, 'create'])->name('ingreso');
// Route::post('/ingreso', [IngresoController::class, 'store'])->name('ingreso.guardar');

// Mostrar formulario de valoración
Route::get('/valorar-receta', function () {
    return view('valorar_receta');
});

// Ruta temporal para probar el formulario
Route::post('/valorar-receta/guardar', function () {
    return redirect('/valorar-receta')
        ->with('exito', 'Formulario enviado en modo de prueba.');
})->name('valorar.guardar');