<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table = 'perfiles';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'perfil_id');
    }

    public function permisos()
    {
        return $this->belongsToMany(
            Permiso::class,
            'perfil_permiso',
            'perfil_id',
            'permiso_id'
        );
    }
}