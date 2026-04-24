<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpedienteParte extends Model
{
    use SoftDeletes;

    protected $table = 'expediente_partes';

    protected $fillable = [
        'expediente_id',
        'tipo_parte',
        'tipo_persona',
        'nombres_razon_social',
        'documento_identidad',
        'correo',
        'telefono',
        'direccion',
        'observaciones',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }
}