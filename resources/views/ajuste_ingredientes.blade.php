<!DOCTYPE html>
<html lang="es">  {{-- lenguaje en español --}}

<head>
    <meta charset="UTF-8"> {{-- permite mostrar carácteres como tildes por ejemplo --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> {{-- permite que la pagina se adapte a pantallas pequeñas--}}
    <title>Ajustar ingredientes</title> {{-- nombre de la pestaña --}}
</head>

<body>

    <h1>{{ $receta->nombre_receta }}</h1> {{-- encabezado --}}
    <p>{{ $receta->descripcion_corta }}</p> {{-- párrafo --}}

    <hr> {{-- linea separadora --}}

    <h2>Ajustar cantidad de personas</h2>

    <p> Personas originales: <strong>{{ $receta->cant_personas }}</strong></p> {{-- strong pone el texto en negrita --}}

    <label for="personas">  Cantidad de personas deseada: </label>
    <input
        type="number"
        id="personas"
        min="1"
        max="50"
        step="1"
        value="{{ max(1, (int) $receta->cant_personas) }}" {{-- establece que el valor inicial sea el mismo que el de la receta o minimo 1 --}}
    >

    <hr>

    <h2>Ingredientes</h2>

    @forelse ($ingredientes as $ingrediente) {{-- aqui mostramos todos los ingredientes de la receta --}}
        <p>{{ $ingrediente->nombre_ingrediente }}:
            <strong
                class="cantidad_ajustada" {{-- hacemos una clase para que sea usada luego --}}
                data-cantidad="{{ $ingrediente->cantidad_ingrediente }}" {{-- guardamos la cantidad original de cada ingrediente --}}
            >{{ $ingrediente->cantidad_ingrediente }} {{-- mostramos la cantidad --}}
            </strong>
            {{ $ingrediente->unidad_medida }}
        </p>
    @empty
        <p>Esta receta no tiene ingredientes registrados.</p> {{-- receta con 0 ingredientes --}}
    @endforelse

    <hr>

    <h2>Pasos de preparación</h2>

    @forelse ($pasos as $paso)
        <p> {{ $paso->paso_numero }}. {{ $paso->descripcion_paso }}</p> {{-- mostramos cada pasp --}}
    @empty
        <p>Esta receta no tiene pasos registrados.</p> {{-- receta con 0 pasos --}}
    @endforelse



    <script> //ejecutamos este scrip cada vez que se llama el evento input

        const personasInput = document.getElementById('personas'); //buscamos el elemento con id persona y lo guardamos en personasInput (en forma de texto)
        const personasOriginales = @json((int) $receta->cant_personas); //obtenemos la cantidad de personas originales y lo almacenamos en personasOriginales

        const cantidades = document.querySelectorAll('.cantidad_ajustada'); //buscamos los elementos que tienen la clase cantidad-ajustada


        function ajustarIngredientes() { //ejecutamos los calculos
            const personasDeseadas = Number(personasInput.value); //obtenemos el valor dentro del elemento personasInput y lo guardamos en la variable personasDeseadas

            if (personasDeseadas >= 1 || personasDeseadas < 51 || personasOriginales > 0){ //nos aseguramos que se cumplan las condiciones adecuadas para personasDeseadas
                
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

        personasInput.addEventListener('input',ajustarIngredientes); //detectamos cuando ocurra el evento input y ejecutamos el script ajustarIngredientes
    </script>

</body>
</html>
