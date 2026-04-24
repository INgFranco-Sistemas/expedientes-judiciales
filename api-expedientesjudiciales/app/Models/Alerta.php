<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alerta extends Model
{
    use SoftDeletes;

    protected $table = 'alertas';

    protected $fillable = [
        'expediente_id',
        'tipo_alerta_id',
        'fecha_alerta',
        'mensaje',
        'usuario_destino_id',
        'leido',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_alerta' => 'datetime',
            'leido' => 'boolean',
        ];
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function tipoAlerta()
    {
        return $this->belongsTo(TipoAlerta::class, 'tipo_alerta_id');
    }

    public function usuarioDestino()
    {
        return $this->belongsTo(Usuario::class, 'usuario_destino_id');
    }
}