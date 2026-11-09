<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expediente;
use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Models\ReasignacionExpediente;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReasignacionExpedienteController extends Controller
{
    public function expedientesPorGestor(Request $request)
    {
        $data = $request->validate([
            'usuario_origen_id' => [
                'required',
                'integer',
                'exists:usuarios,id'
            ],
        ]);

        $usuario = $request->user()->load('perfil');

        if ($usuario->perfil?->nombre !== 'Administrador Procuraduria') {
            return response()->json([
                'message' => 'Solo el Administrador puede gestionar reasignaciones.'
            ], 403);
        }

        $gestor = Usuario::with('perfil')
            ->findOrFail($data['usuario_origen_id']);

        if ($gestor->perfil?->nombre !== 'Gestor Procuraduria') {
            return response()->json([
                'message' => 'El usuario de origen debe ser un Gestor Procuraduria.'
            ], 422);
        }

        $expedientes = Expediente::with([
                'tipoExpediente',
                'estadoExpediente',
                'prioridad',
                'materia',
                'encargadoActual',
            ])
            ->where('encargado_actual_id', $gestor->id)
            ->where('estado_registro', 'ACTIVO')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'gestor' => [
                'id' => $gestor->id,
                'nombre_completo' => $gestor->nombre_completo,
            ],
            'total_expedientes' => $expedientes->count(),
            'expedientes' => $expedientes,
        ]);
    }

    public function gestoresActivos(Request $request)
    {
        $usuario = $request->user()->load('perfil');

        if (
            $usuario->perfil?->nombre !== 'Administrador Procuraduria'
            || !$usuario->tienePermiso('expedientes.reasignar')
        ) {
            return response()->json([
                'message' => 'No tiene permiso para consultar los gestores.'
            ], 403);
        }

        $gestores = Usuario::where('estado_usuario', 'ACTIVO')
            ->whereHas('perfil', function ($query) {
                $query->where('nombre', 'Gestor Procuraduria');
            })
            ->orderBy('nombre_completo')
            ->get(['id', 'username', 'nombre_completo']);

        return response()->json($gestores);
    }
    
    public function reasignar(Request $request)
    {
        $usuario = $request->user()->load('perfil');

        if (
            $usuario->perfil?->nombre !== 'Administrador Procuraduria'
            || !$usuario->tienePermiso('expedientes.reasignar')
        ) {
            return response()->json([
                'message' => 'Solo el Administrador puede reasignar expedientes.'
            ], 403);
        }

        $data = $request->validate([
            'usuario_origen_id' => [
                'required', 'integer', 'exists:usuarios,id'
            ],
            'motivo' => [
                'required', 'string', 'min:10', 'max:2000'
            ],
            'asignaciones' => [
                'required', 'array', 'min:1'
            ],
            'asignaciones.*.expediente_id' => [
                'required', 'integer', 'distinct', 'exists:expedientes,id'
            ],
            'asignaciones.*.usuario_destino_id' => [
                'required', 'integer', 'exists:usuarios,id'
            ],
        ]);

        $origen = Usuario::with('perfil')
            ->findOrFail($data['usuario_origen_id']);

        if ($origen->perfil?->nombre !== 'Gestor Procuraduria') {
            throw ValidationException::withMessages([
                'usuario_origen_id' => 'El usuario de origen debe ser un gestor.'
            ]);
        }

        $destinoIds = collect($data['asignaciones'])
            ->pluck('usuario_destino_id')
            ->unique()
            ->values();

        if ($destinoIds->contains($origen->id)) {
            throw ValidationException::withMessages([
                'asignaciones' => 'El origen y el destino no pueden ser el mismo usuario.'
            ]);
        }

        $destinosValidos = Usuario::whereIn('id', $destinoIds)
            ->where('estado_usuario', 'ACTIVO')
            ->whereHas('perfil', function ($query) {
                $query->where('nombre', 'Gestor Procuraduria');
            })
            ->count();

        if ($destinosValidos !== $destinoIds->count()) {
            throw ValidationException::withMessages([
                'asignaciones' => 'Todos los destinatarios deben ser gestores activos.'
            ]);
        }

        $loteUuid = (string) Str::uuid();

        $resultado = DB::transaction(function () use (
            $data,
            $usuario,
            $origen,
            $loteUuid,
            $request
        ) {
            $ids = collect($data['asignaciones'])
                ->pluck('expediente_id')
                ->all();

            $expedientes = Expediente::whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($expedientes->count() !== count($ids)) {
                throw ValidationException::withMessages([
                    'asignaciones' => 'Uno o más expedientes no están disponibles.'
                ]);
            }

            foreach ($expedientes as $expediente) {
                if (
                    (int) $expediente->encargado_actual_id !== (int) $origen->id
                    || $expediente->estado_registro !== 'ACTIVO'
                ) {
                    throw ValidationException::withMessages([
                        'asignaciones' =>
                            'Uno o más expedientes ya no pertenecen al gestor de origen o no están activos.'
                    ]);
                }
            }

            $movimientos = [];

            foreach ($data['asignaciones'] as $asignacion) {
                $expediente = $expedientes->get(
                    $asignacion['expediente_id']
                );

                $antes = $expediente->toArray();

                $expediente->update([
                    'encargado_actual_id' =>
                        $asignacion['usuario_destino_id']
                ]);

                ReasignacionExpediente::create([
                    'lote_uuid' => $loteUuid,
                    'expediente_id' => $expediente->id,
                    'usuario_origen_id' => $origen->id,
                    'usuario_destino_id' =>
                        $asignacion['usuario_destino_id'],
                    'realizado_por_id' => $usuario->id,
                    'motivo' => $data['motivo'],
                ]);

                AuditoriaService::registrar(
                    'expedientes',
                    $expediente->id,
                    'REASIGNACION',
                    $antes,
                    $expediente->fresh()->toArray(),
                    $usuario->id,
                    $request
                );

                $movimientos[] = [
                    'expediente_id' => $expediente->id,
                    'usuario_origen_id' => $origen->id,
                    'usuario_destino_id' =>
                        $asignacion['usuario_destino_id'],
                ];
            }

            return $movimientos;
        }, 3);

        return response()->json([
            'message' => 'Expedientes reasignados correctamente.',
            'lote_uuid' => $loteUuid,
            'total_reasignados' => count($resultado),
            'movimientos' => $resultado,
        ]);
    }
}
