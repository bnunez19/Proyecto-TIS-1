<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buscar recetas</title>
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
        <h1 class="h3 mb-4 fw-bold">Buscar recetas</h1>
        
        {{-- busca y muestra los errores --}}
        @if ($errors->any())
            <div class="alert alert-danger fw-bold" role="alert">
                @foreach ($errors->all() as $error)
                <div>{{$error}}</div>
                @endforeach
            </div>
        @endif

        {{-- poner nombre y seleccionar categoria y regimen --}}
        <form method="GET" action="{{route('buscar')}}" class="card shadow-sm mb-4 border-0">
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="nombre" class="form-label fw-bold">Nombre de la receta</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" value="{{request('nombre')}}">
                    </div>

                    <div class="col-md-6">
                        <label for="categoria" class="form-label fw-bold">Categoría</label>
                        <select id="categoria" name="categoria" class="form-select">
                            <option value="">Todas</option>
                            @foreach ($categorias as $cat)
                                <option value="{{$cat}}" @selected(request('categoria') === $cat)>{{$cat}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label d-block fw-bold">Régimen alimenticio</label>
                        @foreach ($regimenes as $reg)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="regimen[]"
                                       id="regimen{{$loop->index}}" value="{{$reg}}"
                                       @checked(in_array($reg, (array) request('regimen', [])))>
                                <label class="form-check-label fw-semibold" for="regimen{{$loop->index}}">{{$reg}}</label>
                            </div>
                        @endforeach
                    </div>
                        
                    <div class="col-12">
                        <button type="submit" class="btn btn-pastel-naranja">Buscar</button>
                        <a href="{{route('buscar')}}" class="btn btn-outline-secondary fw-bold">Limpiar</a>
                    </div>

                </div>
            </div>
        </form>

        {{-- los resultados--}}
        @if(!is_null($resultados))
            <h2 class="h5 mb-3 fw-bold">Resultado de la búsqueda</h2>

            @if ($resultados->isEmpty())
                <div class="alert alert-danger fw-bold" role="alert">
                    No se encontraron recetas que coincidan con los criterios ingresados
                </div>
            @else
                @foreach ($resultados as $r)
                    <div class="card shadow-sm mb-3 border-0">
                        <div class="card-body">
                            <h3 class="h5 card-title fw-bold">{{$r->nombre_receta}}</h3>
                                <img src="{{ asset('storage/' . $r->imagen_referencial)}}"
                                class="img-fluid rounded" style="max-width: 300px;">
                            <p class="card-text">{{$r->descripcion_corta}}</p>

                            <p class="mb-1">
                                <span class="fw-bold">Categoría:</span> {{$r->categoria}}
                            </p>

                            <p class="mb-2">
                                <span class="fw-bold">Régimen alimenticio:</span>
                                {{$r->regimenes->pluck('regimen_alimenticio')->implode(', ')}}
                            </p>

                            <p class="mb-0">
                                <span class="fw-bold">Link:</span> <a href="/mostrar/{{ $r->id_receta }}" class="text-decoration-none fw-bold" style="color: #e88d51;">{{ $r->nombre_receta }}</a>
                            </p>
                        </div>
                    </div>
                @endforeach
            @endif
        @endif

    </main>
</body>
</html>
