<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoActuacion extends Model
{
    protected $table = 'tipos_actuacion';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function actuaciones()
    {
        return $this->hasMany(Actuacion::class, 'tipo_actuacion_id');
    }
}