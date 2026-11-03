<!DOCTYPE html>
{{-- Vista para la función 4 Ajuste de Ingredientes segun cantidad de personas --}}
{{-- Responsable: Edinson Ramos --}}
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
            <h1 class="h3 mb-2 fw-bold">{{ $receta->nombre_receta }}</h1> {{-- encabezado con el nombre de la receta --}}
            @if (!$sin_recetas) <!-- si no hay ninguna receta registrada no mostramos el resto de la interfaz -->

            <!-- selector de receta por id-->
            <div class="mb-4">
            <label for="id_receta" class="form-label fw-bold"> Seleccionar receta por ID: </label>
            <form action="{{ url('/ajuste-ingredientes') }}" method="GET" class="d-flex align-items-center gap-2" style="max-width: 400px;">

            <!-- boton de receta anterior -->
            <a
                href="{{ $id_anterior !== null ? url('/ajuste-ingredientes?id_receta='.$id_anterior) : '#' }}"
                class="btn text-white fw-bold {{ $id_anterior === null ? 'disabled' : '' }}"
                style="background-color: {{ $id_anterior === null ? '#adb5bd' : '#ffab76' }}; border-color: {{ $id_anterior === null ? '#adb5bd' : '#ffab76' }};"
                >
                &lt;
            </a>

            <!-- campo para ingresar id manualmente -->
            <input
                type="number"
                id="id_receta"
                name="id_receta"
                min="1"
                max="99999999999"
                value="{{ $receta->id_receta }}"
                class="form-control fw-bold text-center"
                required
            >

            <!-- Boton de receta siguiente -->
            <a
                href="{{ $id_siguiente !== null ? url('/ajuste-ingredientes?id_receta='.$id_siguiente) : '#' }}"
                class="btn text-white fw-bold {{ $id_siguiente === null ? 'disabled' : '' }}"
                style="background-color: {{ $id_siguiente === null ? '#adb5bd' : '#ffab76' }}; border-color: {{ $id_siguiente === null ? '#adb5bd' : '#ffab76' }};"
                >
                &gt;
            </a>

            <!-- boton de busqueda -->
            <button type="submit" class="btn text-white fw-bold" style="background-color: #ffab76; border-color: #ffab76;" > Buscar </button>
            
        </form>
    </div>
            <p class="text-muted fw-semibold">{{ $receta->descripcion_corta }}</p> {{-- párrafo --}}
            <hr class="my-4"> {{-- linea separadora --}}

            <h2 class="h5 mb-3 fw-bold">Ajustar cantidad de personas</h2>

            <p class="mb-2 fw-bold"> Personas originales: <strong class="badge" style="background-color: #ffab76; color: #5c3a21; font-size: 1rem;">{{ $receta->cant_personas }}</strong></p> {{-- strong pone el texto en negrita --}}

            <div class="mb-4" style="max-width: 300px;">
                <label for="personas" class="form-label fw-bold">  Cantidad de personas deseada: </label>

                {{-- R4.1 CANT_PERSONAS_DESEADA es un dato de tipo número entero positivo --}}
                {{-- R4.2 ID_RECETA y CANT_PERSONAS_DESEADA son obligatorios para  ejecutar el ajuste --}}
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
                            >{{ $ingrediente->cantidad_ingrediente }} <!-- mostramos la cantidad -->
                            </strong>
                            {{ $ingrediente->unidad_medida }}
                        </span>
                    </li>
                @empty
                    <li class="list-group-item px-0 text-muted fw-bold">Esta receta no tiene ingredientes registrados.</li> <!-- receta con 0 ingredientes -->
                @endforelse
            </ul>

            <hr class="my-4">

            <h2 class="h5 mb-3 fw-bold">Pasos de preparación</h2>

            <div class="vstack gap-2">
                @forelse ($pasos as $paso)
                    <div class="p-2 bg-light rounded">
                        <span class="fw-bold" style="color: #e88d51;"> {{ $paso->paso_numero }}.</span> <span class="fw-semibold">{{ $paso->descripcion_paso }}</span> <!-- mostramos cada paso -->
                    </div>
                @empty
                    <p class="text-muted fw-bold">Esta receta no tiene pasos registrados.</p> <!-- receta con 0 pasos -->
                @endforelse
            </div>
            @endif
            <hr class="my-4">
            <a href="/" class="btn btn-outline-secondary fw-bold">Volver al inicio</a> <!-- boton para volver hacia atras -->
        </div>
    </main>

    @if (!$sin_recetas) <!-- si no hay ninguna receta registrada no es necesario hacer el calculo -->
    <script> //ejecutamos este script cada vez que se llama el evento input

        const personasInput = document.getElementById('personas'); //buscamos el elemento con id persona y lo guardamos en personasInput (en forma de texto)
        const personasOriginales = @json((int) $receta->cant_personas); //obtenemos la cantidad de personas originales y lo almacenamos en personasOriginales

        const cantidades = document.querySelectorAll('.cantidad_ajustada'); //buscamos los elementos que tienen la clase cantidad-ajustada


        function ajustarIngredientes() { //ejecutamos los calculos
            const personasDeseadas = Number(personasInput.value); //obtenemos el valor dentro del elemento personasInput y lo guardamos en la variable personasDeseadas

            if (personasDeseadas >= 1 && personasDeseadas <= 50 && personasOriginales > 0){ //nos aseguramos que se cumplan las condiciones adecuadas para personasDeseadas
                
                // R4.5 El sistema calcula el FACTOR_AJUSTE dividiendo CANT_PERSONAS_DESEADA por CANT_PERSONAS original
                const factor_ajuste = personasDeseadas / personasOriginales; //calculamos el factor_ajuste

                cantidades.forEach(function (elemento) { //recorremos cada ingrediente y obteneoms su cantidad original para multiplicarla por el factor ajuste
                    const cantidadOriginal = Number(elemento.dataset.cantidad);
                    const cantidadNueva = cantidadOriginal * factor_ajuste;
                    // R4.6 El sistema calcula la CANTIDAD_AJUSTADA multiplicando la CANTIDAD_INGREDIENTE original por el FACTOR_AJUSTE. 
                    // El resultado se redondea a un maximo de 2 decimales

                    // R4.7 Si CANT_PERSONAS_DESEADA es igual a la CANT_PERSONAS  original, el FACTOR_AJUSTE es 1 y el sistema muestra las cantidades  originales sin cambios 
                    // junto con su respectivo NOMBRE_INGREDIENTE y UNIDAD_MEDIDA para cada ingrediente.

                    //R4.8: El ajuste es solo visual y temporal. No modifica los valores de  CANT_PERSONAS ni de CANTIDAD_INGREDIENTE guardados en la receta  original

                    elemento.textContent = Number(cantidadNueva.toFixed(2)).toLocaleString('es-CL', {maximumFractionDigits: 2}); //reemplazamos la cantidad con la nueva cantidad redondeada
                    //es-CL es la convencion chilena para mostrar los decimales con coma en vez de punto
                });
            } else {
                return;
            }
        }

        personasInput.addEventListener('input', ajustarIngredientes); //detectamos cuando ocurra el evento input y ejecutamos el script ajustarIngredientes
    </script>
    @endif
</body>
</html>