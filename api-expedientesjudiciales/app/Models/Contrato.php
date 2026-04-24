<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $table = 'contratos';

    protected $fillable = [
        'numero_contrato',
        'objeto_contrato',
        'fecha_contrato',
        'entidad_contratante',
        'contratista',
        'monto_contrato',
        'observaciones',
        'documento_contrato_url',
    ];

    protected function casts(): array
    {
        return [
            'fecha_contrato' => 'date',
            'monto_contrato' => 'decimal:2',
        ];
    }

    public function expedienteMascDetalles()
    {
        return $this->hasMany(ExpedienteMascDetalle::class, 'contrato_id');
    }
}