<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecetaRegimen extends Model
{
    protected $table = 'receta_regimen';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_receta', 'regimen_alimenticio'];

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'id_receta', 'id_receta');
    }
}