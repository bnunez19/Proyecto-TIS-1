<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buscar recetas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <main class="container py-4" style="max-width: 900px;">
        <h1 class="h3 mb-4">Buscar recetas</h1>

        {{-- busca y muestra los errores --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach ($errors->all() as $error)
                <div>{{$error}}</div>
                @endforeach
            </div>
        @endif

        {{-- poner nombre y seleccionar categoria y regimen --}}
        <form method="GET" action="{{route('buscar')}}" class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre de la receta</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" value="{{request('nombre')}}">
                    </div>

                    <div class="col-md-6">
                        <label for="categoria" class="form-label">Categoría</label>
                        <select id="categoria" name="categoria" class="form-select">
                            <option value="">Todas</option>
                            @foreach ($categorias as $cat)
                                <option value="{{$cat}}" @selected(request('categoria') === $cat)>{{$cat}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label d-block">Régimen alimenticio</label>
                        @foreach ($regimenes as $reg)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="regimen[]"
                                       id="regimen{{$loop->index}}" value="{{$reg}}"
                                       @checked(in_array($reg, (array) request('regimen', [])))>
                                <label class="form-check-label" for="regimen{{$loop->index}}">{{$reg}}</label>
                            </div>
                        @endforeach
                    </div>
                        
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Buscar</button>
                        <a href="{{route('buscar')}}" class="btn btn-outline-secondary">Limpiar</a>
                    </div>

                </div>
            </div>
        </form>

        {{-- los resultados--}}
        @if(!is_null($resultados))
            <h2 class="h5 mb-3">Resultado de la búsqueda</h2>

            @if ($resultados->isEmpty())
                <div class="alert alert-danger" role="alert">
                    No se encontraron recetas que coincidan con los criterios ingresados
                </div>
            @else
                @foreach ($resultados as $r)
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h3 class="h5 card-title">{{$r->nombre_receta}}</h3>
                            <p class="card-text">{{$r->descripcion_corta}}</p>

                            <p class="mb-1">
                                <span class="fw-semibold">Categoría:</span> {{$r->categoria}}
                            </p>

                            <p class="mb-2">
                                <span class="fw-semibold">Régimen alimenticio:</span>
                                {{$r->regimenes->pluck('regimen_alimenticio')->implode(', ')}}
                            </p>

                            <p class="mb-0">
                                <span class="fw-semibold">Link:</span>
                                <a href="{{$r->link_receta}}">{{$r->link_receta}}</a>
                            </p>
                        </div>
                    </div>
                @endforeach
            @endif
        @endif

    </main>
</body>
</html>
