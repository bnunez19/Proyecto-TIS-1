<h1>Buscar recetas mas populares por categoria</h1>

<form action="/top10" method="GET">
    <select name="categoria">
        @foreach ($categorias as $cat)
            <option value="{{ $cat }}" @selected(request('categoria') === $cat)>{{ $cat }}</option>
        @endforeach
    </select>
    <button type="submit">Buscar</button>
</form>

@if ($receta !== null)
    <hr>
    <h2>Resultado de la búsqueda</h2>

    @forelse ($receta as $r)
    @if ($r->cantidad_calificaciones <=0 )
        <p>Aún no hay recetas calificadas en esta categoría.</p> <!-- $loop->mensaje de salida 2 (3.6)-->
    @else
        <p>Nombre: {{ $r->nombre_receta }}</p>
        <p>Descripcion corta: {{ $r->descripcion_corta }}</p>
        <p>Promedio: {{ $r->promedio_calificacion}}</p>
        <p>Cantidad: {{ $r->cantidad_calificaciones }}</p>
        <p>Link: <a href="/mostrar/{{ $r->id_receta }}">{{ $r->nombre_receta }}</a></p>
        <p>Regimen alimenticio: 

            @foreach ($r->regimenes as $reg)
                {{ $reg->regimen_alimenticio }}@if (!$loop->last), @endif <!-- $loop->last evita dejar una coma sobrante al final.-->
            @endforeach
        </p>
        <hr>
    @endif
    @empty
        <p>No se encontró ninguna receta en esa categoría.</p> <!-- $loop->mensaje de salida 3 (3.7)-->
    @endforelse
@endif

<a href="/">Volver al inicio</a>