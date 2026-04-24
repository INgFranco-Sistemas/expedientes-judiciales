<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instancia extends Model
{
    protected $table = 'instancias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'instancia_id');
    }
}