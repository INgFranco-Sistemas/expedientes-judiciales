<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prioridad extends Model
{
    protected $table = 'prioridades';

    protected $fillable = [
        'nombre',
        'nivel',
        'color_ui',
        'estado',
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'prioridad_id');
    }
}