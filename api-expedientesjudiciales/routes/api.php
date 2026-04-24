<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ExpedienteController;
use App\Http\Controllers\Api\ExpedienteParteController;
use App\Http\Controllers\Api\ExpedienteDetalleController;
use App\Http\Controllers\Api\ActuacionController;
use App\Http\Controllers\Api\DocumentoController;
use App\Http\Controllers\Api\AlertaController;
use App\Http\Controllers\Api\AuditoriaController;
use App\Http\Controllers\Api\DashboardController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->prefix('catalogos')->group(function () {
    Route::get('/perfiles', [CatalogoController::class, 'perfiles']);
    Route::get('/especialidades', [CatalogoController::class, 'especialidades']);
    Route::get('/tipos-expediente', [CatalogoController::class, 'tiposExpediente']);
    Route::get('/estados-expediente', [CatalogoController::class, 'estadosExpediente']);
    Route::get('/distritos-judiciales', [CatalogoController::class, 'distritosJudiciales']);
    Route::get('/dependencias', [CatalogoController::class, 'dependencias']);
    Route::get('/instancias', [CatalogoController::class, 'instancias']);
    Route::get('/materias', [CatalogoController::class, 'materias']);
    Route::get('/etapas', [CatalogoController::class, 'etapas']);
    Route::get('/tipos-actuacion', [CatalogoController::class, 'tiposActuacion']);
    Route::get('/tipos-documento', [CatalogoController::class, 'tiposDocumento']);
    Route::get('/prioridades', [CatalogoController::class, 'prioridades']);
    Route::get('/motivos-cierre', [CatalogoController::class, 'motivosCierre']);
    Route::get('/tipos-alerta', [CatalogoController::class, 'tiposAlerta']);

    Route::get('/todos', [CatalogoController::class, 'todos']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::post('/usuarios', [UsuarioController::class, 'store']);
    Route::get('/usuarios/{usuario}', [UsuarioController::class, 'show']);
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update']);
    Route::patch('/usuarios/{usuario}/estado', [UsuarioController::class, 'cambiarEstado']);
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/expedientes', [ExpedienteController::class, 'index']);
    Route::post('/expedientes', [ExpedienteController::class, 'store']);
    Route::get('/expedientes/{expediente}', [ExpedienteController::class, 'show']);
    Route::put('/expedientes/{expediente}', [ExpedienteController::class, 'update']);
    Route::patch('/expedientes/{expediente}/cerrar', [ExpedienteController::class, 'cerrar']);
    Route::patch('/expedientes/{expediente}/reabrir', [ExpedienteController::class, 'reabrir']);
    Route::patch('/expedientes/{expediente}/importante', [ExpedienteController::class, 'marcarImportante']);
    Route::delete('/expedientes/{expediente}', [ExpedienteController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/expedientes/{expediente}/partes', [ExpedienteParteController::class, 'index']);
    Route::post('/expedientes/{expediente}/partes', [ExpedienteParteController::class, 'store']);
    Route::get('/expedientes/{expediente}/partes/{parte}', [ExpedienteParteController::class, 'show']);
    Route::put('/expedientes/{expediente}/partes/{parte}', [ExpedienteParteController::class, 'update']);
    Route::delete('/expedientes/{expediente}/partes/{parte}', [ExpedienteParteController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/expedientes/{expediente}/detalle-judicial', [ExpedienteDetalleController::class, 'verJudicial']);
    Route::post('/expedientes/{expediente}/detalle-judicial', [ExpedienteDetalleController::class, 'guardarJudicial']);

    Route::get('/expedientes/{expediente}/detalle-penal', [ExpedienteDetalleController::class, 'verPenal']);
    Route::post('/expedientes/{expediente}/detalle-penal', [ExpedienteDetalleController::class, 'guardarPenal']);

    Route::get('/expedientes/{expediente}/detalle-masc', [ExpedienteDetalleController::class, 'verMasc']);
    Route::post('/expedientes/{expediente}/detalle-masc', [ExpedienteDetalleController::class, 'guardarMasc']);

    Route::get('/contratos', [ExpedienteDetalleController::class, 'listarContratos']);
    Route::post('/contratos', [ExpedienteDetalleController::class, 'guardarContrato']);
    Route::put('/contratos/{contrato}', [ExpedienteDetalleController::class, 'actualizarContrato']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/expedientes/{expediente}/actuaciones', [ActuacionController::class, 'index']);
    Route::post('/expedientes/{expediente}/actuaciones', [ActuacionController::class, 'store']);
    Route::get('/expedientes/{expediente}/actuaciones/{actuacion}', [ActuacionController::class, 'show']);
    Route::put('/expedientes/{expediente}/actuaciones/{actuacion}', [ActuacionController::class, 'update']);
    Route::delete('/expedientes/{expediente}/actuaciones/{actuacion}', [ActuacionController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/expedientes/{expediente}/documentos', [DocumentoController::class, 'index']);
    Route::post('/expedientes/{expediente}/documentos', [DocumentoController::class, 'store']);
    Route::get('/expedientes/{expediente}/documentos/{documento}', [DocumentoController::class, 'show']);
    Route::get('/expedientes/{expediente}/documentos/{documento}/descargar', [DocumentoController::class, 'descargar']);
    Route::patch('/expedientes/{expediente}/documentos/{documento}/anular', [DocumentoController::class, 'anular']);
    Route::delete('/expedientes/{expediente}/documentos/{documento}', [DocumentoController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/alertas', [AlertaController::class, 'index']);
    Route::get('/mis-alertas', [AlertaController::class, 'misAlertas']);

    Route::get('/expedientes/{expediente}/alertas', [AlertaController::class, 'porExpediente']);
    Route::post('/expedientes/{expediente}/alertas', [AlertaController::class, 'store']);
    Route::get('/expedientes/{expediente}/alertas/{alerta}', [AlertaController::class, 'show']);
    Route::patch('/expedientes/{expediente}/alertas/{alerta}/leer', [AlertaController::class, 'marcarLeida']);
    Route::patch('/expedientes/{expediente}/alertas/{alerta}/atender', [AlertaController::class, 'atender']);
    Route::patch('/expedientes/{expediente}/alertas/{alerta}/anular', [AlertaController::class, 'anular']);
    Route::delete('/expedientes/{expediente}/alertas/{alerta}', [AlertaController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auditoria', [AuditoriaController::class, 'index']);
    Route::get('/auditoria/{auditoria}', [AuditoriaController::class, 'show']);
});

Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('/resumen', [DashboardController::class, 'resumen']);
    Route::get('/expedientes-por-tipo', [DashboardController::class, 'expedientesPorTipo']);
    Route::get('/expedientes-por-estado', [DashboardController::class, 'expedientesPorEstado']);
    Route::get('/expedientes-por-encargado', [DashboardController::class, 'expedientesPorEncargado']);
    Route::get('/proximos-vencimientos', [DashboardController::class, 'proximosVencimientos']);
    Route::get('/alertas-activas', [DashboardController::class, 'alertasActivas']);
    Route::get('/ultimos-expedientes', [DashboardController::class, 'ultimosExpedientes']);
    Route::get('/ultimas-actuaciones', [DashboardController::class, 'ultimasActuaciones']);
    Route::get('/completo', [DashboardController::class, 'completo']);
});