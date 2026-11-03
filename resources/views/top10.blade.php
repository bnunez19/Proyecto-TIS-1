<!DOCTYPE html>
{{-- Vista para la función 3 Listado de recetas populares --}}
{{-- Responsable: Diego Burgos --}}
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
                    <!-- R3.1 filtra las recetas según la CATEGORIA -->
                    <!-- R3.5 (NUNCA SE PUEDE ESCOGER FUERA DE LAS CATEGORIAS EN BD) -->

                        <select name="categoria" class="form-select @error('categoria') is-invalid @enderror">
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat }}" @selected(request('categoria') === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('categoria')
                            <div class="invalid-feedback fw-bold">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-pastel-naranja w-100">Buscar</button>
                    </div>
                </div>
            </div>
        </form>

        @if ($recetas !== null)
            <hr>
            <h2 class="h5 mb-3 fw-bold">Resultado de la búsqueda</h2>

            <!-- R3.4 muestra los datos pedidos de las recetas dentro del listado -->

            @if (!$hayRecetasEnCategoria)
                <div class="alert alert-danger fw-bold" role="alert">
                    No se encontró ninguna receta en esa categoría. <!-- mensaje de salida 3 (3.7)-->
                </div>
            @elseif ($recetas->isEmpty())
                <div class="alert alert-warning fw-bold" role="alert">
                    Aún no hay recetas calificadas en esta categoría <!-- mensaje de salida 2 (3.6)-->
                </div>
            @else
                @foreach ($recetas as $r)
                    <div class="card shadow-sm mb-3 border-0">
                        <div class="card-body">
                            <p class="mb-1"><strong class="fw-bold">Nombre:</strong> {{ $r->nombre_receta }}</p>
                            <p class="mb-1"><strong class="fw-bold">Descripción corta:</strong> {{ $r->descripcion_corta }}</p>
                            <p class="mb-1"><strong class="fw-bold">Promedio:</strong> {{ number_format($r->promedio_calificacion, 1) }}</p>
                            <p class="mb-1"><strong class="fw-bold">Cantidad:</strong> {{ $r->cantidad_calificaciones }}</p>
                            <p class="mb-1"><strong class="fw-bold">Link:</strong> <a href="{{ $r->link_receta }}" class="text-decoration-none fw-bold" style="color: #e88d51;">{{ $r->nombre_receta }}</a></p>
                            <p class="mb-0"><strong class="fw-bold">Régimen alimenticio:</strong>
                                @foreach ($r->regimenes as $reg)
                                    {{ $reg->regimen_alimenticio }}@if (!$loop->last), @endif <!-- $loop->last evita dejar una coma sobrante al final.-->
                                @endforeach
                            </p>
                            @if($r->imagen_referencial)
                            <p class="text-center">
                                <img src="{{asset('storage/' . $r->imagen_referencial)}}" alt="Imagen de la receta" class="img-fluid rounded shadow-sm" width="300">
                            </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        @endif

        <div class="mt-4">
            <a href="/" class="btn btn-outline-secondary fw-bold">Volver al inicio</a>
        </div>
    </main>
</body>
</html>