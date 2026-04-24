<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expediente;
use App\Models\ExpedienteParte;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpedienteParteController extends Controller
{
    public function index(Expediente $expediente)
    {
        return response()->json([
            'expediente_id' => $expediente->id,
            'partes' => $expediente->partes()
                ->orderBy('tipo_parte')
                ->orderBy('nombres_razon_social')
                ->get(),
        ]);
    }

    public function store(Request $request, Expediente $expediente)
    {
        $data = $request->validate([
            'tipo_parte' => [
                'required',
                Rule::in([
                    'DEMANDANTE',
                    'DEMANDADO',
                    'DENUNCIANTE',
                    'DENUNCIADO',
                    'SOLICITANTE',
                    'CONTRATISTA',
                    'ENTIDAD',
                    'TERCERO',
                ]),
            ],
            'tipo_persona' => [
                'required',
                Rule::in(['NATURAL', 'JURIDICA']),
            ],
            'nombres_razon_social' => ['required', 'string', 'max:250'],
            'documento_identidad' => ['nullable', 'string', 'max:20'],
            'correo' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:250'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $data['expediente_id'] = $expediente->id;

        $parte = ExpedienteParte::create($data);

        AuditoriaService::registrar(
            'expediente_partes',
            $parte->id,
            'CREATE',
            null,
            $parte->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Parte del expediente registrada correctamente.',
            'parte' => $parte,
        ], 201);
    }

    public function show(Expediente $expediente, ExpedienteParte $parte)
    {
        $this->validarPartePerteneceAlExpediente($expediente, $parte);

        return response()->json([
            'parte' => $parte,
        ]);
    }

    public function update(Request $request, Expediente $expediente, ExpedienteParte $parte)
    {
        $this->validarPartePerteneceAlExpediente($expediente, $parte);

        $antes = $parte->toArray();

        $data = $request->validate([
            'tipo_parte' => [
                'required',
                Rule::in([
                    'DEMANDANTE',
                    'DEMANDADO',
                    'DENUNCIANTE',
                    'DENUNCIADO',
                    'SOLICITANTE',
                    'CONTRATISTA',
                    'ENTIDAD',
                    'TERCERO',
                ]),
            ],
            'tipo_persona' => [
                'required',
                Rule::in(['NATURAL', 'JURIDICA']),
            ],
            'nombres_razon_social' => ['required', 'string', 'max:250'],
            'documento_identidad' => ['nullable', 'string', 'max:20'],
            'correo' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:250'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $parte->update($data);

        AuditoriaService::registrar(
            'expediente_partes',
            $parte->id,
            'UPDATE',
            $antes,
            $parte->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Parte del expediente actualizada correctamente.',
            'parte' => $parte->fresh(),
        ]);
    }

    public function destroy(Request $request, Expediente $expediente, ExpedienteParte $parte)
    {
        $this->validarPartePerteneceAlExpediente($expediente, $parte);

        $antes = $parte->toArray();

        $parte->delete();

        AuditoriaService::registrar(
            'expediente_partes',
            $parte->id,
            'DELETE_LOGICO',
            $antes,
            null,
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Parte del expediente eliminada correctamente.',
        ]);
    }

    private function validarPartePerteneceAlExpediente(Expediente $expediente, ExpedienteParte $parte): void
    {
        if ($parte->expediente_id !== $expediente->id) {
            abort(404, 'La parte indicada no pertenece al expediente seleccionado.');
        }
    }
}