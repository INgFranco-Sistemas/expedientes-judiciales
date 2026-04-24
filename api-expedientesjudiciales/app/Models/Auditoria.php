<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    protected $fillable = [
        'tabla_afectada',
        'registro_id',
        'accion',
        'valor_anterior_json',
        'valor_nuevo_json',
        'usuario_id',
        'ip',
        'user_agent',
        'fecha_evento',
    ];

    protected function casts(): array
    {
        return [
            'valor_anterior_json' => 'array',
            'valor_nuevo_json' => 'array',
            'fecha_evento' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}