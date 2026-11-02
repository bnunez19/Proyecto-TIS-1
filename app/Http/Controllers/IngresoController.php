<?php
namespace App\Http\Controllers; 
use App\Models\Ingrediente;        //tabla ingrediente: crea el ingrediente o lo reutiliza si ya existe
use App\Models\PasoPreparacion;    //tabla paso_preparacion: guarda cada paso con su numero (R1.12 y R1.18)
use App\Models\Receta;             //tabla receta: revisa nombre repetido (R1.20), crea la receta y la carga en show()
use App\Models\RecetaIngrediente;  //tabla receta_ingrediente: une la receta con cada ingrediente, con cantidad y unidad
use App\Models\RecetaRegimen;      //tabla receta_regimen: guarda cada regimen marcado (R1.10 y R1.17)
use Illuminate\Http\Request;       //trae todo lo que el usuario envio en el formulario ($request)
use Illuminate\Support\Facades\DB; //consultas directas a la bd: DB::transaction (todo o nada) y DB::select (valoresDe)
use Illuminate\Support\Str;        //ayudas para texto: Str::slug arma el nombre del link (R1.13) y Str::ascii compara sin tildes
use Illuminate\Validation\Rule;    //reglas de validacion avanzadas: Rule::in revisa que el valor este en la lista de la bd

//funcion de ingreso de recetas (R.1)
class IngresoController extends Controller
{

    //R1.2: letras del español (con tildes y ñ), numeros, espacios, guion y coma
    private const REGEX_NOMBRE = '/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ0-9,\- ]+$/u';

    //R1.6, R1.9 y R1.10: lee desde la bd los valores permitidos de una columna de tipo lista, con una subconsulta
    private function valoresDe($tabla, $columna)
    {
        return array_column(DB::select(
            "select enumlabel from pg_enum
            where enumtypid = (select atttypid from pg_attribute where attrelid = to_regclass(?) and attname = ?)
            order by enumsortorder",
            [$tabla, $columna]
        ), 'enumlabel');
    }

    //muestra el formulario
    public function create()
    {
        return view('ingreso_receta', [
        'categorias' => $this->valoresDe('receta', 'categoria'),                    //R1.9 lista cerrada de categorias
        'regimenes'  => $this->valoresDe('receta_regimen', 'regimen_alimenticio'),  //R1.10 lista cerrada de regimenes
        'unidades'   => $this->valoresDe('receta_ingrediente', 'unidad_medida'),    //R1.6 lista cerrada de unidades de medida
        ]);
    }

