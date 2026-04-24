<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Http\Request;

class AuditoriaService
{
    public static function registrar(
        string $tablaAfectada,
        int $registroId,
        string $accion,
        ?array $valorAnterior = null,
        ?array $valorNuevo = null,
        ?int $usuarioId = null,
        ?Request $request = null
    ): Auditoria {
        return Auditoria::create([
            'tabla_afectada' => $tablaAfectada,
            'registro_id' => $registroId,
            'accion' => $accion,
            'valor_anterior_json' => $valorAnterior,
            'valor_nuevo_json' => $valorNuevo,
            'usuario_id' => $usuarioId,
            'ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'fecha_evento' => now(),
        ]);
    }
}