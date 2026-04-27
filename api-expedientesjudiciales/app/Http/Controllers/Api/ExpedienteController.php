<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expediente;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpedienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Expediente::with([
            'tipoExpediente',
            'estadoExpediente',
            'encargadoActual',
            'prioridad',
            'materia',
            'especialidad',
        ])->orderBy('id', 'desc');

        if ($request->filled('buscar')) {
        $buscar = $request->buscar;

        $query->where(function ($q) use ($buscar) {
            $q->where('numero_expediente', 'ILIKE', "%{$buscar}%")
                ->orWhere('codigo_unico_interno', 'ILIKE', "%{$buscar}%")
                ->orWhere('pretensiones', 'ILIKE', "%{$buscar}%")
                ->orWhere('observaciones_generales', 'ILIKE', "%{$buscar}%")
                ->orWhereHas('tipoExpediente', function ($sub) use ($buscar) {
                    $sub->where('nombre', 'ILIKE', "%{$buscar}%");
                })
                ->orWhereHas('estadoExpediente', function ($sub) use ($buscar) {
                    $sub->where('nombre', 'ILIKE', "%{$buscar}%");
                })
                ->orWhereHas('prioridad', function ($sub) use ($buscar) {
                    $sub->where('nombre', 'ILIKE', "%{$buscar}%");
                })
                ->orWhereHas('encargadoActual', function ($sub) use ($buscar) {
                    $sub->where('nombre_completo', 'ILIKE', "%{$buscar}%")
                        ->orWhere('username', 'ILIKE', "%{$buscar}%");
                })
                ->orWhereHas('materia', function ($sub) use ($buscar) {
                    $sub->where('nombre', 'ILIKE', "%{$buscar}%");
                })
                ->orWhereHas('especialidad', function ($sub) use ($buscar) {
                    $sub->where('nombre', 'ILIKE', "%{$buscar}%");
                })
                ->orWhereHas('partes', function ($sub) use ($buscar) {
                    $sub->where('nombres_razon_social', 'ILIKE', "%{$buscar}%")
                        ->orWhere('documento_identidad', 'ILIKE', "%{$buscar}%");
                });
            });
        }

        if ($request->filled('tipo_expediente_id')) {
            $query->where('tipo_expediente_id', $request->tipo_expediente_id);
        }

        if ($request->filled('estado_expediente_id')) {
            $query->where('estado_expediente_id', $request->estado_expediente_id);
        }

        if ($request->filled('encargado_actual_id')) {
            $query->where('encargado_actual_id', $request->encargado_actual_id);
        }

        if ($request->filled('prioridad_id')) {
            $query->where('prioridad_id', $request->prioridad_id);
        }

        if ($request->filled('importante')) {
            $query->where('importante', filter_var($request->importante, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('estado_registro')) {
            $query->where('estado_registro', $request->estado_registro);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_registro', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_registro', '<=', $request->fecha_hasta);
        }

        return response()->json(
            $query->paginate($request->get('per_page', 10))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_expediente_id' => ['required', 'exists:tipos_expediente,id'],
            'numero_expediente' => [
                'required',
                'string',
                'max:100',
                Rule::unique('expedientes', 'numero_expediente')
                    ->where('tipo_expediente_id', $request->tipo_expediente_id)
                    ->where('anio_expediente', $request->anio_expediente),
            ],
            'anio_expediente' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'codigo_unico_interno' => ['required', 'string', 'max:50', 'unique:expedientes,codigo_unico_interno'],

            'distrito_judicial_id' => ['nullable', 'exists:distritos_judiciales,id'],
            'dependencia_id' => ['nullable', 'exists:dependencias,id'],
            'especialidad_id' => ['nullable', 'exists:especialidades,id'],
            'instancia_id' => ['nullable', 'exists:instancias,id'],
            'materia_id' => ['nullable', 'exists:materias,id'],
            'etapa_id' => ['nullable', 'exists:etapas,id'],

            'estado_expediente_id' => ['required', 'exists:estados_expediente,id'],
            'encargado_actual_id' => ['required', 'exists:usuarios,id'],
            'prioridad_id' => ['nullable', 'exists:prioridades,id'],

            'monto' => ['nullable', 'numeric', 'min:0'],
            'pretensiones' => ['nullable', 'string'],
            'observaciones_generales' => ['nullable', 'string'],

            'fecha_registro' => ['required', 'date'],
            'fecha_ingreso' => ['nullable', 'date'],
            'fecha_ultima_actuacion' => ['nullable', 'date'],
            'fecha_proximo_vencimiento' => ['nullable', 'date'],
            'fecha_cierre' => ['nullable', 'date'],

            'motivo_cierre_id' => ['nullable', 'exists:motivos_cierre,id'],
            'importante' => ['nullable', 'boolean'],
            'estado_registro' => ['required', Rule::in(['ACTIVO', 'CERRADO', 'ARCHIVADO'])],
        ]);

        $data['importante'] = $data['importante'] ?? false;

        $expediente = Expediente::create($data);

        AuditoriaService::registrar(
            'expedientes',
            $expediente->id,
            'CREATE',
            null,
            $expediente->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Expediente registrado correctamente.',
            'expediente' => $expediente->load([
                'tipoExpediente',
                'estadoExpediente',
                'encargadoActual',
                'prioridad',
                'materia',
                'especialidad',
            ]),
        ], 201);
    }

    public function show(Expediente $expediente)
    {
        return response()->json([
            'expediente' => $expediente->load([
                'tipoExpediente',
                'distritoJudicial',
                'dependencia',
                'especialidad',
                'instancia',
                'materia',
                'etapa',
                'estadoExpediente',
                'encargadoActual',
                'prioridad',
                'motivoCierre',
                'partes',
                'judicialDetalle',
                'penalDetalle',
                'mascDetalle',
                'actuaciones.tipoActuacion',
                'actuaciones.usuario',
                'documentos.tipoDocumento',
                'documentos.subidoPor',
                'alertas.tipoAlerta',
                'alertas.usuarioDestino',
            ]),
        ]);
    }

    public function update(Request $request, Expediente $expediente)
    {
        $antes = $expediente->toArray();

        $data = $request->validate([
            'tipo_expediente_id' => ['required', 'exists:tipos_expediente,id'],
            'numero_expediente' => [
                'required',
                'string',
                'max:100',
                Rule::unique('expedientes', 'numero_expediente')
                    ->where('tipo_expediente_id', $request->tipo_expediente_id)
                    ->where('anio_expediente', $request->anio_expediente)
                    ->ignore($expediente->id),
            ],
            'anio_expediente' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'codigo_unico_interno' => [
                'required',
                'string',
                'max:50',
                Rule::unique('expedientes', 'codigo_unico_interno')->ignore($expediente->id),
            ],

            'distrito_judicial_id' => ['nullable', 'exists:distritos_judiciales,id'],
            'dependencia_id' => ['nullable', 'exists:dependencias,id'],
            'especialidad_id' => ['nullable', 'exists:especialidades,id'],
            'instancia_id' => ['nullable', 'exists:instancias,id'],
            'materia_id' => ['nullable', 'exists:materias,id'],
            'etapa_id' => ['nullable', 'exists:etapas,id'],

            'estado_expediente_id' => ['required', 'exists:estados_expediente,id'],
            'encargado_actual_id' => ['required', 'exists:usuarios,id'],
            'prioridad_id' => ['nullable', 'exists:prioridades,id'],

            'monto' => ['nullable', 'numeric', 'min:0'],
            'pretensiones' => ['nullable', 'string'],
            'observaciones_generales' => ['nullable', 'string'],

            'fecha_registro' => ['required', 'date'],
            'fecha_ingreso' => ['nullable', 'date'],
            'fecha_ultima_actuacion' => ['nullable', 'date'],
            'fecha_proximo_vencimiento' => ['nullable', 'date'],
            'fecha_cierre' => ['nullable', 'date'],

            'motivo_cierre_id' => ['nullable', 'exists:motivos_cierre,id'],
            'importante' => ['nullable', 'boolean'],
            'estado_registro' => ['required', Rule::in(['ACTIVO', 'CERRADO', 'ARCHIVADO'])],
        ]);

        $data['importante'] = $data['importante'] ?? false;

        $expediente->update($data);

        AuditoriaService::registrar(
            'expedientes',
            $expediente->id,
            'UPDATE',
            $antes,
            $expediente->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Expediente actualizado correctamente.',
            'expediente' => $expediente->fresh()->load([
                'tipoExpediente',
                'estadoExpediente',
                'encargadoActual',
                'prioridad',
                'materia',
                'especialidad',
            ]),
        ]);
    }

    public function cerrar(Request $request, Expediente $expediente)
    {
        $data = $request->validate([
            'motivo_cierre_id' => ['required', 'exists:motivos_cierre,id'],
            'fecha_cierre' => ['required', 'date'],
            'observaciones_generales' => ['nullable', 'string'],
        ]);

        $antes = $expediente->toArray();

        $expediente->update([
            'estado_registro' => 'CERRADO',
            'motivo_cierre_id' => $data['motivo_cierre_id'],
            'fecha_cierre' => $data['fecha_cierre'],
            'observaciones_generales' => $data['observaciones_generales'] ?? $expediente->observaciones_generales,
        ]);

        AuditoriaService::registrar(
            'expedientes',
            $expediente->id,
            'CIERRE_EXPEDIENTE',
            $antes,
            $expediente->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Expediente cerrado correctamente.',
            'expediente' => $expediente->fresh()->load(['motivoCierre']),
        ]);
    }

    public function reabrir(Request $request, Expediente $expediente)
    {
        $data = $request->validate([
            'justificacion' => ['required', 'string', 'min:5'],
        ]);

        $antes = $expediente->toArray();

        $expediente->update([
            'estado_registro' => 'ACTIVO',
            'motivo_cierre_id' => null,
            'fecha_cierre' => null,
            'observaciones_generales' => trim(($expediente->observaciones_generales ?? '') . "\n\nReapertura: " . $data['justificacion']),
        ]);

        AuditoriaService::registrar(
            'expedientes',
            $expediente->id,
            'REAPERTURA_EXPEDIENTE',
            $antes,
            $expediente->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Expediente reabierto correctamente.',
            'expediente' => $expediente->fresh(),
        ]);
    }

    public function marcarImportante(Request $request, Expediente $expediente)
    {
        $data = $request->validate([
            'importante' => ['required', 'boolean'],
        ]);

        $antes = $expediente->toArray();

        $expediente->update([
            'importante' => $data['importante'],
        ]);

        AuditoriaService::registrar(
            'expedientes',
            $expediente->id,
            'UPDATE',
            $antes,
            $expediente->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Marcación de importancia actualizada correctamente.',
            'expediente' => $expediente->fresh(),
        ]);
    }

    public function destroy(Request $request, Expediente $expediente)
    {
        $antes = $expediente->toArray();

        $expediente->delete();

        AuditoriaService::registrar(
            'expedientes',
            $expediente->id,
            'DELETE_LOGICO',
            $antes,
            null,
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Expediente eliminado correctamente.',
        ]);
    }
}