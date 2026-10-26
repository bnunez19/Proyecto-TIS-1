<h1>Recetas</h1>

<a href="/">Volver al inicio </a>

<ul>
    @foreach ($recetas as $r)
        <li>
            <a href="/mostrar/{{ $r->id_receta }}">{{ $r->nombre_receta }}</a>
        </li>
    @endforeach
</ul>
