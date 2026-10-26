<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\ajuste_ingredientesController;
use App\Http\Controllers\TopController;
use App\Http\Controllers\RecetaController;

Route::get('/', [RecetaController::class, 'inicio']); 
Route::get('/lista', [RecetaController::class, 'mostrar_lista_recetas']); 
Route::get('/mostrar/{id_receta}', [RecetaController::class, 'mostrar_receta']); 

Route::get('/top10', [TopController::class, 'top10']);

Route::get('/ajuste-ingredientes', [ajuste_ingredientesController::class, 'ajuste_ingredientes']);
Route::get('/ingreso', [IngresoController::class, 'create'])->name('ingreso');
Route::post('/ingreso', [IngresoController::class, 'store'])->name('ingreso.guardar');
Route::get('/receta/{id}/{slug?}', [IngresoController::class, 'show'])->whereNumber('id')->name('receta.mostrar');
Route::get('/buscar', [BusquedaController::class, 'index'])->name('buscar');

 
use App\Http\Controllers\BusquedaController;
use Illuminate\Support\Facades\Route;
 
// La página de inicio lleva directo al buscador
Route::get('/', function () {
    return redirect()->route('buscar');
});
 
// R.5: Búsqueda y filtrado de recetas
Route::get('/buscar', [BusquedaController::class, 'index'])->name('buscar');
