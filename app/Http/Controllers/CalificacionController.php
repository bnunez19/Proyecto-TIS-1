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
    // R1.1: limite superior del ID_RECETA.
    private const MAX_ID_RECETA = 2147483647;
 
    // muestra los usuarios, las recetas y los datos de la receta que se quiere calificar
    public function index(Request $request)
    {
        // R2.6: lista de usuarios registrados que alimenta el <select> de la vista
        $usuarios = Usuario::orderBy('id_usuario')->get();
        $recetas = Receta::orderBy('nombre_receta')->get(['id_receta', 'nombre_receta']);
 
        // R2.1: ID_USUARIO se recibe como texto 
        $idUsuario = trim((string) $request->query('id_usuario', ''));
        $idReceta = trim((string) $request->query('id_receta', ''));
        $receta = null;
        $errorReceta = null;
        $calificacionPrevia = null;
 
        // busca la receta solo cuando el usuario selecciono una en el formulario
        if ($idReceta !== '') {
            // R1.1 y R2.7: valida formato del id y existencia de la receta
            $receta = $this->buscarReceta($idReceta);
 
            if ($receta === null) {
                // R2.7: mensaje exigido cuando la receta no existe
                $errorReceta = 'La receta consultada no existe';
            } elseif ($idUsuario !== '') {
                // R2.8: revisa si el usuario ya habia calificado esta receta (par ID_USUARIO + ID_RECETA)
                $calificacionPrevia = DB::table('calificacion')
                    ->where('id_receta', $receta->id_receta)
                    ->where('id_usuario', $idUsuario)
                    ->value('calificacion');
            }
        }
 
        // envia los datos necesarios a la vista valorar_receta.blade.php
        // Salida 1: la vista muestra promedio, cantidad y calificacion previa de la receta
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
 
        // R2.5: ID_RECETA, ID_USUARIO y CALIFICACION son obligatorios
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
            // R2.2: CALIFICACION debe ser un entero en el rango [1-5]
            $errores['calificacion'] = 'El valor de CALIFICACION debe estar entre 1 y 5.';
        }
 
        // revisa que el usuario y la receta seleccionados existan en la base de datos
        // R2.6: el usuario debe estar registrado; si no, no se permite calificar
        if (! isset($errores['id_usuario']) && ! Usuario::where('id_usuario', $idUsuario)->exists()) {
            $errores['id_usuario'] = 'Usuario no registrado en el sistema';
        }
        // R2.7: la receta debe existir (buscarReceta tambien aplica R1.1); si no, no se permite calificar
        if (! isset($errores['id_receta']) && $this->buscarReceta($idReceta) === null) {
            $errores['id_receta'] = 'La receta consultada no existe';
        }
 
        // si hay errores se vuelve a la vista con los mensajes (Salida 2) 
        $volver = ['id_usuario' => $idUsuario, 'id_receta' => $idReceta];
        if (! empty($errores)) {
            return redirect()->route('valorar', $volver)->withErrors($errores);
        }
 
        // R2.2: conversion a entero una vez validados los datos
        $idReceta = (int) $idReceta;
        $calificacion = (int) $calificacion;
 
        // R2.10: todo el calculo ocurre dentro de una transaccion para que nota, promedio y conteo queden consistentes
    
        DB::transaction(function () use ($idReceta, $idUsuario, $calificacion): void {
            // R2.7: firstOrFail es una segunda barrera si la receta dejara de existir
            Receta::where('id_receta', $idReceta)->lockForUpdate()->firstOrFail();
 
            // R2.8: si ya existe el par ID_USUARIO + ID_RECETA reemplaza la nota; si no, inserta una nueva.
            // R2.9: almacena la nueva CALIFICACION en la base de datos
            DB::table('calificacion')->updateOrInsert(
                ['id_receta' => $idReceta, 'id_usuario' => $idUsuario],
                ['calificacion' => $calificacion],
            );
 
            // R2.4 y R2.10: CANTIDAD_CALIFICACIONES = COUNT(*)
            // R2.10: PROMEDIO_CALIFICACION = AVG = suma de notas / cantidad
            // obtiene el promedio con un decimal y la cantidad de calificaciones de la receta
            $metricas = DB::table('calificacion')
                ->where('id_receta', $idReceta)
                ->selectRaw('COUNT(*) as cantidad, AVG(calificacion) as promedio')
                ->first();
 
            // R2.9: guarda la cantidad de calificaciones junto con su ID_RECETA
            // R2.3: promedio con maximo 1 decimal y redondeo estandar (0,5 hacia arriba)
            // R2.10: calculo inmediato al guardar (tambien sustenta el rendimiento del Top 10, R.3)
            // actualiza los datos que se muestran en el detalle y en el top 10
            Receta::where('id_receta', $idReceta)->update([
                'cantidad_calificaciones' => (int) $metricas->cantidad,
                'promedio_calificacion' => round((float) $metricas->promedio, 1, PHP_ROUND_HALF_UP),
            ]);
        });
 
        // R2.11: mensaje de exito "Calificación registrada exitosamente"
        // vuelve a la pagina de valoracion con la receta y el usuario seleccionados
        return redirect()->route('valorar', ['id_usuario' => $idUsuario, 'id_receta' => $idReceta])
            ->with('exito', 'Calificación registrada exitosamente');
    }
 
    // R2.7: devuelve la receta con sus regimenes o null si el id no es valido o no existe
    private function buscarReceta(string $idReceta): ?Receta
    {
        if (! ctype_digit($idReceta) || (int) $idReceta < 1 || (int) $idReceta > self::MAX_ID_RECETA) {
            return null;
        }
        return Receta::with('regimenes')->find((int) $idReceta);
    }
}