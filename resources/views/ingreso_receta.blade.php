<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresar receta</title>
    {{-- R1.22: los campos con error se marcan en rojo --}}
    <style>
        .rojo { border: 2px solid red; }
    </style>
</head>
<body>
    <h1>Ingresar receta</h1>

    {{-- R1.22 y Salida 2: busca y muestra los mensajes de error (sin repetir los iguales) --}}
    @if ($errors->any())
        @foreach (collect($errors->all())->unique() as $error)
            <p style="color: red;">{{$error}}</p>
        @endforeach
    @endif

    {{-- formulario: los datos se envian a la ruta ingreso.guardar y asi de esa forma la receta se guarda
         enctype multipart: se usa para enviar la imagen como archivo (R1.7)
         novalidate: los mensajes de error los entrega el sistema y no el navegador (R1.22) --}}

    <form method="POST" action="{{route('ingreso.guardar')}}" enctype="multipart/form-data" novalidate>
        @csrf

        {{-- R1.2 y R1.15: nombre de la receta --}}
        <p>
            <label for="nombre_receta">Nombre de la receta:</label>
            <input type="text" id="nombre_receta" name="nombre_receta" value="{{old('nombre_receta')}}"
                   class="{{ $errors->has('nombre_receta') ? 'rojo' : '' }}">
        </p>

        {{-- R1.3 y R1.15: descripcion corta --}}
        <p>
            <label for="descripcion_corta">Descripción corta:</label>
            <input type="text" id="descripcion_corta" name="descripcion_corta" value="{{old('descripcion_corta')}}"
                   class="{{ $errors->has('descripcion_corta') ? 'rojo' : '' }}">
        </p>

        {{-- R1.7, R1.15 y R1.19: la imagen es un archivo adjunto JPG, JPEG o PNG
             (despues de un error hay que volver a elegirla, el navegador no la conserva) --}}
        <p>
            <label for="imagen_referencial">Imagen referencial (JPG o PNG):</label>
            <input type="file" id="imagen_referencial" name="imagen_referencial" accept=".jpg,.jpeg,.png"
                   class="{{ $errors->has('imagen_referencial') ? 'rojo' : '' }}">
        </p>

        {{-- R1.8 y R1.15: cantidad de personas --}}
        <p>
            <label for="cant_personas">Cantidad de personas:</label>
           <input type="number" id="cant_personas" name="cant_personas" min="1" max="50" step="1" value="{{old('cant_personas')}}"
                    class="{{ $errors->has('cant_personas') ? 'rojo' : '' }}">
        </p>

        {{-- R1.9 y R1.17: categoria, lista cerrada de una sola opcion --}}
        <p>
            <label for="categoria">Categoría:</label>
            <select id="categoria" name="categoria" class="{{ $errors->has('categoria') ? 'rojo' : '' }}">
                <option value="">Seleccione</option>
                @foreach ($categorias as $cat)
                    <option value="{{$cat}}" @selected(old('categoria') === $cat)>{{$cat}}</option>
                @endforeach
            </select>
        </p>

        {{-- R1.10 y R1.17: regimen alimenticio, lista cerrada de una o mas opciones --}}
        <p>
            Régimen alimenticio:<br>
            <span class="{{ $errors->has('regimen') ? 'rojo' : '' }}">
                @foreach ($regimenes as $reg)
                    <label>
                        <input type="checkbox" name="regimen[]" value="{{$reg}}"
                        @checked(in_array($reg, (array) old('regimen', [])))>
                        {{$reg}}
                    </label>
                @endforeach
            </span>
        </p>
        <hr>

        {{-- R1.16: de 1 a 50 ingredientes. Cada uno con nombre (R1.4), cantidad (R1.5) y unidad (R1.6) --}}
        <h3>Ingredientes</h3>
        <div id="ingredientes">
            @php $filasIng = old('ingredientes', [['nombre' => '', 'cantidad' => '', 'unidad' => '']]); @endphp
            @foreach ($filasIng as $i => $ing)
                <p>
                    <input type="text" name="ingredientes[{{$i}}][nombre]" placeholder="Ingrediente"
                           value="{{$ing['nombre'] ?? ''}}"
                           class="{{ $errors->has("ingredientes.$i.nombre") ? 'rojo' : '' }}">
                    <input type="number" min="0.01" max="10000" step="0.01" name="ingredientes[{{$i}}][cantidad]" placeholder="Cantidad"
                            value="{{$ing['cantidad'] ?? ''}}"
                            class="{{ $errors->has("ingredientes.$i.cantidad") ? 'rojo' : '' }}">
                    <select name="ingredientes[{{$i}}][unidad]" class="{{ $errors->has("ingredientes.$i.unidad") ? 'rojo' : '' }}">
                        <option value="">Unidad</option>
                        @foreach ($unidades as $u)
                            <option value="{{$u}}" @selected(($ing['unidad'] ?? '') === $u)>{{$u}}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="quitarFila(this, 'ingredientes')">Quitar</button>
                </p>
            @endforeach
        </div>
        <button type="button" onclick="agregarIngrediente()">Agregar ingrediente</button>
        <hr>

        {{-- R1.18: de 1 a 30 pasos. La lista numerada pone el numero sola
             (R1.12: guarda PASO_NUMERO correlativo desde 1) --}}
        <h3>Pasos de preparación</h3>
        <ol id="pasos">
            @php $filasPasos = old('pasos', ['']); @endphp
            @foreach ($filasPasos as $i => $paso)
                <li>
                    <textarea name="pasos[]" rows="2" cols="50"
                              class="{{ $errors->has("pasos.$i") ? 'rojo' : '' }}">{{$paso}}</textarea>
                    <button type="button" onclick="quitarFila(this, 'pasos')">Quitar</button>
                </li>
            @endforeach
        </ol>
        <button type="button" onclick="agregarPaso()">Agregar paso</button>
        <hr>

        <button type="submit">Guardar receta</button>
    </form>

    {{-- moldes de las filas nuevas (NUEVO se reemplaza por un numero distinto en cada fila) --}}
    <template id="molde-ingrediente">
        <p>
            <input type="text" name="ingredientes[NUEVO][nombre]" placeholder="Ingrediente">
            <input type="number" min="0.01" max="10000" step="0.01" name="ingredientes[NUEVO][cantidad]" placeholder="Cantidad">
            <select name="ingredientes[NUEVO][unidad]">
                <option value="">Unidad</option>
                @foreach ($unidades as $u)
                    <option value="{{$u}}">{{$u}}</option>
                @endforeach
            </select>
            <button type="button" onclick="quitarFila(this, 'ingredientes')">Quitar</button>
        </p>
    </template>

    <template id="molde-paso">
        <li>
            <textarea name="pasos[]" rows="2" cols="50"></textarea>
            <button type="button" onclick="quitarFila(this, 'pasos')">Quitar</button>
        </li>
    </template>

    <script>
        var contador = 1000; //numero distinto para cada fila nueva de ingredientes

        //R1.16: agrega una fila de ingrediente (maximo 50)
        function agregarIngrediente() {
            var lista = document.getElementById('ingredientes');
            if (lista.children.length >= 50) {
                alert('Una receta puede tener como maximo 50 ingredientes');
                return;
            }
            contador++;
            var fila = document.getElementById('molde-ingrediente').innerHTML.replace(/NUEVO/g, contador);
            lista.insertAdjacentHTML('beforeend', fila);
        }

        //R1.18: agrega un paso (maximo 30)
        function agregarPaso() {
            var lista = document.getElementById('pasos');
            if (lista.children.length >= 30) {
                alert('Una receta puede tener como maximo 30 pasos');
                return;
            }
            lista.insertAdjacentHTML('beforeend', document.getElementById('molde-paso').innerHTML);
        }

        //quita una fila, pero siempre deja al menos una (R1.16 y R1.18: minimo 1)
        function quitarFila(boton, idLista) {
            var lista = document.getElementById(idLista);
            if (lista.children.length > 1) {
                boton.parentNode.remove();
            }
        }
    </script>
</body>
</html>
