<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoAlerta extends Model
{
    protected $table = 'tipos_alerta';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function alertas()
    {
        return $this->hasMany(Alerta::class, 'tipo_alerta_id');
    }
}