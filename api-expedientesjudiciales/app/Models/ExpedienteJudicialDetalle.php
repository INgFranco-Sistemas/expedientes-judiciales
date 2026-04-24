<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpedienteJudicialDetalle extends Model
{
    protected $table = 'expediente_judicial_detalle';

    protected $fillable = [
        'expediente_id',
        'distrito_judicial_id',
        'dependencia_id',
        'especialidad_id',
        'instancia_id',
        'materia_id',
        'sumilla',
        'observaciones',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }
}