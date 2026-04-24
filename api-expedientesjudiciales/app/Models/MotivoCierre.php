<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotivoCierre extends Model
{
    protected $table = 'motivos_cierre';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'motivo_cierre_id');
    }
}