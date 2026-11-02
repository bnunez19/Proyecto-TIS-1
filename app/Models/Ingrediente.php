<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingrediente extends Model
{
    protected $table = 'ingrediente';
    protected $primaryKey = 'id_ingrediente';
    public $timestamps = false;

    protected $fillable = ['nombre_ingrediente'];

    public function recetas()
    {
        return $this->hasMany(RecetaIngrediente::class, 'id_ingrediente', 'id_ingrediente');
    }
}