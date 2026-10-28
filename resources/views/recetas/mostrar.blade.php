<h1>{{ $recetas->nombre_receta }}</h1>

@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li style="color: red;">{{ $error }}</li>
        @endforeach
    </ul>
@endif
<p><img src="{{ asset('storage/' . $recetas->imagen_referencial) }}" alt="Imagen de la receta" width="300"></p>

<h3>Detalle de la receta</h3>
<p>Descripción: {{ $recetas->descripcion_corta }}</p>
<p>Promedio: {{ $recetas->promedio_calificacion }}</p>
<p>Cantidad de calificaciones: {{ $recetas->cantidad_calificaciones }}</p>
<p>Link: <a href="{{ route('recetas.mostrar', $recetas->id_receta) }}">{{ $recetas->nombre_receta }}</a></p>
<h3>Regímenes</h3>
<ul>
    @forelse ($recetas->regimenes as $reg)
        <li>{{ $reg->regimen_alimenticio }}</li>
    @empty
        <li>Esta receta no tiene regímenes asignados.</li>
    @endforelse
</ul>


@if (request('origen') === 'valorar')
    <a href="{{ route('valorar', [
        'id_usuario' => request('id_usuario'),
        'id_receta' => $recetas->id_receta,
    ]) }}">
        Volver a valorar receta
    </a>
@else
    <a href="{{ route('recetas.lista') }}">Volver a la lista</a>
@endif