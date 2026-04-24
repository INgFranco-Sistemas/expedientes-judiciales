<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expediente extends Model
{
    use SoftDeletes;

    protected $table = 'expedientes';

    protected $fillable = [
        'tipo_expediente_id',
        'numero_expediente',
        'anio_expediente',
        'codigo_unico_interno',
        'distrito_judicial_id',
        'dependencia_id',
        'especialidad_id',
        'instancia_id',
        'materia_id',
        'etapa_id',
        'estado_expediente_id',
        'encargado_actual_id',
        'prioridad_id',
        'monto',
        'pretensiones',
        'observaciones_generales',
        'fecha_registro',
        'fecha_ingreso',
        'fecha_ultima_actuacion',
        'fecha_proximo_vencimiento',
        'fecha_cierre',
        'motivo_cierre_id',
        'importante',
        'estado_registro',
    ];

    protected function casts(): array
    {
        return [
            'anio_expediente' => 'integer',
            'monto' => 'decimal:2',
            'fecha_registro' => 'date',
            'fecha_ingreso' => 'date',
            'fecha_ultima_actuacion' => 'date',
            'fecha_proximo_vencimiento' => 'date',
            'fecha_cierre' => 'date',
            'importante' => 'boolean',
        ];
    }

    public function tipoExpediente()
    {
        return $this->belongsTo(TipoExpediente::class, 'tipo_expediente_id');
    }

    public function distritoJudicial()
    {
        return $this->belongsTo(DistritoJudicial::class, 'distrito_judicial_id');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'dependencia_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function instancia()
    {
        return $this->belongsTo(Instancia::class, 'instancia_id');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function etapa()
    {
        return $this->belongsTo(Etapa::class, 'etapa_id');
    }

    public function estadoExpediente()
    {
        return $this->belongsTo(EstadoExpediente::class, 'estado_expediente_id');
    }

    public function encargadoActual()
    {
        return $this->belongsTo(Usuario::class, 'encargado_actual_id');
    }

    public function prioridad()
    {
        return $this->belongsTo(Prioridad::class, 'prioridad_id');
    }

    public function motivoCierre()
    {
        return $this->belongsTo(MotivoCierre::class, 'motivo_cierre_id');
    }

    public function partes()
    {
        return $this->hasMany(ExpedienteParte::class, 'expediente_id');
    }

    public function judicialDetalle()
    {
        return $this->hasOne(ExpedienteJudicialDetalle::class, 'expediente_id');
    }

    public function penalDetalle()
    {
        return $this->hasOne(ExpedientePenalDetalle::class, 'expediente_id');
    }

    public function mascDetalle()
    {
        return $this->hasOne(ExpedienteMascDetalle::class, 'expediente_id');
    }

    public function actuaciones()
    {
        return $this->hasMany(Actuacion::class, 'expediente_id');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'expediente_id');
    }

    public function alertas()
    {
        return $this->hasMany(Alerta::class, 'expediente_id');
    }
}