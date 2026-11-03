<?php
namespace App\Http\Controllers;
use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
/*
Controlador para la vista buscar.blade.php de la función 5 Busqueda y Filtrado de recetas
Responsable: Leandro Torres
*/
class BusquedaController extends Controller
{
    //R1.9 y R1.10: busca los valores de categoria y regimenes directamente de la base de datos y los guarda
    private function valores_enum(string $tipo): array {
        $filas = DB::select("SELECT unnest(enum_range(NULL::$tipo)) as valor");
        return array_column($filas, 'valor');
    }

    public function index(Request $request)
    {
        $datosVista = ['categorias' => $this-> valores_enum('categoria_al'),
        'regimenes' => $this->valores_enum('regimen_alimenticio_al')];

        //es la visita a la pagina
        if (!$request->hasAny(['nombre', 'categoria', 'regimen'])){
            return view('buscar', $datosVista + ['resultados' => null]);
        }

        //R5.1: nombre_receta_busqueda
        $nombre = trim((string)$request->input('nombre'));
        $request->merge(['nombre' =>$nombre]);

        //R5.1: valida las condiciones de nombre_busqueda
        $validador = validator::make($request->all(), [
            'nombre' => ['nullable', 'string', 'min:1', 'max:60', 'regex:/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ0-9 ,\-]+$/u'],],[
            'nombre.min' => 'El nombre debe tener entre 1 y 60 caracteres',
            'nombre.max' => 'El nombre debe tener entre 1 y 60 caracteres',
            'nombre.regex' => 'El nombre solo admite letras, numeros, espacios, guion y coma', 
        ]);

        if($validador->fails()){
            return view('buscar', $datosVista + ['resultados' => null])->withErrors($validador);
        }

        $categoria = $request->input('categoria'); //R5.2: categoria_busqueda
        $regimen = array_filter((array) $request->input('regimen', [])); //R5.3: regimen_alimenticio_busqueda

        //R5.4 y R5.5: los tres campos pueden ser opcionales, pero si los tres estan vacios muestra un mensaje
        if ($nombre === '' && empty($categoria) && empty($regimen)){
            return view('buscar', $datosVista + ['resultados' => null]) -> withErrors(['criterios' => 'Debe ingresar al menos un criterio de búsqueda']);
        }

        //R5.6: consulta sobre la base de datos
        $consulta = Receta::with('regimenes');

        //R5.6: nombre_receta no distingue mayuscula, minusculas ni tilde, ademas de la coincidencia parcial
        if ($nombre !== '') {
            $consulta->whereRaw('unaccent(nombre_receta) ILIKE unaccent(?)', ['%' . addcslashes($nombre, '%_\\') . '%']);
        }

        //R5.6: la categoria_busqueda escogida coincide con categoria
        if (!empty($categoria)) {
            $consulta->where('categoria', $categoria);
        }

        //R5.6: se filtran las recetas que incluyan cada regimen_alimenticio que el usuario escoge
        foreach ($regimen as $reg) {
            $consulta->whereExists(function ($consul) use ($reg) {
                $consul->selectRaw('1')->from('receta_regimen')->whereColumn('receta_regimen.id_receta', 'receta.id_receta')->where('receta_regimen.regimen_alimenticio', $reg);
            });
        }

        //R5.7: los resultados se ordenan alfabeticamente por nombre_receta, de la A a la Z
        $resultados = $consulta->orderBy('nombre_receta')->orderBy('id_receta')->paginate(10)->withQueryString();

        //R5.8 y R5.9: la vista muestra salida 1 o, si no hay coincidencias, salida 2
        return view('buscar', $datosVista + ['resultados' => $resultados]);
    }
}