<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReasignacionExpediente extends Model
{
    protected $table = 'reasignaciones_expedientes';

    protected $fillable = [
        'lote_uuid',
        'expediente_id',
        'usuario_origen_id',
        'usuario_destino_id',
        'realizado_por_id',
        'motivo',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function usuarioOrigen()
    {
        return $this->belongsTo(Usuario::class, 'usuario_origen_id');
    }

    public function usuarioDestino()
    {
        return $this->belongsTo(Usuario::class, 'usuario_destino_id');
    }

    public function realizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'realizado_por_id');
    }
}
