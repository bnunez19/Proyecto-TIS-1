<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar receta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- R1.22: los campos con error se marcan en rojo --}}
    <style>
        .rojo { border: 2px solid red !important; }
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
    <main class="container py-4" style="max-width: 900px;">
        <h1 class="h3 mb-4 fw-bold">Ingresar receta</h1>

        {{-- R1.22 y Salida 2: busca y muestra los mensajes de error (sin repetir los iguales) --}}
        @if ($errors->any())
            <div class="alert alert-danger fw-bold" role="alert">
                @foreach (collect($errors->all())->unique() as $error)
                    <div>{{$error}}</div>
                @endforeach
            </div>
        @endif

        {{-- formulario: los datos se envian a la ruta ingreso.guardar y asi de esa forma la receta se guarda
             enctype multipart: se usa para enviar la imagen como archivo (R1.7)
             novalidate: los mensajes de error los entrega el sistema y no el navegador (R1.22) --}}
        <form method="POST" action="{{route('ingreso.guardar')}}" enctype="multipart/form-data" novalidate class="card shadow-sm p-4 mb-4 border-0">
            @csrf

            {{-- R1.2 y R1.15: nombre de la receta --}}
            <div class="mb-3">
                <label for="nombre_receta" class="form-label fw-bold">Nombre de la receta:</label>
                <input type="text" id="nombre_receta" name="nombre_receta" value="{{old('nombre_receta')}}"
                       class="form-control {{ $errors->has('nombre_receta') ? 'rojo' : '' }}">
            </div>

            {{-- R1.3 y R1.15: descripcion corta --}}
            <div class="mb-3">
                <label for="descripcion_corta" class="form-label fw-bold">Descripción corta:</label>
                <input type="text" id="descripcion_corta" name="descripcion_corta" value="{{old('descripcion_corta')}}"
                       class="form-control {{ $errors->has('descripcion_corta') ? 'rojo' : '' }}">
            </div>

            {{-- R1.7, R1.15 y R1.19: la imagen es un archivo adjunto JPG, JPEG o PNG
                 (despues de un error hay que volver a elegirla, el navegador no la conserva) --}}
            <div class="mb-3">
                <label for="imagen_referencial" class="form-label fw-bold">Imagen referencial (JPG o PNG):</label>
                <input type="file" id="imagen_referencial" name="imagen_referencial" accept=".jpg,.jpeg,.png"
                       class="form-control {{ $errors->has('imagen_referencial') ? 'rojo' : '' }}">
            </div>

            {{-- R1.8 y R1.15: cantidad de personas (min y max limitan la flecha del campo) --}}
            <div class="mb-3">
                <label for="cant_personas" class="form-label fw-bold">Cantidad de personas:</label>
                <input type="number" id="cant_personas" name="cant_personas" min="1" max="50" step="1" value="{{old('cant_personas')}}"
                       class="form-control {{ $errors->has('cant_personas') ? 'rojo' : '' }}">
            </div>

            {{-- R1.9 y R1.17: categoria, lista cerrada de una sola opcion --}}
            <div class="mb-3">
                <label for="categoria" class="form-label fw-bold">Categoría:</label>
                <select id="categoria" name="categoria" class="form-select {{ $errors->has('categoria') ? 'rojo' : '' }}">
                    <option value="">Seleccione</option>
                    @foreach ($categorias as $cat)
                        <option value="{{$cat}}" @selected(old('categoria') === $cat)>{{$cat}}</option>
                    @endforeach
                </select>
            </div>

            {{-- R1.10 y R1.17: regimen alimenticio, lista cerrada de una o mas opciones --}}
            <div class="mb-3">
                <label class="form-label d-block fw-bold">Régimen alimenticio:</label>
                <div class="{{ $errors->has('regimen') ? 'p-2 border border-danger rounded' : '' }}">
                    @foreach ($regimenes as $reg)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="regimen[]" value="{{$reg}}" id="reg_{{$loop->index}}"
                                   @checked(in_array($reg, (array) old('regimen', [])))>
                            <label class="form-check-label fw-semibold" for="reg_{{$loop->index}}">{{$reg}}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <hr class="my-4">

            {{-- R1.16: de 1 a 50 ingredientes. Cada uno con nombre (R1.4), cantidad (R1.5) y unidad (R1.6) --}}
            <h3 class="h5 mb-3 fw-bold">Ingredientes</h3>
            <div id="ingredientes" class="vstack gap-2 mb-3">
                @php $filasIng = old('ingredientes', [['nombre' => '', 'cantidad' => '', 'unidad' => '']]); @endphp
                @foreach ($filasIng as $i => $ing)
                    <div class="input-group" data-fila>
                        <input type="text" name="ingredientes[{{$i}}][nombre]" placeholder="Ingrediente"
                               value="{{$ing['nombre'] ?? ''}}"
                               class="form-control {{ $errors->has("ingredientes.$i.nombre") ? 'rojo' : '' }}">
                        <input type="number" min="0.01" max="10000" step="0.01" name="ingredientes[{{$i}}][cantidad]" placeholder="Cantidad"
                               value="{{$ing['cantidad'] ?? ''}}"
                               class="form-control {{ $errors->has("ingredientes.$i.cantidad") ? 'rojo' : '' }}">
                        <select name="ingredientes[{{$i}}][unidad]" class="form-select {{ $errors->has("ingredientes.$i.unidad") ? 'rojo' : '' }}">
                            <option value="">Unidad</option>
                            @foreach ($unidades as $u)
                                <option value="{{$u}}" @selected(($ing['unidad'] ?? '') === $u)>{{$u}}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-danger fw-bold" onclick="quitarFila(this, 'ingredientes')">Quitar</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm mb-4 fw-bold" onclick="agregarIngrediente()">Agregar ingrediente</button>
            <hr class="my-4">

            {{-- R1.18: de 1 a 30 pasos. La lista numerada pone el numero sola
                 (R1.12: guarda PASO_NUMERO correlativo desde 1) --}}
            <h3 class="h5 mb-3 fw-bold">Pasos de preparación</h3>
            <ol id="pasos" class="ps-4 mb-3">
                @php $filasPasos = old('pasos', ['']); @endphp
                @foreach ($filasPasos as $i => $paso)
                    <li class="mb-2" data-fila>
                        <div class="input-group">
                            <textarea name="pasos[]" rows="2" class="form-control {{ $errors->has("pasos.$i") ? 'rojo' : '' }}">{{$paso}}</textarea>
                            <button type="button" class="btn btn-outline-danger fw-bold" onclick="quitarFila(this, 'pasos')">Quitar</button>
                        </div>
                    </li>
                @endforeach
            </ol>
            <button type="button" class="btn btn-outline-secondary btn-sm mb-4 fw-bold" onclick="agregarPaso()">Agregar paso</button>
            <hr class="my-4">

            <button type="submit" class="btn btn-pastel-naranja w-100">Guardar receta</button>
        </form>

        {{-- moldes de las filas nuevas (NUEVO se reemplaza por un numero distinto en cada fila) --}}
        <template id="molde-ingrediente">
            <div class="input-group" data-fila>
                <input type="text" name="ingredientes[NUEVO][nombre]" placeholder="Ingrediente" class="form-control">
                <input type="number" min="0.01" max="10000" step="0.01" name="ingredientes[NUEVO][cantidad]" placeholder="Cantidad" class="form-control">
                <select name="ingredientes[NUEVO][unidad]" class="form-select">
                    <option value="">Unidad</option>
                    @foreach ($unidades as $u)
                        <option value="{{$u}}">{{$u}}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-outline-danger fw-bold" onclick="quitarFila(this, 'ingredientes')">Quitar</button>
            </div>
        </template>

        <template id="molde-paso">
            <li class="mb-2" data-fila>
                <div class="input-group">
                    <textarea name="pasos[]" rows="2" class="form-control"></textarea>
                    <button type="button" class="btn btn-outline-danger fw-bold" onclick="quitarFila(this, 'pasos')">Quitar</button>
                </div>
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
                    boton.closest('[data-fila]').remove();
                }
            }
        </script>
    </main>
</body>
</html>
