<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Valorar Receta</title>
</head>
<body>
    <h1>Valorar Receta</h1>
    @if (session('exito'))
        <p style="color: green;"><strong>{{ session('exito') }}</strong></p>
    @endif
    @if ($receta)
        <h2>{{ $receta->NOMBRE_RECETA }}</h2>
        <p>{{ $receta->DESCRIPCION_CORTA }}</p>
        <p>
            <strong>Promedio:</strong> {{ $receta->PROMEDIO_CALIFICACION ?? 'Sin notas' }} 
            ({{ $receta->CANTIDAD_CALIFICACIONES ?? 0 }} valoraciones)
        </p>
    @endif
    <hr>
    <form method="POST" action="{{ route('valorar.guardar') }}">
        @csrf
        <input type="hidden" name="ID_RECETA" value="{{ $receta->ID_RECETA ?? 1 }}">
        <input type="hidden" name="ID_USUARIO" value="{{ $id_usuario }}">
        <p><strong>Selecciona tu nota (1 a 5):</strong></p>
        @foreach ([5 => '5 - Me gusta mucho', 4 => '4 - Me gusta', 3 => '3 - Indiferente', 2 => '2 - No me gusta', 1 => '1 - No me gusta para nada'] as $valor => $etiqueta)
            <p>
                <label>
                    <input 
                        type="radio" 
                        name="CALIFICACION" 
                        value="{{ $valor }}"
                        @checked($calificacionPrevia == $valor)
                        required
                    >
                    {{ $etiqueta }}
                </label>
            </p>
        @endforeach
        <button type="submit">Calificar</button>
    </form>
</body>
</html>