<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actuacion;
use App\Models\Expediente;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;

class ActuacionController extends Controller
{
    public function index(Expediente $expediente)
    {
        return response()->json([
            'expediente_id' => $expediente->id,
            'actuaciones' => $expediente->actuaciones()
                ->with(['tipoActuacion', 'usuario', 'estadoResultante'])
                ->orderBy('fecha_actuacion', 'desc')
                ->paginate(10),
        ]);
    }

    public function store(Request $request, Expediente $expediente)
    {
        $data = $request->validate([
            'tipo_actuacion_id' => ['required', 'exists:tipos_actuacion,id'],
            'fecha_actuacion' => ['required', 'date'],
            'descripcion' => ['required', 'string'],
            'fecha_proxima_accion' => ['nullable', 'date'],
            'resultado' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
            'estado_resultante_id' => ['nullable', 'exists:estados_expediente,id'],
        ]);

        $data['expediente_id'] = $expediente->id;
        $data['usuario_id'] = $request->user()->id;

        $actuacion = Actuacion::create($data);

        $expedienteAntes = $expediente->toArray();

        $expediente->update([
            'fecha_ultima_actuacion' => date('Y-m-d', strtotime($data['fecha_actuacion'])),
            'fecha_proximo_vencimiento' => !empty($data['fecha_proxima_accion'])
                ? date('Y-m-d', strtotime($data['fecha_proxima_accion']))
                : $expediente->fecha_proximo_vencimiento,
            'estado_expediente_id' => $data['estado_resultante_id'] ?? $expediente->estado_expediente_id,
        ]);

        AuditoriaService::registrar(
            'actuaciones',
            $actuacion->id,
            'CREATE',
            null,
            $actuacion->toArray(),
            $request->user()->id,
            $request
        );

        AuditoriaService::registrar(
            'expedientes',
            $expediente->id,
            'UPDATE',
            $expedienteAntes,
            $expediente->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Actuación registrada correctamente.',
            'actuacion' => $actuacion->load(['tipoActuacion', 'usuario', 'estadoResultante']),
            'expediente' => $expediente->fresh(),
        ], 201);
    }

    public function show(Expediente $expediente, Actuacion $actuacion)
    {
        $this->validarActuacionPerteneceAlExpediente($expediente, $actuacion);

        return response()->json([
            'actuacion' => $actuacion->load(['tipoActuacion', 'usuario', 'estadoResultante', 'documentos']),
        ]);
    }

    public function update(Request $request, Expediente $expediente, Actuacion $actuacion)
    {
        $this->validarActuacionPerteneceAlExpediente($expediente, $actuacion);

        $antes = $actuacion->toArray();
        $expedienteAntes = $expediente->toArray();

        $data = $request->validate([
            'tipo_actuacion_id' => ['required', 'exists:tipos_actuacion,id'],
            'fecha_actuacion' => ['required', 'date'],
            'descripcion' => ['required', 'string'],
            'fecha_proxima_accion' => ['nullable', 'date'],
            'resultado' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
            'estado_resultante_id' => ['nullable', 'exists:estados_expediente,id'],
        ]);

        $actuacion->update($data);

        $expediente->update([
            'fecha_ultima_actuacion' => date('Y-m-d', strtotime($data['fecha_actuacion'])),
            'fecha_proximo_vencimiento' => !empty($data['fecha_proxima_accion'])
                ? date('Y-m-d', strtotime($data['fecha_proxima_accion']))
                : $expediente->fecha_proximo_vencimiento,
            'estado_expediente_id' => $data['estado_resultante_id'] ?? $expediente->estado_expediente_id,
        ]);

        AuditoriaService::registrar(
            'actuaciones',
            $actuacion->id,
            'UPDATE',
            $antes,
            $actuacion->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        AuditoriaService::registrar(
            'expedientes',
            $expediente->id,
            'UPDATE',
            $expedienteAntes,
            $expediente->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Actuación actualizada correctamente.',
            'actuacion' => $actuacion->fresh()->load(['tipoActuacion', 'usuario', 'estadoResultante']),
            'expediente' => $expediente->fresh(),
        ]);
    }

    public function destroy(Request $request, Expediente $expediente, Actuacion $actuacion)
    {
        $this->validarActuacionPerteneceAlExpediente($expediente, $actuacion);

        $antes = $actuacion->toArray();

        $actuacion->delete();

        AuditoriaService::registrar(
            'actuaciones',
            $actuacion->id,
            'DELETE_LOGICO',
            $antes,
            null,
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Actuación eliminada correctamente.',
        ]);
    }

    private function validarActuacionPerteneceAlExpediente(Expediente $expediente, Actuacion $actuacion): void
    {
        if ($actuacion->expediente_id !== $expediente->id) {
            abort(404, 'La actuación indicada no pertenece al expediente seleccionado.');
        }
    }
}