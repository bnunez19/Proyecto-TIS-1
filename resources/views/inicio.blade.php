<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menú Principal - Recetas</title>
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
        .btn-outline-pastel-naranja {
            border-color: #ffab76;
            color: #b35922;
            font-weight: 700;
        }
        .btn-outline-pastel-naranja:hover {
            background-color: #ffab76;
            border-color: #ffab76;
            color: #fff;
        }
    </style>
</head>
<body class="bg-light">

    <main class="container py-5" style="max-width: 800px;">
        <div class="text-center mb-5">
            {{-- Encabezado principal --}}
            <h1 class="h2 fw-bold text-dark">¿Qué quieres hacer?</h1>
            <p class="text-muted fw-bold">Selecciona una opción para gestionar tus recetas</p>
        </div>

        <div class="row g-3">
            {{-- Opción Ingresar receta --}}
            <div class="col-md-6">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex flex-column justify-content-between text-center py-4">
                        <h5 class="card-title h6 fw-bold text-secondary">Ingresar nueva receta</h5>
                        <form action="/ingreso" method="GET" class="mt-3">
                            <button type="submit" class="btn btn-pastel-naranja w-100">Ingresar nueva receta</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Opción Buscar recetas --}}
            <div class="col-md-6">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex flex-column justify-content-between text-center py-4">
                        <h5 class="card-title h6 fw-bold text-secondary">Buscar recetas</h5>
                        <form action="/buscar" method="GET" class="mt-3">
                            <button type="submit" class="btn btn-outline-pastel-naranja w-100">Buscar recetas</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Opción Mostrar todas las recetas --}}
            <div class="col-md-6">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex flex-column justify-content-between text-center py-4">
                        <h5 class="card-title h6 fw-bold text-secondary">Mostrar todas las recetas</h5>
                        <form action="/lista" method="GET" class="mt-3">
                            <button type="submit" class="btn btn-outline-pastel-naranja w-100">Mostar todas las recetas</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Opción Top 10 recetas --}}
            <div class="col-md-6">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex flex-column justify-content-between text-center py-4">
                        <h5 class="card-title h6 fw-bold text-secondary">Top 10 recetas más populares</h5>
                        <form action="/top10" method="GET" class="mt-3">
                            <button type="submit" class="btn btn-outline-pastel-naranja w-100">Top 10 recetas mas populares por categoria</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex flex-column justify-content-between text-center py-4">
                        <h5 class="card-title h6 fw-bold text-secondary">Valorar receta</h5>
                        <form action="/valorar" method="GET" class="mt-3">
                            <button type="submit" class="btn btn-outline-pastel-naranja w-100">Calificar Receta</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
