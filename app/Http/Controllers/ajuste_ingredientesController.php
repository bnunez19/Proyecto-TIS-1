<?php
//Controlador para la vista ajuste_ingredientes.blade.php de la funcion 4 Ajuste de Ingredientes segun cantidad de personas
//Responsable: Edinson Ramos
namespace App\Http\Controllers;

//necesarios para recibir datos y realizar llamados a la bd
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ajuste_ingredientesController extends Controller
{
    public function ajuste_ingredientes(Request $request)
    {

        // R4.3 ID_RECETA se utiliza para identificar la receta cuyos ingredientes se  desean ajustar. 
        // El sistema revisa que corresponda a una receta existente; si no  la encuentra, selecciona el id más cercano, 
        //si no encuentra nada, muestra el mensaje “No se ha encontrado ninguna receta”
        $ids_recetas = DB::table('receta')
            ->orderBy('id_receta')
            ->pluck('id_receta');



        if ($ids_recetas->isEmpty()){
            $sin_recetas = true; //si no se obtiene ningun id hacemos que sin_recetas sea true para hacer un caso de excepción

            //para algunos atributos entregamos un mensaje especial y para otros simplemente null
            return view('ajuste_ingredientes', [
                'sin_recetas' => true,
                'receta' => (object)[

                'id_receta' => null,
                'nombre_receta' => 'No se ha encontrado ninguna receta.',
                'descripcion_corta' => null,
                'cant_personas' => null
                ],
                
                'ingredientes' => null,
                'pasos' => null,
                'ids_recetas' => null,
                'id_anterior' => null,
                'id_siguiente' => null
            ]   ); 
        }

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

        $receta = DB::table('receta')         // obtenemos la receta y buscamos por el id seleccionado 
            ->where('id_receta', $id_receta) ->first();

        abort_if(!$receta, 404, 'Receta no encontrada.');

        // R4.4 El sistema recupera de la base de datos CANT_PERSONAS (definida en  R1.8) 
        //y la lista de INGREDIENTES de la receta, compuesta por  NOMBRE_INGREDIENTE (R1.4), CANTIDAD_INGREDIENTE (R1.5) ,  UNIDAD_MEDIDA (R1.6) de cada ingrediente 
        //y tanto DESCRIPCIÓN_PASO (R1.11) como tambien PASO_NUMERO (R1.12).
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
            'sin_recetas' => false,
            'receta' => $receta,
            'ingredientes' => $ingredientes,
            'pasos' => $pasos,
            'ids_recetas' => $ids_recetas,
            'id_anterior' => $id_anterior,
            'id_siguiente' => $id_siguiente
        ]);
    }
}
