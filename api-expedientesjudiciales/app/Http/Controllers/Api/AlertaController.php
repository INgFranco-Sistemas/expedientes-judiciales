<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Expediente;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlertaController extends Controller
{
    public function index(Request $request)
    {
        $query = Alerta::with([
            'expediente',
            'tipoAlerta',
            'usuarioDestino',
        ])->orderBy('fecha_alerta', 'asc');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('leido')) {
            $query->where('leido', filter_var($request->leido, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('usuario_destino_id')) {
            $query->where('usuario_destino_id', $request->usuario_destino_id);
        }

        if ($request->filled('expediente_id')) {
            $query->where('expediente_id', $request->expediente_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_alerta', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_alerta', '<=', $request->fecha_hasta);
        }

        return response()->json(
            $query->paginate($request->get('per_page', 10))
        );
    }

    public function misAlertas(Request $request)
    {
        $query = Alerta::with([
            'expediente',
            'tipoAlerta',
        ])
            ->where(function ($q) use ($request) {
                $q->where('usuario_destino_id', $request->user()->id)
                    ->orWhereNull('usuario_destino_id');
            })
            ->orderBy('fecha_alerta', 'asc');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        } else {
            $query->where('estado', 'ACTIVA');
        }

        if ($request->filled('leido')) {
            $query->where('leido', filter_var($request->leido, FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json(
            $query->paginate($request->get('per_page', 10))
        );
    }

    public function porExpediente(Expediente $expediente)
    {
        return response()->json([
            'expediente_id' => $expediente->id,
            'alertas' => $expediente->alertas()
                ->with(['tipoAlerta', 'usuarioDestino'])
                ->orderBy('fecha_alerta', 'asc')
                ->paginate(10),
        ]);
    }

    public function store(Request $request, Expediente $expediente)
    {
        $data = $request->validate([
            'tipo_alerta_id' => ['required', 'exists:tipos_alerta,id'],
            'fecha_alerta' => ['required', 'date'],
            'mensaje' => ['required', 'string'],
            'usuario_destino_id' => ['nullable', 'exists:usuarios,id'],
            'leido' => ['nullable', 'boolean'],
            'estado' => ['required', Rule::in(['ACTIVA', 'ATENDIDA', 'ANULADA'])],
        ]);

        $data['expediente_id'] = $expediente->id;
        $data['leido'] = $data['leido'] ?? false;

        $alerta = Alerta::create($data);

        AuditoriaService::registrar(
            'alertas',
            $alerta->id,
            'CREATE',
            null,
            $alerta->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Alerta registrada correctamente.',
            'alerta' => $alerta->load(['tipoAlerta', 'usuarioDestino']),
        ], 201);
    }

    public function show(Expediente $expediente, Alerta $alerta)
    {
        $this->validarAlertaPerteneceAlExpediente($expediente, $alerta);

        return response()->json([
            'alerta' => $alerta->load(['tipoAlerta', 'usuarioDestino', 'expediente']),
        ]);
    }

    public function marcarLeida(Request $request, Expediente $expediente, Alerta $alerta)
    {
        $this->validarAlertaPerteneceAlExpediente($expediente, $alerta);

        $antes = $alerta->toArray();

        $alerta->update([
            'leido' => true,
        ]);

        AuditoriaService::registrar(
            'alertas',
            $alerta->id,
            'UPDATE',
            $antes,
            $alerta->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Alerta marcada como leída.',
            'alerta' => $alerta->fresh()->load(['tipoAlerta', 'usuarioDestino']),
        ]);
    }

    public function atender(Request $request, Expediente $expediente, Alerta $alerta)
    {
        $this->validarAlertaPerteneceAlExpediente($expediente, $alerta);

        $antes = $alerta->toArray();

        $alerta->update([
            'estado' => 'ATENDIDA',
            'leido' => true,
        ]);

        AuditoriaService::registrar(
            'alertas',
            $alerta->id,
            'UPDATE',
            $antes,
            $alerta->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Alerta atendida correctamente.',
            'alerta' => $alerta->fresh()->load(['tipoAlerta', 'usuarioDestino']),
        ]);
    }

    public function anular(Request $request, Expediente $expediente, Alerta $alerta)
    {
        $this->validarAlertaPerteneceAlExpediente($expediente, $alerta);

        $data = $request->validate([
            'motivo' => ['required', 'string', 'min:5'],
        ]);

        $antes = $alerta->toArray();

        $alerta->update([
            'estado' => 'ANULADA',
            'mensaje' => trim($alerta->mensaje . "\n\nAnulación: " . $data['motivo']),
        ]);

        AuditoriaService::registrar(
            'alertas',
            $alerta->id,
            'UPDATE',
            $antes,
            $alerta->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Alerta anulada correctamente.',
            'alerta' => $alerta->fresh()->load(['tipoAlerta', 'usuarioDestino']),
        ]);
    }

    public function destroy(Request $request, Expediente $expediente, Alerta $alerta)
    {
        $this->validarAlertaPerteneceAlExpediente($expediente, $alerta);

        $antes = $alerta->toArray();

        $alerta->delete();

        AuditoriaService::registrar(
            'alertas',
            $alerta->id,
            'DELETE_LOGICO',
            $antes,
            null,
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Alerta eliminada correctamente.',
        ]);
    }

    private function validarAlertaPerteneceAlExpediente(Expediente $expediente, Alerta $alerta): void
    {
        if ($alerta->expediente_id !== $expediente->id) {
            abort(404, 'La alerta indicada no pertenece al expediente seleccionado.');
        }
    }
}