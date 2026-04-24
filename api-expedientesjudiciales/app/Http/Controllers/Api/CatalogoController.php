<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Perfil;
use App\Models\Especialidad;
use App\Models\TipoExpediente;
use App\Models\EstadoExpediente;
use App\Models\DistritoJudicial;
use App\Models\Dependencia;
use App\Models\Instancia;
use App\Models\Materia;
use App\Models\Etapa;
use App\Models\TipoActuacion;
use App\Models\TipoDocumento;
use App\Models\Prioridad;
use App\Models\MotivoCierre;
use App\Models\TipoAlerta;

class CatalogoController extends Controller
{
    public function perfiles()
    {
        return response()->json(
            Perfil::where('estado', true)->orderBy('nombre')->get()
        );
    }

    public function especialidades()
    {
        return response()->json(
            Especialidad::where('estado', true)->orderBy('nombre')->get()
        );
    }

    public function tiposExpediente()
    {
        return response()->json(
            TipoExpediente::where('estado', true)->orderBy('nombre')->get()
        );
    }

    public function estadosExpediente()
    {
        return response()->json(
            EstadoExpediente::where('estado', true)
                ->orderBy('orden_visual')
                ->orderBy('nombre')
                ->get()
        );
    }

    public function distritosJudiciales()
    {
        return response()->json(
            DistritoJudicial::where('estado', true)->orderBy('nombre')->get()
        );
    }

    public function dependencias()
    {
        return response()->json(
            Dependencia::with('distritoJudicial')
                ->where('estado', true)
                ->orderBy('nombre')
                ->get()
        );
    }

    public function instancias()
    {
        return response()->json(
            Instancia::where('estado', true)->orderBy('nombre')->get()
        );
    }

    public function materias()
    {
        return response()->json(
            Materia::where('estado', true)->orderBy('nombre')->get()
        );
    }

    public function etapas()
    {
        return response()->json(
            Etapa::where('estado', true)->orderBy('nombre')->get()
        );
    }

    public function tiposActuacion()
    {
        return response()->json(
            TipoActuacion::where('estado', true)->orderBy('nombre')->get()
        );
    }

    public function tiposDocumento()
    {
        return response()->json(
            TipoDocumento::where('estado', true)->orderBy('nombre')->get()
        );
    }

    public function prioridades()
    {
        return response()->json(
            Prioridad::where('estado', true)
                ->orderBy('nivel')
                ->get()
        );
    }

    public function motivosCierre()
    {
        return response()->json(
            MotivoCierre::where('estado', true)->orderBy('nombre')->get()
        );
    }

    public function tiposAlerta()
    {
        return response()->json(
            TipoAlerta::where('estado', true)->orderBy('nombre')->get()
        );
    }

    public function todos()
    {
        return response()->json([
            'perfiles' => Perfil::where('estado', true)->orderBy('nombre')->get(),
            'especialidades' => Especialidad::where('estado', true)->orderBy('nombre')->get(),
            'tipos_expediente' => TipoExpediente::where('estado', true)->orderBy('nombre')->get(),
            'estados_expediente' => EstadoExpediente::where('estado', true)->orderBy('orden_visual')->get(),
            'distritos_judiciales' => DistritoJudicial::where('estado', true)->orderBy('nombre')->get(),
            'dependencias' => Dependencia::where('estado', true)->orderBy('nombre')->get(),
            'instancias' => Instancia::where('estado', true)->orderBy('nombre')->get(),
            'materias' => Materia::where('estado', true)->orderBy('nombre')->get(),
            'etapas' => Etapa::where('estado', true)->orderBy('nombre')->get(),
            'tipos_actuacion' => TipoActuacion::where('estado', true)->orderBy('nombre')->get(),
            'tipos_documento' => TipoDocumento::where('estado', true)->orderBy('nombre')->get(),
            'prioridades' => Prioridad::where('estado', true)->orderBy('nivel')->get(),
            'motivos_cierre' => MotivoCierre::where('estado', true)->orderBy('nombre')->get(),
            'tipos_alerta' => TipoAlerta::where('estado', true)->orderBy('nombre')->get(),
        ]);
    }
}