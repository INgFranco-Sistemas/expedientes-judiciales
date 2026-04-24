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
        return $this->hasMany(Usuario::class);
    }
}