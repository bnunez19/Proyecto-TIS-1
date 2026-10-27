<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ajuste_ingredientesController extends Controller
{
    public function ajuste_ingredientes()
    {
        $id_receta = 1; // esto despues lo cambiamos por la receta que se esta visualizando en la pagina anterior

        $receta = DB::table('receta')         // obtenemos la receta y buscamos por el id seleccionado 
            ->where('id_receta', $id_receta) ->first();

        abort_if(!$receta, 404, 'Receta no encontrada.');

        //obtenemos los ingredientes y sus cantidades.
        $ingredientes = DB::table('receta_ingrediente as ri')
            ->join( 'ingrediente as i', 'ri.id_ingrediente', '=', 'i.id_ingrediente')
            ->where('ri.id_receta', $id_receta)

            ->select( 'i.nombre_ingrediente', 'ri.cantidad_ingrediente', 'ri.unidad_medida')
            ->get();

        //obtenemos los pasos de preparación.
        $pasos = DB::table('paso_preparacion')
            ->where('id_receta', $id_receta)
            ->orderBy('paso_numero')
            ->get();

        //enviamos los datos a la view ajuste_ingredientes.blade.php
        return view('ajuste_ingredientes', [
            'receta' => $receta,
            'ingredientes' => $ingredientes,
            'pasos' => $pasos
        ]);
    }
}
