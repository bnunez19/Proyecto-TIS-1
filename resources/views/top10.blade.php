<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Top 10 recetas</title>
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
    <main class="container py-4" style="max-width: 900px;">
        <h1 class="h3 mb-4 fw-bold">Buscar recetas mas populares por categoria</h1>

        <form action="/top10" method="GET" class="card shadow-sm mb-4 border-0">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-8">
                        <select name="categoria" class="form-select">
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat }}" @selected(request('categoria') === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-pastel-naranja w-100">Buscar</button>
                    </div>
                </div>
            </div>
        </form>

        @if ($receta !== null)
            <hr>
            <h2 class="h5 mb-3 fw-bold">Resultado de la búsqueda</h2>

            @forelse ($receta as $r)
            @if ($r->cantidad_calificaciones <=0 )
                <div class="alert alert-warning fw-bold" role="alert">
                    Aún no hay recetas calificadas en esta categoría. <!-- $loop->mensaje de salida 2 (3.6)-->
                </div>
            @else
                <div class="card shadow-sm mb-3 border-0">
                    <div class="card-body">
                        <p class="mb-1"><strong class="fw-bold">Nombre:</strong> {{ $r->nombre_receta }}</p>
                        <p class="mb-1"><strong class="fw-bold">Descripcion corta:</strong> {{ $r->descripcion_corta }}</p>
                        <p class="mb-1"><strong class="fw-bold">Promedio:</strong> {{ $r->promedio_calificacion}}</p>
                        <p class="mb-1"><strong class="fw-bold">Cantidad:</strong> {{ $r->cantidad_calificaciones }}</p>
                        <p class="mb-1"><strong class="fw-bold">Link:</strong> <a href="/mostrar/{{ $r->id_receta }}" class="text-decoration-none fw-bold" style="color: #e88d51;">{{ $r->nombre_receta }}</a></p>
                        <p class="mb-0"><strong class="fw-bold">Regimen alimenticio:</strong> 
                            @foreach ($r->regimenes as $reg)
                                {{ $reg->regimen_alimenticio }}@if (!$loop->last), @endif <!-- $loop->last evita dejar una coma sobrante al final.-->
                            @endforeach
                        </p>
                    </div>
                </div>
            @endif
            @empty
                <div class="alert alert-danger fw-bold" role="alert">
                    No se encontró ninguna receta en esa categoría. <!-- $loop->mensaje de salida 3 (3.7)-->
                </div>
            @endforelse
        @endif

        <div class="mt-4">
            <a href="/" class="btn btn-outline-secondary fw-bold">Volver al inicio</a>
        </div>
    </main>
</body>
</html>