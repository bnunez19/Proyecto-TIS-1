<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
/*
Controlador para la vista top10.blade.php de la función 3 Listado de recetas populares 
Responsable: Diego Burgos
*/
class TopController extends Controller
{
public function top10(Request $request)
{
    $categorias = collect(DB::select("SELECT unnest(enum_range(NULL::categoria_al)) AS categoria")) //obtenemos las categorias como un arreglo en filas desde la bd y se convierten en una coleccion de larvel
        ->pluck('categoria') //toma solo el campo categoria de cada objeto y descarta el resto.
        ->all();

    $recetas = null; // null = todavía no se ha buscado nada
    $hayRecetasEnCategoria = false; // sirve para distinguir R3.6 de R3.7

    //R3.5 la categoria es obligatoria (has() permite que ?categoria= vacío también se valide)
    if ($request->has('categoria')) {
        $request->validate([
            'categoria' => ['required', Rule::in($categorias)], //Si alguien manda una categoría inventada (por ejemplo, editando la URL a mano), Laravel muestra un mensaje de error en vez de que PostgreSQL falle con un error 22P02.
        ], [
            'categoria.required' => 'El campo categoría es obligatorio.',
            'categoria.in' => 'La categoría seleccionada no es válida.',
        ]);

        $hayRecetasEnCategoria = Receta::where('categoria', $request->categoria)->exists();

        //R3.1 filtra las recetas según la CATEGORIA y solo considera las que tienen al menos una calificacion
        //R3.2 El listado muestra como máximo las 10 recetas ordenadas de la más popular a la menos popular.
        //R3.3 Si dos o más recetas empatan casos: promedio, cantidad y nombre

        $recetas = Receta::with('regimenes')
            ->where('categoria', $request->categoria)
            ->where('cantidad_calificaciones', '>', 0)
            ->orderByDesc('promedio_calificacion')
            ->orderByDesc('cantidad_calificaciones')
            ->orderByRaw('LOWER(nombre_receta) ASC')
            ->limit(10)
            ->get();
    }

    return view('top10', compact('categorias', 'recetas', 'hayRecetasEnCategoria'));
}
}