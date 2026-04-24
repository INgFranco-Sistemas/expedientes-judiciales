<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Actuacion;
use App\Models\Documento;
use App\Models\Expediente;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function resumen()
    {
        $totalExpedientes = Expediente::count();

        $expedientesActivos = Expediente::where('estado_registro', 'ACTIVO')->count();

        $expedientesCerrados = Expediente::where('estado_registro', 'CERRADO')->count();

        $expedientesArchivados = Expediente::where('estado_registro', 'ARCHIVADO')->count();

        $expedientesImportantes = Expediente::where('importante', true)->count();

        $alertasActivas = Alerta::where('estado', 'ACTIVA')->count();

        $documentosActivos = Documento::where('estado', 'ACTIVO')->count();

        $usuariosActivos = Usuario::where('estado_usuario', 'ACTIVO')->count();

        return response()->json([
            'total_expedientes' => $totalExpedientes,
            'expedientes_activos' => $expedientesActivos,
            'expedientes_cerrados' => $expedientesCerrados,
            'expedientes_archivados' => $expedientesArchivados,
            'expedientes_importantes' => $expedientesImportantes,
            'alertas_activas' => $alertasActivas,
            'documentos_activos' => $documentosActivos,
            'usuarios_activos' => $usuariosActivos,
        ]);
    }

    public function expedientesPorTipo()
    {
        $data = Expediente::query()
            ->join('tipos_expediente', 'tipos_expediente.id', '=', 'expedientes.tipo_expediente_id')
            ->select('tipos_expediente.nombre', DB::raw('COUNT(expedientes.id) as total'))
            ->groupBy('tipos_expediente.nombre')
            ->orderBy('tipos_expediente.nombre')
            ->get();

        return response()->json($data);
    }

    public function expedientesPorEstado()
    {
        $data = Expediente::query()
            ->join('estados_expediente', 'estados_expediente.id', '=', 'expedientes.estado_expediente_id')
            ->select('estados_expediente.nombre', 'estados_expediente.color_ui', DB::raw('COUNT(expedientes.id) as total'))
            ->groupBy('estados_expediente.nombre', 'estados_expediente.color_ui')
            ->orderBy('estados_expediente.nombre')
            ->get();

        return response()->json($data);
    }

    public function expedientesPorEncargado()
    {
        $data = Expediente::query()
            ->join('usuarios', 'usuarios.id', '=', 'expedientes.encargado_actual_id')
            ->select('usuarios.nombre_completo', DB::raw('COUNT(expedientes.id) as total'))
            ->groupBy('usuarios.nombre_completo')
            ->orderByDesc('total')
            ->get();

        return response()->json($data);
    }

    public function proximosVencimientos()
    {
        $data = Expediente::with([
                'tipoExpediente',
                'estadoExpediente',
                'encargadoActual',
                'prioridad',
            ])
            ->whereNotNull('fecha_proximo_vencimiento')
            ->whereDate('fecha_proximo_vencimiento', '>=', now()->toDateString())
            ->whereDate('fecha_proximo_vencimiento', '<=', now()->addDays(15)->toDateString())
            ->orderBy('fecha_proximo_vencimiento', 'asc')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    public function alertasActivas()
    {
        $data = Alerta::with([
                'expediente',
                'tipoAlerta',
                'usuarioDestino',
            ])
            ->where('estado', 'ACTIVA')
            ->orderBy('fecha_alerta', 'asc')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    public function ultimosExpedientes()
    {
        $data = Expediente::with([
                'tipoExpediente',
                'estadoExpediente',
                'encargadoActual',
                'prioridad',
            ])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    public function ultimasActuaciones()
    {
        $data = Actuacion::with([
                'expediente',
                'tipoActuacion',
                'usuario',
            ])
            ->orderBy('fecha_actuacion', 'desc')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    public function completo()
    {
        return response()->json([
            'resumen' => [
                'total_expedientes' => Expediente::count(),
                'expedientes_activos' => Expediente::where('estado_registro', 'ACTIVO')->count(),
                'expedientes_cerrados' => Expediente::where('estado_registro', 'CERRADO')->count(),
                'expedientes_archivados' => Expediente::where('estado_registro', 'ARCHIVADO')->count(),
                'expedientes_importantes' => Expediente::where('importante', true)->count(),
                'alertas_activas' => Alerta::where('estado', 'ACTIVA')->count(),
                'documentos_activos' => Documento::where('estado', 'ACTIVO')->count(),
                'usuarios_activos' => Usuario::where('estado_usuario', 'ACTIVO')->count(),
            ],
            'expedientes_por_tipo' => Expediente::query()
                ->join('tipos_expediente', 'tipos_expediente.id', '=', 'expedientes.tipo_expediente_id')
                ->select('tipos_expediente.nombre', DB::raw('COUNT(expedientes.id) as total'))
                ->groupBy('tipos_expediente.nombre')
                ->orderBy('tipos_expediente.nombre')
                ->get(),
            'expedientes_por_estado' => Expediente::query()
                ->join('estados_expediente', 'estados_expediente.id', '=', 'expedientes.estado_expediente_id')
                ->select('estados_expediente.nombre', 'estados_expediente.color_ui', DB::raw('COUNT(expedientes.id) as total'))
                ->groupBy('estados_expediente.nombre', 'estados_expediente.color_ui')
                ->orderBy('estados_expediente.nombre')
                ->get(),
            'proximos_vencimientos' => Expediente::with(['tipoExpediente', 'estadoExpediente', 'encargadoActual', 'prioridad'])
                ->whereNotNull('fecha_proximo_vencimiento')
                ->whereDate('fecha_proximo_vencimiento', '>=', now()->toDateString())
                ->whereDate('fecha_proximo_vencimiento', '<=', now()->addDays(15)->toDateString())
                ->orderBy('fecha_proximo_vencimiento', 'asc')
                ->limit(10)
                ->get(),
            'alertas_activas' => Alerta::with(['expediente', 'tipoAlerta', 'usuarioDestino'])
                ->where('estado', 'ACTIVA')
                ->orderBy('fecha_alerta', 'asc')
                ->limit(10)
                ->get(),
            'ultimos_expedientes' => Expediente::with(['tipoExpediente', 'estadoExpediente', 'encargadoActual', 'prioridad'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'ultimas_actuaciones' => Actuacion::with(['expediente', 'tipoActuacion', 'usuario'])
                ->orderBy('fecha_actuacion', 'desc')
                ->limit(10)
                ->get(),
        ]);
    }
}