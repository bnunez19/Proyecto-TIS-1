<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_usuario'];

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'id_usuario', 'id_usuario');
    }
}