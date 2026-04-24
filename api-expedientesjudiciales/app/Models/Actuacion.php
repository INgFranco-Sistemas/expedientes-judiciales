<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actuacion extends Model
{
    use SoftDeletes;

    protected $table = 'actuaciones';

    protected $fillable = [
        'expediente_id',
        'tipo_actuacion_id',
        'fecha_actuacion',
        'descripcion',
        'usuario_id',
        'fecha_proxima_accion',
        'resultado',
        'observaciones',
        'estado_resultante_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_actuacion' => 'datetime',
            'fecha_proxima_accion' => 'datetime',
        ];
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function tipoActuacion()
    {
        return $this->belongsTo(TipoActuacion::class, 'tipo_actuacion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function estadoResultante()
    {
        return $this->belongsTo(EstadoExpediente::class, 'estado_resultante_id');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'actuacion_id');
    }
}