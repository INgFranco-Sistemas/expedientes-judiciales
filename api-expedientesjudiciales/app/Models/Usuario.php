<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'username',
        'password',
        'nombres',
        'apellidos',
        'nombre_completo',
        'dni',
        'correo_institucional',
        'telefono', 
        'especialidad_id',
        'perfil_id',
        'estado_usuario',
        'fecha_inicio_asignacion',
        'fecha_termino_asignacion',
        'ultimo_acceso_at',
        'intentos_fallidos',
        'bloqueado_hasta',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio_asignacion' => 'date',
            'fecha_termino_asignacion' => 'date',
            'ultimo_acceso_at' => 'datetime',
            'bloqueado_hasta' => 'datetime',
            'intentos_fallidos' => 'integer',
        ];
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function actuaciones()
    {
        return $this->hasMany(Actuacion::class, 'usuario_id');
    }

    public function documentosSubidos()
    {
        return $this->hasMany(Documento::class, 'subido_por');
    }

    public function alertasRecibidas()
    {
        return $this->hasMany(Alerta::class, 'usuario_destino_id');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'usuario_id');
    }
}