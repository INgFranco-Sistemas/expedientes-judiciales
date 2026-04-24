<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etapa extends Model
{
    protected $table = 'etapas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'etapa_id');
    }
}