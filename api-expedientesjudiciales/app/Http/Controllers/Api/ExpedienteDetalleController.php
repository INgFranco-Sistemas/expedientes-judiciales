<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Expediente;
use App\Models\ExpedienteJudicialDetalle;
use App\Models\ExpedientePenalDetalle;
use App\Models\ExpedienteMascDetalle;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpedienteDetalleController extends Controller
{
    public function verJudicial(Expediente $expediente)
    {
        return response()->json([
            'detalle' => $expediente->judicialDetalle,
        ]);
    }

    public function guardarJudicial(Request $request, Expediente $expediente)
    {
        $data = $request->validate([
            'distrito_judicial_id' => ['nullable', 'exists:distritos_judiciales,id'],
            'dependencia_id' => ['nullable', 'exists:dependencias,id'],
            'especialidad_id' => ['nullable', 'exists:especialidades,id'],
            'instancia_id' => ['nullable', 'exists:instancias,id'],
            'materia_id' => ['nullable', 'exists:materias,id'],
            'sumilla' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $detalle = ExpedienteJudicialDetalle::where('expediente_id', $expediente->id)->first();
        $antes = $detalle?->toArray();

        $data['expediente_id'] = $expediente->id;

        $detalle = ExpedienteJudicialDetalle::updateOrCreate(
            ['expediente_id' => $expediente->id],
            $data
        );

        AuditoriaService::registrar(
            'expediente_judicial_detalle',
            $detalle->id,
            $antes ? 'UPDATE' : 'CREATE',
            $antes,
            $detalle->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Detalle judicial guardado correctamente.',
            'detalle' => $detalle->fresh(),
        ]);
    }

    public function verPenal(Expediente $expediente)
    {
        return response()->json([
            'detalle' => $expediente->penalDetalle,
        ]);
    }

    public function guardarPenal(Request $request, Expediente $expediente)
    {
        $data = $request->validate([
            'distrito_judicial_id' => ['nullable', 'exists:distritos_judiciales,id'],
            'dependencia_id' => ['nullable', 'exists:dependencias,id'],
            'materia_id' => ['nullable', 'exists:materias,id'],
            'etapa_id' => ['nullable', 'exists:etapas,id'],
            'especialidad_id' => ['nullable', 'exists:especialidades,id'],
            'delito' => ['nullable', 'string', 'max:250'],
            'fiscalia' => ['nullable', 'string', 'max:250'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $detalle = ExpedientePenalDetalle::where('expediente_id', $expediente->id)->first();
        $antes = $detalle?->toArray();

        $data['expediente_id'] = $expediente->id;

        $detalle = ExpedientePenalDetalle::updateOrCreate(
            ['expediente_id' => $expediente->id],
            $data
        );

        AuditoriaService::registrar(
            'expediente_penal_detalle',
            $detalle->id,
            $antes ? 'UPDATE' : 'CREATE',
            $antes,
            $detalle->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Detalle penal guardado correctamente.',
            'detalle' => $detalle->fresh(),
        ]);
    }

    public function verMasc(Expediente $expediente)
    {
        return response()->json([
            'detalle' => $expediente->mascDetalle?->load(['solicitante', 'contratista', 'contrato']),
        ]);
    }

    public function guardarMasc(Request $request, Expediente $expediente)
    {
        $data = $request->validate([
            'subtipo_masc' => ['required', Rule::in(['ARBITRAJE', 'CONCILIACION'])],
            'solicitante_id_parte' => ['nullable', 'exists:expediente_partes,id'],
            'contratista_id_parte' => ['nullable', 'exists:expediente_partes,id'],
            'contrato_id' => ['nullable', 'exists:contratos,id'],
            'centro_masc' => ['nullable', 'string', 'max:250'],
            'estado_especial' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string'],
        ]);

        if (!empty($data['solicitante_id_parte'])) {
            $pertenece = $expediente->partes()
                ->where('id', $data['solicitante_id_parte'])
                ->exists();

            if (!$pertenece) {
                return response()->json([
                    'message' => 'El solicitante indicado no pertenece al expediente.',
                ], 422);
            }
        }

        if (!empty($data['contratista_id_parte'])) {
            $pertenece = $expediente->partes()
                ->where('id', $data['contratista_id_parte'])
                ->exists();

            if (!$pertenece) {
                return response()->json([
                    'message' => 'El contratista indicado no pertenece al expediente.',
                ], 422);
            }
        }

        $detalle = ExpedienteMascDetalle::where('expediente_id', $expediente->id)->first();
        $antes = $detalle?->toArray();

        $data['expediente_id'] = $expediente->id;

        $detalle = ExpedienteMascDetalle::updateOrCreate(
            ['expediente_id' => $expediente->id],
            $data
        );

        AuditoriaService::registrar(
            'expediente_masc_detalle',
            $detalle->id,
            $antes ? 'UPDATE' : 'CREATE',
            $antes,
            $detalle->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Detalle de arbitraje/conciliación guardado correctamente.',
            'detalle' => $detalle->fresh()->load(['solicitante', 'contratista', 'contrato']),
        ]);
    }

    public function listarContratos()
    {
        return response()->json([
            'contratos' => Contrato::orderBy('id', 'desc')->paginate(10),
        ]);
    }

    public function guardarContrato(Request $request)
    {
        $data = $request->validate([
            'numero_contrato' => ['required', 'string', 'max:100', 'unique:contratos,numero_contrato'],
            'objeto_contrato' => ['nullable', 'string'],
            'fecha_contrato' => ['nullable', 'date'],
            'entidad_contratante' => ['nullable', 'string', 'max:250'],
            'contratista' => ['nullable', 'string', 'max:250'],
            'monto_contrato' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string'],
            'documento_contrato_url' => ['nullable', 'string', 'max:500'],
        ]);

        $contrato = Contrato::create($data);

        AuditoriaService::registrar(
            'contratos',
            $contrato->id,
            'CREATE',
            null,
            $contrato->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Contrato registrado correctamente.',
            'contrato' => $contrato,
        ], 201);
    }

    public function actualizarContrato(Request $request, Contrato $contrato)
    {
        $antes = $contrato->toArray();

        $data = $request->validate([
            'numero_contrato' => [
                'required',
                'string',
                'max:100',
                Rule::unique('contratos', 'numero_contrato')->ignore($contrato->id),
            ],
            'objeto_contrato' => ['nullable', 'string'],
            'fecha_contrato' => ['nullable', 'date'],
            'entidad_contratante' => ['nullable', 'string', 'max:250'],
            'contratista' => ['nullable', 'string', 'max:250'],
            'monto_contrato' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string'],
            'documento_contrato_url' => ['nullable', 'string', 'max:500'],
        ]);

        $contrato->update($data);

        AuditoriaService::registrar(
            'contratos',
            $contrato->id,
            'UPDATE',
            $antes,
            $contrato->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Contrato actualizado correctamente.',
            'contrato' => $contrato->fresh(),
        ]);
    }
}