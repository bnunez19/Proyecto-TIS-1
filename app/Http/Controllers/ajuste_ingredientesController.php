<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ajuste_ingredientesController extends Controller
{
    public function ajuste_ingredientes(Request $request)
    {
        //se obtienen todos los id y se ordenan de menor a mayor
        $ids_recetas = DB::table('receta')
            ->orderBy('id_receta')
            ->pluck('id_receta');

        abort_if($ids_recetas->isEmpty(), 404, 'No se ha encontrado ninguna receta.');

        //se obtiene el id que solicita el usuario o el primero si es que no ingresa nada
        $id_solicitado = $request->query('id_receta');

        if ($id_solicitado === null){
            $id_receta = $ids_recetas->first();
        } else {
            //se valida que el id ingresado cumpla con la especificacion
            $request->validate([
                'id_receta' => 'required|integer|min:1|max:99999999999'
            ]);


            $id_solicitado = (int) $id_solicitado;
            
            //se busca si el id ingresado por el usuario existe o no
            if ($ids_recetas->contains($id_solicitado)) {
                $id_receta = $id_solicitado;
            } else {
                $id_receta = $ids_recetas->first(); //si no existe, se busca el mas cercano

                foreach ($ids_recetas as $id) {
                    if (
                        abs($id - $id_solicitado) <
                        abs($id_receta - $id_solicitado)
                    ) {
                        $id_receta = $id;
                    }
                }
            }
        }

        //obtención de datos
        $receta = DB::table('receta')         // obtenemos la receta y buscamos por el id seleccionado 
            ->where('id_receta', $id_receta) ->first();

        abort_if(!$receta, 404, 'Receta no encontrada.');

        //obtenemos los ingredientes y sus cantidades
        $ingredientes = DB::table('receta_ingrediente as ri')
            ->join( 'ingrediente as i', 'ri.id_ingrediente', '=', 'i.id_ingrediente')
            ->where('ri.id_receta', $id_receta)

            ->select( 'i.nombre_ingrediente', 'ri.cantidad_ingrediente', 'ri.unidad_medida')
            ->get();

        //obtenemos los pasos de preparación
        $pasos = DB::table('paso_preparacion')
            ->where('id_receta', $id_receta)
            ->orderBy('paso_numero')
            ->get();

        //posición de la receta seleccionada
        $posicion = $ids_recetas->search($id_receta);

        //identificamos el ID anterior y el siguiente
        $id_anterior = $posicion > 0
            ? $ids_recetas[$posicion - 1]
            : null;

        $id_siguiente = $posicion < $ids_recetas->count() - 1
            ? $ids_recetas[$posicion + 1]
            : null;

        //enviamos los datos a la view ajuste_ingredientes.blade.php
        return view('ajuste_ingredientes', [
            'receta' => $receta,
            'ingredientes' => $ingredientes,
            'pasos' => $pasos,
            'ids_recetas' => $ids_recetas,
            'id_anterior' => $id_anterior,
            'id_siguiente' => $id_siguiente
        ]);
    }
}
