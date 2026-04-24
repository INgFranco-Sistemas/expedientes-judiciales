<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoExpediente extends Model
{
    protected $table = 'estados_expediente';

    protected $fillable = [
        'nombre',
        'descripcion',
        'color_ui',
        'orden_visual',
        'estado',
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'estado_expediente_id');
    }
}