<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TopController extends Controller
{
public function top10(Request $request)
{
    $categorias = collect(DB::select("SELECT unnest(enum_range(NULL::categoria_al)) AS categoria")) //obtenemos las categorias como un arreglo en filas desde la bd y se convierten en una coleccion de larvel
        ->pluck('categoria') //toma solo el campo categoria de cada objeto y descarta el resto.
        ->all();

    $receta = null; // null = todavía no se ha buscado nada

    if ($request->filled('categoria')) {
        $request->validate([
            'categoria' => ['required', Rule::in($categorias)], //Si alguien manda una categoría inventada (por ejemplo, editando la URL a mano), Laravel muestra un mensaje de error en vez de que PostgreSQL falle con un error 22P02.
        ]);

        $receta = Receta::with('regimenes')
            ->where('categoria', $request->categoria)
            ->orderByDesc('promedio_calificacion')
            ->orderByDesc('cantidad_calificaciones')
            ->orderBy('nombre_receta')
            ->limit(10)
            ->get();
    }

    return view('top10', compact('categorias', 'receta'));
}
}
