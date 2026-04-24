<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model
{
    protected $table = 'dependencias';

    protected $fillable = [
        'distrito_judicial_id',
        'nombre',
        'descripcion',
        'estado',
    ];

    public function distritoJudicial()
    {
        return $this->belongsTo(DistritoJudicial::class, 'distrito_judicial_id');
    }

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'dependencia_id');
    }
}