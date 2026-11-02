<?php

namespace App\Http\Controllers;
use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecetaController extends Controller
{
    public function inicio()
    {
        return view('inicio');
    }

    public function mostrar_lista_recetas()
    {
        $recetas = Receta::orderBy('nombre_receta') -> get();
        return view('recetas.lista_recetas', compact('recetas'));

    }

    public function mostrar_receta($id_receta)
    {
        $recetas = Receta::with('regimenes') ->findOrFail($id_receta); //busca el autor con ese id. Si no existe, Laravel muestra automáticamente una página de error 404, en vez de romperse.
        return view('recetas.mostrar', compact('recetas'));
    }


}
