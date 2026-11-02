<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasoPreparacion extends Model
{
    protected $table = 'paso_preparacion';
    protected $primaryKey = null; 
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_receta', 'paso_numero', 'descripcion_paso'];

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'id_receta', 'id_receta');
    }
}