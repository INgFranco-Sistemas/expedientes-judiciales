<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actuacion;
use App\Models\Documento;
use App\Models\Expediente;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentoController extends Controller
{
    public function index(Expediente $expediente)
    {
        return response()->json([
            'expediente_id' => $expediente->id,
            'documentos' => $expediente->documentos()
                ->with(['tipoDocumento', 'subidoPor', 'actuacion'])
                ->orderBy('fecha_subida', 'desc')
                ->paginate(10),
        ]);
    }

    public function store(Request $request, Expediente $expediente)
    {
        $data = $request->validate([
            'tipo_documento_id' => ['required', 'exists:tipos_documento,id'],
            'actuacion_id' => ['nullable', 'exists:actuaciones,id'],
            'archivo' => ['required', 'file', 'max:51200'],
            'observacion' => ['nullable', 'string'],
            'version' => ['nullable', 'integer', 'min:1'],
        ]);

        if (!empty($data['actuacion_id'])) {
            $actuacion = Actuacion::find($data['actuacion_id']);

            if (!$actuacion || $actuacion->expediente_id !== $expediente->id) {
                return response()->json([
                    'message' => 'La actuación indicada no pertenece al expediente.',
                ], 422);
            }
        }

        $archivo = $request->file('archivo');

        $nombreOriginal = $archivo->getClientOriginalName();
        $extension = strtolower($archivo->getClientOriginalExtension());
        $mimeType = $archivo->getMimeType();
        $pesoBytes = $archivo->getSize();

        $codigoExpediente = Str::slug($expediente->codigo_unico_interno);
        $anio = $expediente->anio_expediente ?? now()->year;

        $nombreGuardado = $codigoExpediente . '_' . now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $extension;

        $rutaDirectorio = "expedientes/{$anio}/{$codigoExpediente}/documentos";
        $rutaArchivo = $archivo->storeAs($rutaDirectorio, $nombreGuardado, 'local');

        $contenido = Storage::disk('local')->get($rutaArchivo);
        $hashArchivo = hash('sha256', $contenido);

        $documento = Documento::create([
            'expediente_id' => $expediente->id,
            'actuacion_id' => $data['actuacion_id'] ?? null,
            'tipo_documento_id' => $data['tipo_documento_id'],
            'nombre_original' => $nombreOriginal,
            'nombre_guardado' => $nombreGuardado,
            'ruta_archivo' => $rutaArchivo,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'peso_bytes' => $pesoBytes,
            'hash_archivo' => $hashArchivo,
            'version' => $data['version'] ?? 1,
            'observacion' => $data['observacion'] ?? null,
            'subido_por' => $request->user()->id,
            'fecha_subida' => now(),
            'estado' => 'ACTIVO',
        ]);

        AuditoriaService::registrar(
            'documentos',
            $documento->id,
            'SUBIDA_DOCUMENTO',
            null,
            $documento->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Documento subido correctamente.',
            'documento' => $documento->load(['tipoDocumento', 'subidoPor', 'actuacion']),
        ], 201);
    }

    public function show(Expediente $expediente, Documento $documento)
    {
        $this->validarDocumentoPerteneceAlExpediente($expediente, $documento);

        return response()->json([
            'documento' => $documento->load(['tipoDocumento', 'subidoPor', 'actuacion']),
        ]);
    }

    public function descargar(Request $request, Expediente $expediente, Documento $documento)
    {
        $this->validarDocumentoPerteneceAlExpediente($expediente, $documento);

        if ($documento->estado !== 'ACTIVO') {
            return response()->json([
                'message' => 'El documento no se encuentra activo.',
            ], 422);
        }

        if (!Storage::disk('local')->exists($documento->ruta_archivo)) {
            return response()->json([
                'message' => 'El archivo físico no existe en el almacenamiento.',
            ], 404);
        }

        AuditoriaService::registrar(
            'documentos',
            $documento->id,
            'DESCARGA_DOCUMENTO',
            null,
            [
                'nombre_original' => $documento->nombre_original,
                'ruta_archivo' => $documento->ruta_archivo,
            ],
            $request->user()->id,
            $request
        );

        return Storage::disk('local')->download(
            $documento->ruta_archivo,
            $documento->nombre_original
        );
    }

    public function anular(Request $request, Expediente $expediente, Documento $documento)
    {
        $this->validarDocumentoPerteneceAlExpediente($expediente, $documento);

        $data = $request->validate([
            'motivo' => ['required', 'string', 'min:5'],
        ]);

        $antes = $documento->toArray();

        $documento->update([
            'estado' => 'ANULADO',
            'observacion' => trim(($documento->observacion ?? '') . "\n\nAnulación: " . $data['motivo']),
        ]);

        AuditoriaService::registrar(
            'documentos',
            $documento->id,
            'UPDATE',
            $antes,
            $documento->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Documento anulado correctamente.',
            'documento' => $documento->fresh(),
        ]);
    }

    public function destroy(Request $request, Expediente $expediente, Documento $documento)
    {
        $this->validarDocumentoPerteneceAlExpediente($expediente, $documento);

        $antes = $documento->toArray();

        $documento->delete();

        AuditoriaService::registrar(
            'documentos',
            $documento->id,
            'DELETE_LOGICO',
            $antes,
            null,
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Documento eliminado lógicamente correctamente.',
        ]);
    }

    private function validarDocumentoPerteneceAlExpediente(Expediente $expediente, Documento $documento): void
    {
        if ($documento->expediente_id !== $expediente->id) {
            abort(404, 'El documento indicado no pertenece al expediente seleccionado.');
        }
    }
}