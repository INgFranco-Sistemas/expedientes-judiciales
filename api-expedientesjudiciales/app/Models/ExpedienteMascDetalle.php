<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpedienteMascDetalle extends Model
{
    protected $table = 'expediente_masc_detalle';

    protected $fillable = [
        'expediente_id',
        'subtipo_masc',
        'solicitante_id_parte',
        'contratista_id_parte',
        'contrato_id',
        'centro_masc',
        'estado_especial',
        'observaciones',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(ExpedienteParte::class, 'solicitante_id_parte');
    }

    public function contratista()
    {
        return $this->belongsTo(ExpedienteParte::class, 'contratista_id_parte');
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }
}