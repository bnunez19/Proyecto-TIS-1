<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recetas</title>
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
        <h1 class="h3 mb-4 fw-bold">Recetas</h1>

        <div class="card shadow-sm p-4 mb-4 border-0">
            {{-- lista de recetas, cada nombre lleva al detalle (/mostrar/id) --}}
            <ul class="list-group list-group-flush mb-3">
                @foreach ($recetas as $r)
                    <li class="list-group-item px-0">
                        <a href="/mostrar/{{ $r->id_receta }}" class="text-decoration-none fw-bold" style="color: #e88d51;">{{ $r->nombre_receta }}</a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-2">
                <a href="/" class="btn btn-outline-secondary fw-bold">Volver al inicio</a>
            </div>
        </div>
    </main>
</body>
</html>
