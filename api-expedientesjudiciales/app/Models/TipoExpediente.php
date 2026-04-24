<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoExpediente extends Model
{
    protected $table = 'tipos_expediente';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'estado',
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'tipo_expediente_id');
    }
}