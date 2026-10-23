<?php
 
use App\Http\Controllers\BusquedaController;
use Illuminate\Support\Facades\Route;
 
// La página de inicio lleva directo al buscador
Route::get('/', function () {
    return redirect()->route('buscar');
});
 
// R.5: Búsqueda y filtrado de recetas
Route::get('/buscar', [BusquedaController::class, 'index'])->name('buscar');