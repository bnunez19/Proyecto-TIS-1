<?php
namespace App\Http\Controllers;
use App\Models\Receta;                    //tabla receta: busca la receta y actualiza su promedio y cantidad de calificaciones
use App\Models\Usuario;                   //tabla usuario: revisa que la persona que califica este registrada (R2.6)
use Illuminate\Http\RedirectResponse;     //respuesta que redirige a la pagina de valoracion despues de guardar
use Illuminate\Http\Request;              //trae los datos enviados por el formulario: id_receta, id_usuario y calificacion
use Illuminate\Support\Facades\DB;        //consultas directas y DB::transaction para guardar todo o nada

/*
Controlador para la vista valorar_receta.blade.php de la función 2 Valoracion de recetas 
Responsable: Bastián Núñez
*/
class CalificacionController extends Controller
{
    //limite maximo que soporta la columna integer de PostgreSQL para ID_RECETA
    private const MAX_ID_RECETA = 2147483647;

    //muestra el formulario, los usuarios registrados y las recetas disponibles para calificar
    public function index(Request $request)
    {
        $usuarios = Usuario::orderBy('id_usuario')->get(); //R2.6: muestra solo usuarios registrados en el sistema
        $recetas = Receta::orderBy('nombre_receta')->get(['id_receta', 'nombre_receta']); //R2.7: permite elegir una receta existente

        //lee los datos enviados al seleccionar un usuario o una receta en la vista
        $idUsuario = trim((string) $request->query('id_usuario', ''));
        $idReceta = trim((string) $request->query('id_receta', ''));
        $receta = null;
        $errorReceta = null;
        $calificacionPrevia = null;

        //R2.7: busca la receta solo cuando se selecciono una en el formulario
        if ($idReceta !== '') {
            $receta = $this->buscarReceta($idReceta);

            if ($receta === null) {
                $errorReceta = 'La receta consultada no existe';
            } elseif ($idUsuario !== '') {
                //R2.8: revisa si el usuario ya habia calificado esta receta para informar que su nota sera reemplazada
                $calificacionPrevia = DB::table('calificacion')
                    ->where('id_receta', $receta->id_receta)
                    ->where('id_usuario', $idUsuario)
                    ->value('calificacion');
            }
        }

        //Salida 1: envia los datos necesarios a la vista valorar_receta.blade.php
        return view('valorar_receta', compact(
            'usuarios', 'recetas', 'receta', 'idUsuario', 'idReceta',
            'calificacionPrevia', 'errorReceta',
        ));
    }

    //guarda la calificacion elegida y actualiza el promedio y el conteo de la receta
    public function guardar(Request $request): RedirectResponse
    {
        //R2.5: obtiene los tres datos obligatorios enviados por el formulario
        $idReceta = trim((string) $request->input('id_receta', ''));
        $idUsuario = trim((string) $request->input('id_usuario', ''));
        $calificacion = $request->input('calificacion');
        $errores = [];

        //R2.2 y R2.5: revisa que los datos obligatorios esten presentes y que CALIFICACION este entre 1 y 5
        if ($idReceta === '') {
            $errores['id_receta'] = 'El campo ID_RECETA es obligatorio.';
        }
        if ($idUsuario === '') {
            $errores['id_usuario'] = 'El campo ID_USUARIO es obligatorio.';
        }
        if ($calificacion === null || $calificacion === '') {
            $errores['calificacion'] = 'El campo CALIFICACION es obligatorio.';
        } elseif (filter_var($calificacion, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]) === false) {
            $errores['calificacion'] = 'El valor de CALIFICACION debe estar entre 1 y 5.';
        }

        //R2.6 y R2.7: revisa que el usuario y la receta seleccionados existan en la base de datos
        if (! isset($errores['id_usuario']) && ! Usuario::where('id_usuario', $idUsuario)->exists()) {
            $errores['id_usuario'] = 'Usuario no registrado en el sistema';
        }
        if (! isset($errores['id_receta']) && $this->buscarReceta($idReceta) === null) {
            $errores['id_receta'] = 'La receta consultada no existe';
        }

        //conserva las selecciones al volver a la vista si existe algun error
        $volver = ['id_usuario' => $idUsuario, 'id_receta' => $idReceta];
        if (! empty($errores)) {
            return redirect()->route('valorar', $volver)->withErrors($errores);
        }

        $idReceta = (int) $idReceta;
        $calificacion = (int) $calificacion;

        //R2.8, R2.9 y R2.10: guarda todo junto para que la nota, el promedio y el conteo no queden desactualizados
        DB::transaction(function () use ($idReceta, $idUsuario, $calificacion): void {
            //bloquea la receta mientras se guarda la nota y se calculan sus nuevas metricas
            Receta::where('id_receta', $idReceta)->lockForUpdate()->firstOrFail();

            //R2.8: reemplaza la nota si el usuario ya califico; si no, inserta una nueva sin duplicar el par usuario-receta
            DB::table('calificacion')->updateOrInsert(
                ['id_receta' => $idReceta, 'id_usuario' => $idUsuario],
                ['calificacion' => $calificacion],
            );

            //R2.3 y R2.4: obtiene la suma implicita, el promedio y la cantidad de calificaciones vigentes de la receta
            $metricas = DB::table('calificacion')
                ->where('id_receta', $idReceta)
                ->selectRaw('COUNT(*) as cantidad, AVG(calificacion) as promedio')
                ->first();

            //R2.10: actualiza los datos que se muestran en el detalle y en el top 10
            Receta::where('id_receta', $idReceta)->update([
                'cantidad_calificaciones' => (int) $metricas->cantidad,
                'promedio_calificacion' => round((float) $metricas->promedio, 1, PHP_ROUND_HALF_UP),
            ]);
        });

        //R2.11: vuelve a la pagina de valoracion con el mensaje de exito y las selecciones conservadas
        return redirect()->route('valorar', ['id_usuario' => $idUsuario, 'id_receta' => $idReceta])
            ->with('exito', 'Calificación registrada exitosamente');
    }
    //R1.1 y R2.7: devuelve la receta con sus regimenes o null si el ID_RECETA no es valido o no existe
    private function buscarReceta(string $idReceta): ?Receta
    {
        if (! ctype_digit($idReceta) || (int) $idReceta < 1 || (int) $idReceta > self::MAX_ID_RECETA) {
            return null;
        }
        return Receta::with('regimenes')->find((int) $idReceta);
    }
}