<!DOCTYPE html>
<html lang="es">  {{-- lenguaje en español --}}

<head>
    <meta charset="UTF-8"> {{-- permite mostrar carácteres como tildes por ejemplo --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> {{-- permite que la pagina se adapte a pantallas pequeñas--}}
    <title>Ajustar ingredientes</title> {{-- nombre de la pestaña --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <main class="container py-4" style="max-width: 800px;">
        <div class="card shadow-sm p-4 mb-4 border-0">
            <h1 class="h3 mb-2 fw-bold">{{ $receta->nombre_receta }}</h1> {{-- encabezado --}}
            <p class="text-muted fw-semibold">{{ $receta->descripcion_corta }}</p> {{-- párrafo --}}

            <hr class="my-4"> {{-- linea separadora --}}

            <h2 class="h5 mb-3 fw-bold">Ajustar cantidad de personas</h2>

            <p class="mb-2 fw-bold"> Personas originales: <strong class="badge" style="background-color: #ffab76; color: #5c3a21; font-size: 1rem;">{{ $receta->cant_personas }}</strong></p> {{-- strong pone el texto en negrita --}}

            <div class="mb-4" style="max-width: 300px;">
                <label for="personas" class="form-label fw-bold">  Cantidad de personas deseada: </label>
                <input
                    type="number"
                    id="personas"
                    min="1"
                    max="50"
                    step="1"
                    value="{{ max(1, (int) $receta->cant_personas) }}" {{-- establece que el valor inicial sea el mismo que el de la receta o minimo 1 --}}
                    class="form-control fw-bold"
                >
            </div>

            <hr class="my-4">

            <h2 class="h5 mb-3 fw-bold">Ingredientes</h2>

            <ul class="list-group list-group-flush mb-4">
                @forelse ($ingredientes as $ingrediente) {{-- aqui mostramos todos los ingredientes de la receta --}}
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center fw-semibold">
                        <span>{{ $ingrediente->nombre_ingrediente }}:</span>
                        <span>
                            <strong
                                class="cantidad_ajustada fw-bold" {{-- hacemos una clase para que sea usada luego --}}
                                style="color: #e88d51;"
                                data-cantidad="{{ $ingrediente->cantidad_ingrediente }}" {{-- guardamos la cantidad original de cada ingrediente --}}
                            >{{ $ingrediente->cantidad_ingrediente }} {{-- mostramos la cantidad --}}
                            </strong>
                            {{ $ingrediente->unidad_medida }}
                        </span>
                    </li>
                @empty
                    <li class="list-group-item px-0 text-muted fw-bold">Esta receta no tiene ingredientes registrados.</li> {{-- receta con 0 ingredientes --}}
                @endforelse
            </ul>

            <hr class="my-4">

            <h2 class="h5 mb-3 fw-bold">Pasos de preparación</h2>

            <div class="vstack gap-2">
                @forelse ($pasos as $paso)
                    <div class="p-2 bg-light rounded">
                        <span class="fw-bold" style="color: #e88d51;"> {{ $paso->paso_numero }}.</span> <span class="fw-semibold">{{ $paso->descripcion_paso }}</span> {{-- mostramos cada paso --}}
                    </div>
                @empty
                    <p class="text-muted fw-bold">Esta receta no tiene pasos registrados.</p> {{-- receta con 0 pasos --}}
                @endforelse
            </div>
        </div>
    </main>

    <script> //ejecutamos este scrip cada vez que se llama el evento input

        const personasInput = document.getElementById('personas'); //buscamos el elemento con id persona y lo guardamos en personasInput (en forma de texto)
        const personasOriginales = @json((int) $receta->cant_personas); //obtenemos la cantidad de personas originales y lo almacenamos en personasOriginales

        const cantidades = document.querySelectorAll('.cantidad_ajustada'); //buscamos los elementos que tienen la clase cantidad-ajustada


        function ajustarIngredientes() { //ejecutamos los calculos
            const personasDeseadas = Number(personasInput.value); //obtenemos el valor dentro del elemento personasInput y lo guardamos en la variable personasDeseadas

            if (personasDeseadas >= 1 && personasDeseadas <= 50 && personasOriginales > 0){ //nos aseguramos que se cumplan las condiciones adecuadas para personasDeseadas
                
                const factor_ajuste = personasDeseadas / personasOriginales; //calculamos el factor_ajuste

                cantidades.forEach(function (elemento) { //recorremos cada ingrediente y obteneoms su cantidad original para multiplicarla por el factor ajuste
                    const cantidadOriginal = Number(elemento.dataset.cantidad);
                    const cantidadNueva = cantidadOriginal * factor_ajuste;

                    elemento.textContent = Number(cantidadNueva.toFixed(2)).toLocaleString('es-CL', {maximumFractionDigits: 2}); //reemplazamos la cantidad con la nueva cantidad
                    //ademas, se limita a solo 2 decimales segun la especificación, y es-CL es la convencion chilena para mostrar los decimales con coma en vez de punto
                });
            } else {
                return;
            }
        }

        personasInput.addEventListener('input', ajustarIngredientes); //detectamos cuando ocurra el evento input y ejecutamos el script ajustarIngredientes
    </script>

</body>
</html>