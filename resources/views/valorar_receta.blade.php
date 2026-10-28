<h1>Valorar receta</h1>

<!-- muestra el mensaje despues de guardar una calificacion correctamente -->
@if (session('exito'))
    <p style="color: green;">{{ session('exito') }}</p>
@endif

<!-- muestra los mensajes si falta un dato o la receta y el usuario no existen -->
@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li style="color: red;">{{ $error }}</li>
        @endforeach
    </ul>
@endif

@if ($errorReceta)
    <p style="color: red;">{{ $errorReceta }}</p>
@endif

<!-- permite elegir la receta y el usuario antes de guardar una calificacion -->
<form action="{{ route('valorar') }}" method="GET">
    <p>
        <label for="id_usuario">Usuario:</label>
        <select name="id_usuario" id="id_usuario" onchange="this.form.submit()">
            <option value="">-- Seleccione un usuario --</option>
            @foreach ($usuarios as $usuario)
                <option value="{{ $usuario->id_usuario }}" @selected($idUsuario === $usuario->id_usuario)>{{ $usuario->id_usuario }}</option>
            @endforeach
        </select>
    </p>

    <p>
        <label for="id_receta">Receta:</label>
        <select name="id_receta" id="id_receta" onchange="this.form.submit()">
            <option value="">-- Seleccione una receta --</option>
            @foreach ($recetas as $recetaLista)
                <option value="{{ $recetaLista->id_receta }}" @selected((string) $idReceta === (string) $recetaLista->id_receta)>{{ $recetaLista->nombre_receta }}</option>
            @endforeach
        </select>
    </p>

    <noscript><button type="submit">Ver receta</button></noscript>
</form>

<!-- muestra el detalle y el formulario solo cuando se eligio una receta valida -->
@if ($receta)
    <hr>
    <h2>{{ $receta->nombre_receta }}</h2>
    <p>Descripción: {{ $receta->descripcion_corta }}</p>
    <p>Categoría: {{ $receta->categoria }}</p>
    <p>Cantidad de personas: {{ $receta->cant_personas }}</p>
    <p>Régimen alimenticio: {{ $receta->regimenes->pluck('regimen_alimenticio')->implode(', ') }}</p>
    <p>Promedio de calificación: {{ $receta->cantidad_calificaciones > 0 ? number_format($receta->promedio_calificacion, 1) : 'Sin calificaciones' }}</p>
    <p>Cantidad de calificaciones: {{ $receta->cantidad_calificaciones }}</p>
    <p>
        Link:
        <a href="{{ route('recetas.mostrar', [
        'id_receta' => $receta->id_receta,
        'origen' => 'valorar',
        'id_usuario' => $idUsuario,
    ]) }}">
        Ver detalle completo
    </a>
</p>

    <hr>
    <h3>Calificar esta receta</h3>

    @if ($idUsuario === '')
        <p>Seleccione un usuario arriba para poder calificar.</p>
    @else
        <!-- indica si la nueva nota creara una calificacion o reemplazara la existente -->
        @if ($calificacionPrevia !== null)
            <p>El usuario {{ $idUsuario }} ya calificó esta receta con un {{ $calificacionPrevia }}. Si envía una nueva nota, se reemplazará.</p>
        @else
            <p>El usuario {{ $idUsuario }} aún no ha calificado esta receta.</p>
        @endif

        <!-- envia los datos de la calificacion al controlador -->
        <form action="{{ route('valorar.guardar') }}" method="POST">
            @csrf
            <input type="hidden" name="id_usuario" value="{{ $idUsuario }}">
            <input type="hidden" name="id_receta" value="{{ $receta->id_receta }}">

            <label for="calificacion">Calificación:</label>
            <select name="calificacion" id="calificacion" required>
                @foreach ([1 => 'No me gusta para nada', 2 => 'No me gusta', 3 => 'Indiferente', 4 => 'Me gusta', 5 => 'Me gusta mucho'] as $valor => $texto)
                    <option value="{{ $valor }}" @selected((int) $calificacionPrevia === $valor)>{{ $valor }} - {{ $texto }}</option>
                @endforeach
            </select>
            <button type="submit">Guardar calificación</button>
        </form>
    @endif
@endif

<hr>
<a href="/">Volver al inicio</a>
