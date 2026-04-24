<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistritoJudicial extends Model
{
    protected $table = 'distritos_judiciales';

    protected $fillable = [
        'nombre',
        'codigo',
        'estado',
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'distrito_judicial_id');
    }
}