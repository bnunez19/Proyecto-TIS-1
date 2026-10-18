<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar receta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> / */
</head>
<body class="bg-light">
<div class="container py-4">

    <h1 class="mb-4">Ingresar receta</h1>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('ingreso.guardar') }}" method="POST" class="card card-body">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nombre de la receta</label>
            <input type="text" name="nombre_receta" class="form-control" maxlength="60" value="{{ old('nombre_receta') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción </label>
            <input type="text" name="descripcion_corta" class="form-control" maxlength="150" value="{{ old('descripcion_corta') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Imagen referencial (enlace)</label>
            <input type="url" name="imagen_referencial" class="form-control" placeholder="https://..." value="{{ old('imagen_referencial') }}">
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Cantidad de personas</label>
                <input type="number" name="cant_personas" class="form-control" min="1" max="50" value="{{ old('cant_personas') }}">
            </div>
            <div class="col-md-8 mb-3">
                <label class="form-label">Tipo de plato</label>
                <select name="categoria" class="form-select">
                    <option value="">Seleccione...</option>
                    @foreach (['Entrada', 'Plato de fondo', 'Postre', 'Ensalada', 'Sopa', 'Bebestible', 'Aperitivo'] as $c)
                        <option value="{{ $c }}" @selected(old('categoria') === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label d-block">Régimen alimenticio</label>
            @foreach (['Normal', 'Vegetariano', 'Vegano', 'Diabético', 'Celíaco', 'Hipertenso'] as $i => $reg)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="regimen[]" id="reg{{ $i }}" value="{{ $reg }}"
                           @checked(in_array($reg, (array) old('regimen', [])))>
                    <label class="form-check-label" for="reg{{ $i }}">{{ $reg }}</label>
                </div>
            @endforeach
        </div>

        <hr>

        <h5>Ingredientes</h5>
        @foreach ([0, 1, 2] as $i)
            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <input type="text" name="ingredientes[{{ $i }}][nombre]" class="form-control" placeholder="Ingrediente"
                           value="{{ old("ingredientes.$i.nombre") }}">
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="ingredientes[{{ $i }}][cantidad]" class="form-control" placeholder="Cantidad"
                           value="{{ old("ingredientes.$i.cantidad") }}">
                </div>
                <div class="col-md-3">
                    <select name="ingredientes[{{ $i }}][unidad]" class="form-select">
                        <option value="">Unidad...</option>
                        @foreach (['gramos', 'kilogramos', 'mililitros', 'litros', 'tazas', 'cucharadas', 'cucharaditas', 'unidades', 'pizca'] as $u)
                            <option value="{{ $u }}" @selected(old("ingredientes.$i.unidad") === $u)>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endforeach

        <hr>

        <h5>Pasos de preparación</h5>
        @foreach ([0, 1, 2] as $i)
            <div class="input-group mb-2">
                <span class="input-group-text">{{ $i + 1 }}</span>
                <textarea name="pasos[]" class="form-control" rows="2" maxlength="300">{{ old("pasos.$i") }}</textarea>
            </div>
        @endforeach

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Guardar receta</button>
        </div>
    </form>

</div>
</body>
</html>
