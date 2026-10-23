<?php
namespace App\Http\Controllers;
use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//funcion de busqueda y filtro
class BusquedaController extends Controller
{
    //busca los valores de categoria y regimenes directamente de la base de datos
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

        $nombre = $request->input('nombre');
        $categoria = $request->input('categoria');
        $regimen = array_filter((array) $request->input('regimen', []));

        //condicion donde los tres datos estan vacios
        if ($nombre === '' && empty($categoria) && empty($regimen)){
            return view('buscar', $datosVista + ['resultados' => null]) -> withErrors(['criterios' => 'Debe ingresar al menos un criterio de busqueda']);
        }

        //carga tanto las recetas como los regimenes
        $consulta = Receta::with('regimenes');

        //busqueda por nombre (que no cuente las tildes ni las mayusculas)
        if ($nombre !== '') {
            $consulta->whereRaw('unaccent(nombre_receta) ILIKE unaccent(?)', ['%' . addcslashes($nombre, '%_\\') . '%']);
        }

        //filtro por categoria
        if (!empty($categoria)) {
            $consulta->where('categoria', $categoria);
        }

        //filtro por regimenes
        foreach ($regimen as $reg) {
            $consulta->whereExists(function ($consul) use ($reg) {
                $consul->selectRaw('1')->from('receta_regimen')->whereColumn('receta_regimen.id_receta', 'receta.id_receta')->where('receta_regimen.regimen_alimenticio', $reg);
            });
        }

        //ordenar por orden alfabetico los resultados
        $resultados = $consulta->orderBy('nombre_receta')->get();

        //los resultados van a la vista
        return view('buscar', $datosVista + ['resultados' => $resultados]);
    }
}