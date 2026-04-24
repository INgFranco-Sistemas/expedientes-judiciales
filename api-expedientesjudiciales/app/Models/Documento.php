<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    use SoftDeletes;

    protected $table = 'documentos';

    protected $fillable = [
        'expediente_id',
        'actuacion_id',
        'tipo_documento_id',
        'nombre_original',
        'nombre_guardado',
        'ruta_archivo',
        'extension',
        'mime_type',
        'peso_bytes',
        'hash_archivo',
        'version',
        'observacion',
        'subido_por',
        'fecha_subida',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'peso_bytes' => 'integer',
            'version' => 'integer',
            'fecha_subida' => 'datetime',
        ];
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function actuacion()
    {
        return $this->belongsTo(Actuacion::class, 'actuacion_id');
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    public function subidoPor()
    {
        return $this->belongsTo(Usuario::class, 'subido_por');
    }
}