    //guarda la receta cuando se presiona el boton
    public function store(Request $request)
    {

        $categorias = $this->valoresDe('receta', 'categoria');
        $regimenes  = $this->valoresDe('receta_regimen', 'regimen_alimenticio');
        $unidades   = $this->valoresDe('receta_ingrediente', 'unidad_medida');
        //R1.22: si algun campo no cumple, se detiene todo, se marca el campo con error y se muestra el mensaje (la receta no se guarda)
        //R1.15: nombre y descripcion no pueden estar vacios ni solo con espacios (required los rechaza)
        $request->validate([
            //R1.2 (largo 3 a 60 y caracteres permitidos), R1.15 (obligatorio) y R1.20 (nombre repetido)
            //'bail' detiene la ejecución de validaciones para este campo al encontrar el primer error.
            'nombre_receta' => ['bail', 'required', 'string', 'between:3,60', 'regex:' . self::REGEX_NOMBRE, 
                //R1.20: revisa si ya existe otra receta con el mismo nombre, sin distinguir mayusculas ni tildes
                //unaccent() sirve para ignorar tildes en la comparación.
                function ($campo, $valor, $fail) {
                    $existe = Receta::whereRaw('lower(unaccent(nombre_receta)) = lower(unaccent(?))', [$valor])->exists();
                    if ($existe) {
                        $fail("Ya existe una receta con este nombre. Ingrese un nombre distinto (por ejemplo, agregando un diferenciador como 'de la Abuela')");
                    }
                },
            ],
            //R1.3 (largo 10 a 150) y R1.15 (obligatorio)
            'descripcion_corta' => ['bail', 'required', 'string', 'between:10,150'],
            //R1.7 (archivo jpg, jpeg o png, maximo 5 MB, minimo 400x400), R1.15 (obligatorio) y R1.19 (si falta, mensaje propio)
            'imagen_referencial' => ['bail', 'required', 'file', 'mimes:jpg,jpeg,png', 'max:5120', 'dimensions:min_width=150,min_height=150'],
            //R1.8 (entero entre 1 y 50) y R1.15 (obligatorio)
            'cant_personas' => ['bail', 'required', 'integer', 'between:1,50'],
            //R1.9 (lista cerrada), R1.17 (obligatoria y una sola opcion)
            'categoria'  => ['bail', 'required', Rule::in($categorias)],
            //R1.10 (lista cerrada) y R1.17 (obligatorio, una o mas opciones)
            'regimen' => ['bail', 'required', 'array'],
            'regimen.*'  => [Rule::in($regimenes)],

            //R1.16: entre 1 y 50 ingredientes
            'ingredientes' => ['bail', 'required', 'array', 'between:1,50',
                //la tabla receta_ingrediente no permite el mismo ingrediente dos veces en una receta
                function ($campo, $valor, $fail) {
                    $nombres = [];
                    foreach ((array) $valor as $ing) {
                        $nombres[] = Str::lower(Str::ascii(trim(is_array($ing) ? ($ing['nombre'] ?? '') : '')));
                    }
                    if (count($nombres) !== count(array_unique($nombres))) {
                        $fail('No puede repetir un ingrediente en la misma receta.');
                    }
                },
            ],
            //R1.4 (largo maximo 40) y R1.16 (obligatorio en cada ingrediente)
            'ingredientes.*.nombre' => ['bail', 'required', 'string', 'max:40'],
            //R1.5 (positivo, entre 0,01 y 10.000, maximo 2 decimales) y R1.16 (obligatorio)
            'ingredientes.*.cantidad' => ['bail', 'required', 'numeric', 'between:0.01,10000', 'decimal:0,2'],
            //R1.6 (lista cerrada) y R1.16 (obligatorio)
            'ingredientes.*.unidad' => ['bail', 'required', Rule::in($unidades)],  
            //R1.18: entre 1 y 30 pasos
            'pasos' => ['bail', 'required', 'array', 'between:1,30'],
            //R1.11 (largo 5 a 300) y R1.18 (obligatorio en cada paso)
            'pasos.*' => ['bail', 'required', 'string', 'between:5,300'],
        ], [
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
            'ingredientes.*.nombre.max' => 'El campo NOMBRE_INGREDIENTE debe contener como maximo 40 caracteres.',

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

        //R1.21: si todo cumple, se guarda la receta completa (si algo falla no se guarda nada)
        $receta = DB::transaction(function () use ($request) {

            //R1.7: la imagen se guarda como archivo en storage/app/public/recetas y en la bd queda su ruta
            $ruta = $request->file('imagen_referencial')->store('recetas', 'public');

            //R1.14 y R1.21: no se envia id_receta, lo genera la bd con su secuencia y no se puede modificar
            $receta = Receta::create([
                'nombre_receta'      => $request->nombre_receta,
                'descripcion_corta'  => $request->descripcion_corta,
                'imagen_referencial' => $ruta,
                'link_receta'        => 'pendiente',
                'cant_personas'      => $request->cant_personas,
                'categoria'          => $request->categoria,
            ]);

            //R1.13 y R1.21: con el id ya creado se arma el link: empieza con https:// e incluye el nombre de la receta en la ruta
            $link = 'https://' . $request->getHttpHost() . '/receta/' . $receta->id_receta . '/' . Str::slug($request->nombre_receta);
            $receta->update(['link_receta' => $link]);

            //R1.16: guarda los ingredientes (si el ingrediente ya existe lo reutiliza)
            foreach ($request->ingredientes as $ing) {
                //firstOrCreate busca el registro y, de no existir, lo inserta automáticamente en un solo paso.
                $ingrediente = Ingrediente::firstOrCreate(['nombre_ingrediente' => trim($ing['nombre'])]);
                RecetaIngrediente::create([
                    'id_receta'            => $receta->id_receta,
                    'id_ingrediente'       => $ingrediente->id_ingrediente,
                    'cantidad_ingrediente' => $ing['cantidad'],
                    'unidad_medida'        => $ing['unidad'],
                ]);
            }

            //R1.12 y R1.18: PASO_NUMERO lo asigna el sistema, correlativo desde 1 segun el orden ingresado
            foreach (array_values($request->pasos) as $i => $texto) {
                PasoPreparacion::create([
                    'id_receta'        => $receta->id_receta,
                    'paso_numero'      => $i + 1,
                    'descripcion_paso' => $texto,
                ]);
            }

            //R1.10 y R1.17: guarda los regimenes marcados
            foreach (array_unique($request->regimen) as $reg) {
                RecetaRegimen::create(['id_receta' => $receta->id_receta, 'regimen_alimenticio' => $reg,]);
            }

            return $receta;
        });

        //R1.21 y Salida 2: el mensaje de exito solo se muestra cuando la receta ya se guardo en la bd
        return redirect()->route('receta.mostrar', ['id' => $receta->id_receta])->with('exito', 'Receta ingresada exitosamente');
    }

    //R1.13 y Salida 1: pagina a la que apunta LINK_RECETA, muestra la informacion completa de la receta
    public function show($id, $slug = null)
    {
        $receta = Receta::with(['ingredientes.ingrediente', 'pasos', 'regimenes'])->findOrFail($id);

        return view('ver_receta', compact('receta'));
    }
}