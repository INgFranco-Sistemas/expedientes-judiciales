<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'modulo',
        'estado',
    ];

    public function perfiles()
    {
        return $this->belongsToMany(
            Perfil::class,
            'perfil_permiso',
            'permiso_id',
            'perfil_id'
        );
    }
}