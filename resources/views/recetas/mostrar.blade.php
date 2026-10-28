<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $recetas->nombre_receta }}</title>
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
        <h1 class="h3 mb-4 fw-bold">{{ $recetas->nombre_receta }}</h1>

        {{-- muestra los errores si los hay --}}
        @if ($errors->any())
            <div class="alert alert-danger fw-bold" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="card shadow-sm p-4 mb-4 border-0">
            {{-- imagen guardada por R.1 en storage (necesita php artisan storage:link) --}}
            <p><img src="{{ asset('storage/' . $recetas->imagen_referencial) }}" alt="Imagen de la receta" class="img-fluid rounded" style="max-height: 320px;"></p>

            <h3 class="h5 mb-3 fw-bold">Detalle de la receta</h3>
            <p class="mb-1"><span class="fw-bold">Descripción:</span> {{ $recetas->descripcion_corta }}</p>
            <p class="mb-1"><span class="fw-bold">Promedio:</span> {{ $recetas->promedio_calificacion }}</p>
            <p class="mb-1"><span class="fw-bold">Cantidad de calificaciones:</span> {{ $recetas->cantidad_calificaciones }}</p>
            <p class="mb-4">
                <span class="fw-bold">Link:</span>
                <a href="/mostrar/{{ $recetas->id_receta }}" class="text-decoration-none fw-bold" style="color: #e88d51;">{{ $recetas->nombre_receta }}</a>
            </p>

            <h3 class="h5 mb-3 fw-bold">Regímenes</h3>
            <ul class="list-group list-group-flush mb-4">
                @forelse ($recetas->regimenes as $reg)
                    <li class="list-group-item px-0">{{ $reg->regimen_alimenticio }}</li>
                @empty
                    <li class="list-group-item px-0 text-muted">Esta receta no tiene regímenes asignados.</li>
                @endforelse
            </ul>

            <div>
                <a href="/lista" class="btn btn-outline-secondary fw-bold">Volver a la lista</a>
            </div>
        </div>
    </main>
</body>
</html>
