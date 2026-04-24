<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    protected $table = 'materias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'materia_id');
    }
}