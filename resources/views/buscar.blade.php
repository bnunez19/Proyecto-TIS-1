<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar recetas</title>
</head>
<body>
    <h1>Buscar recetas</h1>

    {{-- busca y muestra los errores --}}
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <p>{{$error}}</p>
        @endforeach
    @endif

    {{-- poner nombre y seleccionar categoria y regimen --}}
    <form method="GET" action="{{route('buscar')}}">
        <p>
            <label for="nombre">Nombre de la receta:</label>
            <input type="text" id="nombre" name="nombre" value="{{request('nombre')}}">
        </p>

        <p>
            <label for="categoria">Categoría:</label>
            <select id="categoria" name="categoria">
                <option value="">Todas</option>
                    @foreach ($categorias as $cat)
                    <option value="{{$cat}}" @selected(request('categoria') === $cat)>{{$cat}}</option>
                @endforeach
            </select>
        </p>
 
        <p>
            Régimen alimenticio:<br>
            @foreach ($regimenes as $reg)
                <label>
                    <input type="checkbox" name="regimen[]" value="{{$reg}}"
                    @checked(in_array($reg, (array) request('regimen', [])))>
                    {{$reg}}
                </label>
            @endforeach
        </p>
 
        <button type="submit">Buscar</button>
        <a href="{{route('buscar')}}">Limpiar</a>
    </form>
 
    {{-- los resultados--}}
    @if(!is_null($resultados))
        <hr>
        <h2>Resultado de la búsqueda</h2>

        @if ($resultados->isEmpty())
            <p>No se encontraron recetas que coincidan con los criterios ingresados</p>
        @else
            @foreach ($resultados as $r)
            <p>Nombre: {{$r->nombre_receta}}</p>
            <p>Descripción corta: {{$r->descripcion_corta}}</p>
            <p>Categoría: {{$r->categoria}}</p>
            <p>Régimen alimenticio: {{$r->regimenes->pluck('regimen_alimenticio')->implode(', ')}}</p>
            <p>Link: <a href="{{$r->link_receta}}">{{$r->link_receta}}</a></p>
            <hr>
            @endforeach
        @endif
    @endif
</body>
</html>