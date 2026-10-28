<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// funcion que muestra y guarda las valoraciones de las recetas
class CalificacionController extends Controller
{
    // valor maximo del id que soporta la columna integer de PostgreSQL
    private const MAX_ID_RECETA = 2147483647;

    // muestra los usuarios, las recetas y los datos de la receta que se quiere calificar
    public function index(Request $request)
    {
        $usuarios = Usuario::orderBy('id_usuario')->get();
        $recetas = Receta::orderBy('nombre_receta')->get(['id_receta', 'nombre_receta']);

        $idUsuario = trim((string) $request->query('id_usuario', ''));
        $idReceta = trim((string) $request->query('id_receta', ''));
        $receta = null;
        $errorReceta = null;
        $calificacionPrevia = null;

        // busca la receta solo cuando el usuario selecciono una en el formulario
        if ($idReceta !== '') {
            $receta = $this->buscarReceta($idReceta);

            if ($receta === null) {
                $errorReceta = 'La receta consultada no existe';
            } elseif ($idUsuario !== '') {
                // revisa si el usuario ya habia calificado esta receta
                $calificacionPrevia = DB::table('calificacion')
                    ->where('id_receta', $receta->id_receta)
                    ->where('id_usuario', $idUsuario)
                    ->value('calificacion');
            }
        }

        // envia los datos necesarios a la vista valorar_receta.blade.php
        return view('valorar_receta', compact(
            'usuarios', 'recetas', 'receta', 'idUsuario', 'idReceta',
            'calificacionPrevia', 'errorReceta',
        ));
    }

    // guarda la calificacion elegida y actualiza el promedio y el conteo de la receta
    public function guardar(Request $request): RedirectResponse
    {
        $idReceta = trim((string) $request->input('id_receta', ''));
        $idUsuario = trim((string) $request->input('id_usuario', ''));
        $calificacion = $request->input('calificacion');
        $errores = [];

        // revisa que los tres datos obligatorios esten presentes y sean validos
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

        // revisa que el usuario y la receta seleccionados existan en la base de datos
        if (! isset($errores['id_usuario']) && ! Usuario::where('id_usuario', $idUsuario)->exists()) {
            $errores['id_usuario'] = 'Usuario no registrado en el sistema';
        }
        if (! isset($errores['id_receta']) && $this->buscarReceta($idReceta) === null) {
            $errores['id_receta'] = 'La receta consultada no existe';
        }

        $volver = ['id_usuario' => $idUsuario, 'id_receta' => $idReceta];
        if (! empty($errores)) {
            return redirect()->route('valorar', $volver)->withErrors($errores);
        }

        $idReceta = (int) $idReceta;
        $calificacion = (int) $calificacion;

        // guarda todo junto para que la nota, el promedio y el conteo no queden desactualizados
        DB::transaction(function () use ($idReceta, $idUsuario, $calificacion): void {
            // bloquea la receta mientras se guarda la nota y se calculan sus nuevas metricas
            Receta::where('id_receta', $idReceta)->lockForUpdate()->firstOrFail();

            // reemplaza la nota si el usuario ya califico; si no, inserta una nueva
            DB::table('calificacion')->updateOrInsert(
                ['id_receta' => $idReceta, 'id_usuario' => $idUsuario],
                ['calificacion' => $calificacion],
            );

            // obtiene el promedio con un decimal y la cantidad de calificaciones de la receta
            $metricas = DB::table('calificacion')
                ->where('id_receta', $idReceta)
                ->selectRaw('COUNT(*) as cantidad, AVG(calificacion) as promedio')
                ->first();

            // actualiza los datos que se muestran en el detalle y en el top 10
            Receta::where('id_receta', $idReceta)->update([
                'cantidad_calificaciones' => (int) $metricas->cantidad,
                'promedio_calificacion' => round((float) $metricas->promedio, 1, PHP_ROUND_HALF_UP),
            ]);
        });

        // vuelve a la pagina de valoracion con la receta y el usuario seleccionados
        return redirect()->route('valorar', ['id_usuario' => $idUsuario, 'id_receta' => $idReceta])
            ->with('exito', 'Calificación registrada exitosamente');
    }

    // devuelve la receta con sus regimenes o null si el id no es valido o no existe
    private function buscarReceta(string $idReceta): ?Receta
    {
        if (! ctype_digit($idReceta) || (int) $idReceta < 1 || (int) $idReceta > self::MAX_ID_RECETA) {
            return null;
        }

        return Receta::with('regimenes')->find((int) $idReceta);
    }
}
