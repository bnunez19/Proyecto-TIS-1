<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalificacionController extends Controller
{
    // Carga la receta y la vista de valoración
    public function index($id_receta = 1)
    {
        $receta = DB::table('RECETA')->where('ID_RECETA', $id_receta)->first();
        // Obtenemos nota previa si existe para mostrarla en la vista
        $id_usuario = 'usuario_demo';
        $calificacionExistente = DB::table('CALIFICACION')
            ->where('ID_RECETA', $id_receta)
            ->where('ID_USUARIO', $id_usuario)
            ->value('CALIFICACION');
        return view('valorar_receta', [
            'receta' => $receta,
            'id_usuario' => $id_usuario,
            'calificacionPrevia' => $calificacionExistente
        ]);
    }
    // Guarda o reemplaza la nota y actualiza métricas de la receta
    public function guardar(Request $request)
    {
        $id_receta    = $request->input('ID_RECETA', 1);
        $id_usuario   = $request->input('ID_USUARIO', 'usuario_demo');
        $calificacion = (int) $request->input('CALIFICACION');
        // 1. Guardar o reemplazar calificación (evita duplicados del par usuario-receta)
        DB::table('CALIFICACION')->updateOrInsert(
            [
                'ID_RECETA'  => $id_receta,
                'ID_USUARIO' => $id_usuario
            ],
            [
                'CALIFICACION' => $calificacion
            ]
        );
        // 2. Recalcular Promedio y Cantidad para la tabla RECETA
        $notas = DB::table('CALIFICACION')
            ->where('ID_RECETA', $id_receta)
            ->pluck('CALIFICACION');
        $cantidad = $notas->count();
        $promedio = round($notas->avg(), 1, PHP_ROUND_HALF_UP);
        // 3. Actualizar la receta
        DB::table('RECETA')
            ->where('ID_RECETA', $id_receta)
            ->update([
                'CANTIDAD_CALIFICACIONES' => $cantidad,
                'PROMEDIO_CALIFICACION'   => $promedio
            ]);
        return back()->with('exito', 'Calificación registrada exitosamente');
    }
}