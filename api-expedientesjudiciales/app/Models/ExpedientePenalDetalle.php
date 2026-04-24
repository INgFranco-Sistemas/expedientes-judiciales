<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpedientePenalDetalle extends Model
{
    protected $table = 'expediente_penal_detalle';

    protected $fillable = [
        'expediente_id',
        'distrito_judicial_id',
        'dependencia_id',
        'materia_id',
        'etapa_id',
        'especialidad_id',
        'delito',
        'fiscalia',
        'observaciones',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }
}