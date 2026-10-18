<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IngresoController;


Route::get('/ingreso', [IngresoController::class, 'create'])->name('ingreso');
Route::post('/ingreso', [IngresoController::class, 'store'])->name('ingreso.guardar');
