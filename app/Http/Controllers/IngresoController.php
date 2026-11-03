<?php
namespace App\Http\Controllers;
use App\Models\Ingrediente;
use App\Models\PasoPreparacion;
use App\Models\Receta;
use App\Models\RecetaIngrediente;
use App\Models\RecetaRegimen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

//funcion de ingreso de recetas
class IngresoController extends Controller
{
    //R1.6, R1.9 y R1.10: busca en la base de datos los valores de cada lista cerrada
    private function valores_enum($tabla, $columna)
    {
        $filas = DB::select(
            "select enumlabel from pg_enum
            where enumtypid = (select atttypid from pg_attribute where attrelid = to_regclass(?) and attname = ?)
            order by enumsortorder",
            [$tabla, $columna]
        );
        return array_column($filas, 'enumlabel');
    }

    //muestra el formulario
    public function create()
    {
        return view('ingreso_receta', [
            'categorias' => $this->valores_enum('receta', 'categoria'),
            'regimenes'  => $this->valores_enum('receta_regimen', 'regimen_alimenticio'),
            'unidades'   => $this->valores_enum('receta_ingrediente', 'unidad_medida'),
        ]);
    }

    //guarda la receta cuando se presiona el boton
    public function store(Request $request)
    {
        $categorias = $this->valores_enum('receta', 'categoria');
        $regimenes = $this->valores_enum('receta_regimen', 'regimen_alimenticio');
        $unidades = $this->valores_enum('receta_ingrediente', 'unidad_medida');

        //R1.22 y Salida 2: si algun dato no cumple, no se guarda y se muestran los mensajes
    
        $request->validate([
            //R1.2 y R1.15: nombre entre 3 y 60 caracteres, solo letras, numeros, espacios, guion y coma
            'nombre_receta' => ['bail', 'required', 'string', 'between:3,60', 'regex:/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ0-9,\- ]+$/u'],
            //R1.3 y R1.15: descripcion entre 10 y 150 caracteres
            'descripcion_corta' => ['bail', 'required', 'string', 'between:10,150'],
            //R1.7, R1.15 y R1.19: archivo jpg, jpeg o png, maximo 5 MB y minimo 150x150
            'imagen_referencial' => ['bail', 'required', 'file', 'mimes:jpg,jpeg,png', 'max:5120', 'dimensions:min_width=150,min_height=150'],
            //R1.8 y R1.15: entero entre 1 y 50
            'cant_personas' => ['bail', 'required', 'integer', 'between:1,50'],
            //R1.9 y R1.17: una sola categoria de la lista
            'categoria' => ['bail', 'required', Rule::in($categorias)],
            //R1.10 y R1.17: uno o mas regimenes de la lista
            'regimen' => ['bail', 'required', 'array'],
            'regimen.*' => [Rule::in($regimenes)],
            //R1.16: entre 1 y 50 ingredientes
            'ingredientes' => ['bail', 'required', 'array', 'between:1,50'],
            //R1.4 y R1.16: nombre del ingrediente entre 2 y 40 caracteres, solo letras, numeros, espacios, guion, punto y coma
            'ingredientes.*.nombre' => ['bail', 'required', 'string', 'between:2,40', 'regex:/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ0-9 .,\-]+$/u'],
            //R1.5 y R1.16: cantidad entre 0,01 y 10.000 con maximo 2 decimales
            'ingredientes.*.cantidad' => ['bail', 'required', 'numeric', 'between:0.01,10000', 'decimal:0,2'],
            //R1.6 y R1.16: unidad de la lista
            'ingredientes.*.unidad' => ['bail', 'required', Rule::in($unidades)],
            //R1.18: entre 1 y 30 pasos
            'pasos' => ['bail', 'required', 'array', 'between:1,30'],
            //R1.11 y R1.18: cada paso entre 5 y 300 caracteres
            'pasos.*' => ['bail', 'required', 'string', 'between:5,300'],
        ], [
            //R1.22 y Salida 2: mensajes de error
            'nombre_receta.required' => 'El campo NOMBRE_RECETA es obligatorio.',
            'nombre_receta.between' => 'El campo NOMBRE_RECETA debe contener entre 3 y 60 caracteres.',
            'nombre_receta.regex' => 'El campo NOMBRE_RECETA solo admite letras, numeros, espacios, guion (-) y coma (,).',

            'descripcion_corta.required' => 'El campo DESCRIPCION_CORTA es obligatorio.',
            'descripcion_corta.between' => 'El campo DESCRIPCION_CORTA debe contener entre 10 y 150 caracteres.',

            //R1.19: mensaje cuando no se adjunta la imagen
            'imagen_referencial.required' => 'Debe adjuntar una imagen referencial de la receta',
            'imagen_referencial.file' => 'El archivo adjunto no cumple con el formato o dimensiones requeridas.',
            'imagen_referencial.uploaded' => 'El archivo adjunto no cumple con el formato o dimensiones requeridas.',
            'imagen_referencial.mimes' => 'El archivo adjunto no cumple con el formato o dimensiones requeridas.',
            'imagen_referencial.max' => 'El archivo adjunto no cumple con el formato o dimensiones requeridas.',
            'imagen_referencial.dimensions' => 'El archivo adjunto no cumple con el formato o dimensiones requeridas.',

            'cant_personas.required' => 'El campo CANT_PERSONAS es obligatorio.',
            'cant_personas.integer' => 'El valor de CANT_PERSONAS debe estar entre 1 y 50.',
            'cant_personas.between' => 'El valor de CANT_PERSONAS debe estar entre 1 y 50.',

            'categoria.required' => 'El campo CATEGORIA es obligatorio.',
            'categoria.in' => 'El campo CATEGORIA tiene un valor no valido.',

            'regimen.required' => 'El campo REGIMEN_ALIMENTICIO es obligatorio.',
            'regimen.*.in' => 'El campo REGIMEN_ALIMENTICIO tiene un valor no valido.',

            'ingredientes.required' => 'La receta debe tener entre 1 y 50 ingredientes.',
            'ingredientes.between' => 'La receta debe tener entre 1 y 50 ingredientes.',

            'ingredientes.*.nombre.required' => 'El campo NOMBRE_INGREDIENTE es obligatorio.',
            'ingredientes.*.nombre.between' => 'El campo NOMBRE_INGREDIENTE debe contener entre 2 y 40 caracteres.',
            'ingredientes.*.nombre.regex' => 'El campo NOMBRE_INGREDIENTE solo admite letras, numeros, espacios, guion (-), punto (.) y coma (,).',

            'ingredientes.*.cantidad.required' => 'El campo CANTIDAD_INGREDIENTE es obligatorio.',
            'ingredientes.*.cantidad.numeric' => 'El valor de CANTIDAD_INGREDIENTE debe estar entre 0,01 y 10.000.',
            'ingredientes.*.cantidad.between' => 'El valor de CANTIDAD_INGREDIENTE debe estar entre 0,01 y 10.000.',
            'ingredientes.*.cantidad.decimal' => 'El valor de CANTIDAD_INGREDIENTE debe tener como maximo 2 decimales.',

            'ingredientes.*.unidad.required' => 'El campo UNIDAD_MEDIDA es obligatorio.',
            'ingredientes.*.unidad.in' => 'El campo UNIDAD_MEDIDA tiene un valor no valido.',

            'pasos.required' => 'La receta debe tener entre 1 y 30 pasos de preparacion.',
            'pasos.between' => 'La receta debe tener entre 1 y 30 pasos de preparacion.',

            'pasos.*.required' => 'El campo DESCRIPCION_PASO es obligatorio.',
            'pasos.*.between' => 'El campo DESCRIPCION_PASO debe contener entre 5 y 300 caracteres.',
        ]);

        //R1.20: no puede existir otra receta con el mismo nombre
        $existe = Receta::whereRaw('lower(unaccent(nombre_receta)) = lower(unaccent(?))', [$request->nombre_receta])->exists();
        if ($existe) {
            return back()->withInput()->withErrors(['nombre_receta' => 'Ya existe una receta con este nombre. Ingrese un nombre distinto']);
        }

        //un ingrediente no se puede repetir dentro de la misma receta
        $nombres = [];
        foreach ($request->ingredientes as $ing) {
            $nombres[] = mb_strtolower(trim($ing['nombre']));
        }
        if (count($nombres) != count(array_unique($nombres))) {
            return back()->withInput()->withErrors(['ingredientes' => 'No puede repetir un ingrediente en la misma receta.']);
        }

        //R1.21: guarda la receta completa
        $receta = DB::transaction(function () use ($request) {

            //R1.7: la imagen se guarda en storage/app/public/recetas y en la bd queda su ruta
            $ruta = $request->file('imagen_referencial')->store('recetas', 'public');

            //R1.1 y R1.14: id_receta (entero positivo) no se envia, lo crea la bd y no se puede modificar
            $receta = Receta::create([
                'nombre_receta'      => $request->nombre_receta,
                'descripcion_corta'  => $request->descripcion_corta,
                'imagen_referencial' => $ruta,
                'link_receta'        => 'pendiente',
                'cant_personas'      => $request->cant_personas,
                'categoria'          => $request->categoria,
            ]);

            //R1.13: con el id ya creado se arma el link (http y con el nombre de la receta)
            $link = 'http://' . $request->getHttpHost() . '/receta/' . $receta->id_receta . '/' . Str::slug($request->nombre_receta);
            $receta->update(['link_receta' => $link]);

            //R1.16: guarda los ingredientes (si ya existe el ingrediente lo reutiliza)
            foreach ($request->ingredientes as $ing) {
                $ingrediente = Ingrediente::firstOrCreate(['nombre_ingrediente' => trim($ing['nombre'])]);
                RecetaIngrediente::create([
                    'id_receta'            => $receta->id_receta,
                    'id_ingrediente'       => $ingrediente->id_ingrediente,
                    'cantidad_ingrediente' => $ing['cantidad'],
                    'unidad_medida'        => $ing['unidad'],
                ]);
            }

            //R1.12 y R1.18: el numero de paso lo pone el sistema, desde 1
            foreach (array_values($request->pasos) as $i => $texto) {
                PasoPreparacion::create([
                    'id_receta'        => $receta->id_receta,
                    'paso_numero'      => $i + 1,
                    'descripcion_paso' => $texto,
                ]);
            }

            //R1.10: guarda los regimenes marcados
            foreach (array_unique($request->regimen) as $reg) {
                RecetaRegimen::create(['id_receta' => $receta->id_receta, 'regimen_alimenticio' => $reg,]);
            }

            return $receta;
        });

        //R1.21 y Salida 2: el mensaje de exito aparece solo despues de guardar en la bd
        return redirect()->route('receta.mostrar', ['id' => $receta->id_receta])->with('exito', 'Receta ingresada exitosamente');
    }

    //R1.13 y Salida 1: pagina a la que lleva el link, muestra la receta completa
    public function show($id, $slug = null)
    {
        $receta = Receta::with(['ingredientes.ingrediente', 'pasos', 'regimenes'])->findOrFail($id);
        return view('ver_receta', compact('receta'));
    }
}
