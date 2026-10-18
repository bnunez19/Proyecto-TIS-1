<?php

namespace App\Http\Controllers;

use App\Models\Ingrediente;
use App\Models\PasoPreparacion;
use App\Models\Receta;
use App\Models\RecetaIngrediente;
use App\Models\RecetaRegimen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngresoController extends Controller
{
    public function create()
    {
        return view('ingreso_receta');
    }

    public function store(Request $request)
    {
        // 1. Validación básica
        $request->validate([
            'nombre_receta'      => ['required', 'string', 'min:3', 'max:60'],
            'descripcion_corta'  => ['required', 'string', 'max:150'],
            'imagen_referencial' => ['required', 'url', 'max:255'],
            'cant_personas'      => ['required', 'integer', 'between:1,50'],
            'categoria'          => ['required'],
            'regimen'            => ['required', 'array', 'min:1'],
            'ingredientes.*.cantidad' => ['required_with:ingredientes.*.nombre', 'nullable', 'numeric', 'between:0.01,10000'],
            'ingredientes.*.unidad'   => ['required_with:ingredientes.*.nombre', 'nullable'],
        ], [
            'required'      => 'Falta completar un campo obligatorio (:attribute).',
            'regimen.required' => 'Elija al menos un régimen alimenticio.',
            'url'           => 'La imagen debe ser un enlace válido (con https://).',
            'between'       => 'El valor de :attribute está fuera del rango permitido.',
        ]);

        // R1.20: nombre duplicado (sin distinguir mayúsculas ni tildes)
        $nombre = $request->input('nombre_receta');
        $existe = Receta::whereRaw('lower(unaccent(nombre_receta)) = lower(unaccent(?))', [$nombre])->exists();
        if ($existe) {
            return back()->withInput()->withErrors(['nombre_receta' => 'Ya existe una receta con ese nombre.']);
        }

        // 2. Nos quedamos solo con las filas que el usuario llenó
        $ingredientes = collect($request->input('ingredientes', []))
            ->filter(fn ($i) => !empty(trim($i['nombre'] ?? '')))
            ->values();
        $pasos = collect($request->input('pasos', []))
            ->filter(fn ($p) => !empty(trim($p ?? '')))
            ->values();

        if ($ingredientes->isEmpty()) {
            return back()->withInput()->withErrors(['ingredientes' => 'Ingrese al menos un ingrediente.']);
        }
        if ($pasos->isEmpty()) {
            return back()->withInput()->withErrors(['pasos' => 'Ingrese al menos un paso de preparación.']);
        }

        // 3. Guardado: todo o nada
        DB::transaction(function () use ($request, $ingredientes, $pasos) {
            $receta = Receta::create([
                'nombre_receta'      => $request->nombre_receta,
                'descripcion_corta'  => $request->descripcion_corta,
                'imagen_referencial' => $request->imagen_referencial,
                'link_receta'        => 'pendiente',
                'cant_personas'      => $request->cant_personas,
                'categoria'          => $request->categoria,
            ]);

            // El link se arma con el ID que acaba de generar la BD
            $receta->update(['link_receta' => url('/receta/' . $receta->id_receta)]);

            foreach ($ingredientes as $ing) {
                // Si el ingrediente ya existe se reutiliza, si no se crea
                $ingrediente = Ingrediente::whereRaw(
                    'lower(unaccent(nombre_ingrediente)) = lower(unaccent(?))', [trim($ing['nombre'])]
                )->first() ?? Ingrediente::create(['nombre_ingrediente' => trim($ing['nombre'])]);

                RecetaIngrediente::create([
                    'id_receta'            => $receta->id_receta,
                    'id_ingrediente'       => $ingrediente->id_ingrediente,
                    'cantidad_ingrediente' => $ing['cantidad'],
                    'unidad_medida'        => $ing['unidad'],
                ]);
            }

            foreach ($pasos as $i => $texto) {
                PasoPreparacion::create([
                    'id_receta'        => $receta->id_receta,
                    'paso_numero'      => $i + 1,
                    'descripcion_paso' => $texto,
                ]);
            }

            foreach ($request->regimen as $reg) {
                RecetaRegimen::create([
                    'id_receta'           => $receta->id_receta,
                    'regimen_alimenticio' => $reg,
                ]);
            }
        });

        return redirect()->route('ingreso')->with('exito', 'Receta ingresada correctamente.');
    }
}
