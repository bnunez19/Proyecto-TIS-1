<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Valorar receta</title>
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
    <h1 class="h3 mb-4 fw-bold">Valorar receta</h1>

    {{-- R2.11 / Salida 2: muestra "Calificación registrada exitosamente" (mensaje 'exito' enviado por el controlador) --}}
    <!-- muestra el mensaje despues de guardar una calificacion correctamente -->
    @if (session('exito'))
        <div class="alert alert-success fw-bold" role="alert">{{ session('exito') }}</div>
    @endif

    {{-- Salida 2: muestra los errores de guardar():
         R2.5 (campos obligatorios), R2.2 (rango 1 a 5),
         R2.6 ("Usuario no registrado en el sistema") y R2.7 ("La receta consultada no existe") --}}
    <!-- muestra los mensajes si falta un dato o la receta y el usuario no existen -->
    @if ($errors->any())
        <div class="alert alert-danger fw-bold" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- R2.7: error de receta inexistente generado en index() (mensaje "La receta consultada no existe") --}}
    @if ($errorReceta)
        <div class="alert alert-danger fw-bold" role="alert">{{ $errorReceta }}</div>
    @endif

    {{-- R2.6: el <select> de usuarios solo ofrece usuarios registrados en la base de datos (ID_USUARIO)
         R2.7: el <select> de recetas solo ofrece recetas existentes (ID_RECETA)
         R2.5: ambos datos son necesarios para poder calificar --}}
    <!-- permite elegir la receta y el usuario antes de guardar una calificacion -->
    <form action="{{ route('valorar') }}" method="GET" class="card shadow-sm p-4 mb-4 border-0">
        <div class="mb-3">
            <label for="id_usuario" class="form-label fw-bold">Usuario:</label>
            <select name="id_usuario" id="id_usuario" class="form-select" onchange="this.form.submit()">
                <option value="">-- Seleccione un usuario --</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id_usuario }}" @selected($idUsuario === $usuario->id_usuario)>{{ $usuario->id_usuario }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-0">
            <label for="id_receta" class="form-label fw-bold">Receta:</label>
            <select name="id_receta" id="id_receta" class="form-select" onchange="this.form.submit()">
                <option value="">-- Seleccione una receta --</option>
                @foreach ($recetas as $recetaLista)
                    <option value="{{ $recetaLista->id_receta }}" @selected((string) $idReceta === (string) $recetaLista->id_receta)>{{ $recetaLista->nombre_receta }}</option>
                @endforeach
            </select>
        </div>

        <noscript><button type="submit" class="btn btn-pastel-naranja mt-3">Ver receta</button></noscript>
    </form>

    {{-- R2.7: el detalle y el formulario solo aparecen si la receta existe ($receta no es null) --}}
    <!-- muestra el detalle y el formulario solo cuando se eligio una receta valida -->
    @if ($receta)
        <div class="card shadow-sm p-4 mb-4 border-0">
            <h2 class="h4 mb-3 fw-bold">{{ $receta->nombre_receta }}</h2>
            <p class="mb-1"><span class="fw-bold">Descripción:</span> {{ $receta->descripcion_corta }}</p>
            <p class="mb-1"><span class="fw-bold">Categoría:</span> {{ $receta->categoria }}</p>
            <p class="mb-1"><span class="fw-bold">Cantidad de personas:</span> {{ $receta->cant_personas }}</p>
            <p class="mb-1"><span class="fw-bold">Régimen alimenticio:</span> {{ $receta->regimenes->pluck('regimen_alimenticio')->implode(', ') }}</p>
            {{-- R2.3 y Salida 1: PROMEDIO_CALIFICACION con 1 decimal (number_format);
                 R2.4: si CANTIDAD_CALIFICACIONES es 0 muestra "Sin calificaciones" --}}
            <p class="mb-1"><span class="fw-bold">Promedio de calificación:</span> {{ $receta->cantidad_calificaciones > 0 ? number_format($receta->promedio_calificacion, 1) : 'Sin calificaciones' }}</p>
            {{-- R2.4 y Salida 1: CANTIDAD_CALIFICACIONES --}}
            <p class="mb-1"><span class="fw-bold">Cantidad de calificaciones:</span> {{ $receta->cantidad_calificaciones }}</p>
            <p class="mb-0">
                <p class="mb-0">
                <span class="fw-bold">Link:</span>
                <a href="{{ parse_url($receta->link_receta, PHP_URL_PATH) }}" class="text-decoration-none fw-bold" style="color: #e88d51;">Ver detalle completo</a>
                </p>
            </p>

            <hr class="my-4">
            <h3 class="h5 mb-3 fw-bold">Calificar esta receta</h3>

            {{-- R2.5 / R2.6: sin ID_USUARIO seleccionado no se muestra el formulario y no se permite calificar --}}
            @if ($idUsuario === '')
                <div class="alert alert-warning mb-0">Seleccione un usuario arriba para poder calificar.</div>
            @else
                {{-- R2.8: informa si la nueva nota reemplazara una calificacion existente
                     del par ID_USUARIO + ID_RECETA o creara una nueva --}}
                <!-- indica si la nueva nota creara una calificacion o reemplazara la existente -->
                @if ($calificacionPrevia !== null)
                    <div class="alert alert-info">El usuario {{ $idUsuario }} ya calificó esta receta con un {{ $calificacionPrevia }}. Si envía una nueva nota, se reemplazará.</div>
                @else
                    <div class="alert alert-secondary">El usuario {{ $idUsuario }} aún no ha calificado esta receta.</div>
                @endif

                {{-- envia ID_RECETA, ID_USUARIO y CALIFICACION (datos de entrada, R2.5) por POST a guardar() --}}
                <!-- envia los datos de la calificacion al controlador -->
                <form action="{{ route('valorar.guardar') }}" method="POST">
                    @csrf
                    {{-- R2.5: ID_USUARIO e ID_RECETA viajan en campos ocultos --}}
                    <input type="hidden" name="id_usuario" value="{{ $idUsuario }}">
                    <input type="hidden" name="id_receta" value="{{ $receta->id_receta }}">

                    <div class="mb-3">
                        <label for="calificacion" class="form-label fw-bold">Calificación:</label>
                        {{-- R2.5: CALIFICACION es obligatoria (required)
                             R2.2: solo ofrece los enteros 1 a 5
                             R2.8: preselecciona la nota previa del usuario si existe --}}
                        <select name="calificacion" id="calificacion" class="form-select" required>
                            @foreach ([1 => 'No me gusta para nada', 2 => 'No me gusta', 3 => 'Indiferente', 4 => 'Me gusta', 5 => 'Me gusta mucho'] as $valor => $texto)
                                <option value="{{ $valor }}" @selected((int) $calificacionPrevia === $valor)>{{ $valor }} - {{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-pastel-naranja w-100">Guardar calificación</button>
                </form>
            @endif
        </div>
    @endif

    <a href="/" class="btn btn-outline-secondary fw-bold">Volver al inicio</a>
</main>
</body>
</html>