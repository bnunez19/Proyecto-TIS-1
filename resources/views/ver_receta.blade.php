<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{$receta->nombre_receta}}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-pastel-naranja {
            background-color: #ffab76;
            border-color: #ffab76;
            color: #5c3a21;
            font-weight: 700;
        }
        .btn-pastel-naranja:hover {
            background-color: #f7955b;
            border-color: #f7955b;
            color: #fff;
        }
    </style>
</head>
<body class="bg-light">
    <main class="container py-4" style="max-width: 800px;">
        {{-- R1.21 y Salida 2: mensaje de exito (solo aparece despues de guardar en la bd) --}}
        @if (session('exito'))
            <div class="alert alert-success fw-bold" role="alert">
                {{session('exito')}}
            </div>
        @endif

        <div class="card shadow-sm p-4 mb-4 border-0">
            {{-- R1.13: esta pagina es la que abre LINK_RECETA (informacion completa de la receta) --}}
            {{-- Salida 1: todos los datos de la receta y su ID_RECETA --}}
            <h1 class="h3 mb-2 fw-bold">{{$receta->nombre_receta}}</h1>
            <p class="text-muted small fw-bold">ID de la receta: {{$receta->id_receta}}</p>
            <p class="lead fs-6"><span class="fw-bold">Descripción:</span> {{$receta->descripcion_corta}}</p>
            
            @if($receta->imagen_referencial)
                <p class="text-center">
                    <img src="{{asset('storage/' . $receta->imagen_referencial)}}" alt="Imagen de la receta" class="img-fluid rounded shadow-sm" width="300">
                </p>
            @endif

            <p><span class="fw-bold">Cantidad de personas:</span> {{$receta->cant_personas}}</p>
            <p><span class="fw-bold">Categoría:</span> {{$receta->categoria}}</p>
            <p><span class="fw-bold">Régimen alimenticio:</span> {{$receta->regimenes->pluck('regimen_alimenticio')->implode(', ')}}</p>

            <h3 class="h5 mt-4 fw-bold">Ingredientes</h3>
            <ul>
                @foreach ($receta->ingredientes as $ri)
                    <li class="fw-semibold">{{$ri->ingrediente->nombre_ingrediente}}: <span class="fw-bold">{{rtrim(rtrim($ri->cantidad_ingrediente, '0'), '.')}} {{$ri->unidad_medida}}</span></li>
                @endforeach
            </ul>

            <h3 class="h5 mt-4 fw-bold">Pasos de preparación</h3>
            @foreach ($receta->pasos as $paso)
                <p><span class="fw-bold text-dark">{{$paso->paso_numero}}.</span> {{$paso->descripcion_paso}}</p>
            @endforeach

            <hr class="my-4">
            <a href="{{route('ingreso')}}" class="btn btn-pastel-naranja">Ingresar otra receta</a>
        </div>
        <div>
                <a href="/" class="btn btn-outline-secondary fw-bold">Volver al inicio</a>
        </div>
    </main>
</body>
</html>