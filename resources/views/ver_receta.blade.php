<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{$receta->nombre_receta}}</title>
</head>
<body>
    {{-- R1.21 y Salida 2: mensaje de exito (solo aparece despues de guardar en la bd) --}}
    @if (session('exito'))
        <p style="color: green;">{{session('exito')}}</p>
    @endif

    {{-- R1.13: esta pagina es la que abre LINK_RECETA (informacion completa de la receta) --}}
    {{-- Salida 1: todos los datos de la receta y su ID_RECETA --}}
    <h1>{{$receta->nombre_receta}}</h1>
    <p>ID de la receta: {{$receta->id_receta}}</p>
    <p>Descripción: {{$receta->descripcion_corta}}</p>
    <p><img src="{{asset('storage/' . $receta->imagen_referencial)}}" alt="Imagen de la receta" width="300"></p>
    <p>Cantidad de personas: {{$receta->cant_personas}}</p>
    <p>Categoría: {{$receta->categoria}}</p>
    <p>Régimen alimenticio: {{$receta->regimenes->pluck('regimen_alimenticio')->implode(', ')}}</p>

    <h3>Ingredientes</h3>
    <ul>
        @foreach ($receta->ingredientes as $ri)
            <li>{{$ri->ingrediente->nombre_ingrediente}}: {{rtrim(rtrim($ri->cantidad_ingrediente, '0'), '.')}} {{$ri->unidad_medida}}</li>
        @endforeach
    </ul>

    <h3>Pasos de preparación</h3>
    @foreach ($receta->pasos as $paso)
        <p>{{$paso->paso_numero}}. {{$paso->descripcion_paso}}</p>
    @endforeach

    <hr>
    <a href="{{route('ingreso')}}">Ingresar otra receta</a>
</body>
</html>
