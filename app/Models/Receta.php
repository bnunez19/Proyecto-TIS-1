<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    protected $table = 'receta';
    protected $primaryKey = 'id_receta';
    public $timestamps = false;

    protected $fillable = [
        'nombre_receta', 'descripcion_corta', 'imagen_referencial', 'link_receta',
        'cant_personas', 'categoria', 'promedio_calificacion', 'cantidad_calificaciones',
    ];

    public function ingredientes()
    {
        return $this->hasMany(RecetaIngrediente::class, 'id_receta', 'id_receta');
    }

    public function pasos()
    {
        return $this->hasMany(PasoPreparacion::class, 'id_receta', 'id_receta')
                    ->orderBy('paso_numero');
    }

    public function regimenes()
    {
        return $this->hasMany(RecetaRegimen::class, 'id_receta', 'id_receta');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'id_receta', 'id_receta');
    }
}