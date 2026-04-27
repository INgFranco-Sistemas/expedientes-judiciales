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

/*
|--------------------------------------------------------------------------
| AUTENTICACIÓN
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::prefix('dashboard')
        ->middleware('permiso:dashboard.ver')
        ->group(function () {
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

    /*
    |--------------------------------------------------------------------------
    | CATÁLOGOS
    |--------------------------------------------------------------------------
    */

    Route::prefix('catalogos')
        ->middleware('permiso:catalogos.ver')
        ->group(function () {
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

    /*
    |--------------------------------------------------------------------------
    | USUARIOS
    |--------------------------------------------------------------------------
    */

    Route::get('/usuarios', [UsuarioController::class, 'index'])->middleware('permiso:usuarios.ver');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->middleware('permiso:usuarios.crear');
    Route::get('/usuarios/{usuario}', [UsuarioController::class, 'show'])->middleware('permiso:usuarios.ver');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->middleware('permiso:usuarios.editar');
    Route::patch('/usuarios/{usuario}/estado', [UsuarioController::class, 'cambiarEstado'])->middleware('permiso:usuarios.editar');
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->middleware('permiso:usuarios.eliminar');

    /*
    |--------------------------------------------------------------------------
    | EXPEDIENTES
    |--------------------------------------------------------------------------
    */

    Route::get('/expedientes', [ExpedienteController::class, 'index'])->middleware('permiso:expedientes.ver');
    Route::post('/expedientes', [ExpedienteController::class, 'store'])->middleware('permiso:expedientes.crear');
    Route::get('/expedientes/{expediente}', [ExpedienteController::class, 'show'])->middleware('permiso:expedientes.ver');
    Route::put('/expedientes/{expediente}', [ExpedienteController::class, 'update'])->middleware('permiso:expedientes.editar');
    Route::patch('/expedientes/{expediente}/cerrar', [ExpedienteController::class, 'cerrar'])->middleware('permiso:expedientes.cerrar');
    Route::patch('/expedientes/{expediente}/reabrir', [ExpedienteController::class, 'reabrir'])->middleware('permiso:expedientes.reabrir');
    Route::patch('/expedientes/{expediente}/importante', [ExpedienteController::class, 'marcarImportante'])->middleware('permiso:expedientes.editar');
    Route::delete('/expedientes/{expediente}', [ExpedienteController::class, 'destroy'])->middleware('permiso:expedientes.eliminar');

    /*
    |--------------------------------------------------------------------------
    | PARTES DEL EXPEDIENTE
    |--------------------------------------------------------------------------
    */

    Route::get('/expedientes/{expediente}/partes', [ExpedienteParteController::class, 'index'])->middleware('permiso:partes.ver');
    Route::post('/expedientes/{expediente}/partes', [ExpedienteParteController::class, 'store'])->middleware('permiso:partes.crear');
    Route::get('/expedientes/{expediente}/partes/{parte}', [ExpedienteParteController::class, 'show'])->middleware('permiso:partes.ver');
    Route::put('/expedientes/{expediente}/partes/{parte}', [ExpedienteParteController::class, 'update'])->middleware('permiso:partes.editar');
    Route::delete('/expedientes/{expediente}/partes/{parte}', [ExpedienteParteController::class, 'destroy'])->middleware('permiso:partes.eliminar');

    /*
    |--------------------------------------------------------------------------
    | DETALLES POR TIPO DE EXPEDIENTE
    |--------------------------------------------------------------------------
    */

    Route::get('/expedientes/{expediente}/detalle-judicial', [ExpedienteDetalleController::class, 'verJudicial'])->middleware('permiso:detalles.ver');
    Route::post('/expedientes/{expediente}/detalle-judicial', [ExpedienteDetalleController::class, 'guardarJudicial'])->middleware('permiso:detalles.editar');
    Route::get('/expedientes/{expediente}/detalle-penal', [ExpedienteDetalleController::class, 'verPenal'])->middleware('permiso:detalles.ver');
    Route::post('/expedientes/{expediente}/detalle-penal', [ExpedienteDetalleController::class, 'guardarPenal'])->middleware('permiso:detalles.editar');
    Route::get('/expedientes/{expediente}/detalle-masc', [ExpedienteDetalleController::class, 'verMasc'])->middleware('permiso:detalles.ver');
    Route::post('/expedientes/{expediente}/detalle-masc', [ExpedienteDetalleController::class, 'guardarMasc'])->middleware('permiso:detalles.editar');

    /*
    |--------------------------------------------------------------------------
    | CONTRATOS
    |--------------------------------------------------------------------------
    */

    Route::get('/contratos', [ExpedienteDetalleController::class, 'listarContratos'])->middleware('permiso:detalles.ver');
    Route::post('/contratos', [ExpedienteDetalleController::class, 'guardarContrato'])->middleware('permiso:detalles.editar');
    Route::put('/contratos/{contrato}', [ExpedienteDetalleController::class, 'actualizarContrato'])->middleware('permiso:detalles.editar');

    /*
    |--------------------------------------------------------------------------
    | ACTUACIONES
    |--------------------------------------------------------------------------
    */

    Route::get('/expedientes/{expediente}/actuaciones', [ActuacionController::class, 'index'])->middleware('permiso:actuaciones.ver');
    Route::post('/expedientes/{expediente}/actuaciones', [ActuacionController::class, 'store'])->middleware('permiso:actuaciones.crear');
    Route::get('/expedientes/{expediente}/actuaciones/{actuacion}', [ActuacionController::class, 'show'])->middleware('permiso:actuaciones.ver');
    Route::put('/expedientes/{expediente}/actuaciones/{actuacion}', [ActuacionController::class, 'update'])->middleware('permiso:actuaciones.editar');
    Route::delete('/expedientes/{expediente}/actuaciones/{actuacion}', [ActuacionController::class, 'destroy'])->middleware('permiso:actuaciones.eliminar');

    /*
    |--------------------------------------------------------------------------
    | DOCUMENTOS
    |--------------------------------------------------------------------------
    */

    Route::get('/expedientes/{expediente}/documentos', [DocumentoController::class, 'index'])->middleware('permiso:documentos.ver');
    Route::post('/expedientes/{expediente}/documentos', [DocumentoController::class, 'store'])->middleware('permiso:documentos.subir');
    Route::get('/expedientes/{expediente}/documentos/{documento}', [DocumentoController::class, 'show'])->middleware('permiso:documentos.ver');
    Route::get('/expedientes/{expediente}/documentos/{documento}/descargar', [DocumentoController::class, 'descargar'])->middleware('permiso:documentos.descargar');
    Route::patch('/expedientes/{expediente}/documentos/{documento}/anular', [DocumentoController::class, 'anular'])->middleware('permiso:documentos.anular');
    Route::delete('/expedientes/{expediente}/documentos/{documento}', [DocumentoController::class, 'destroy'])->middleware('permiso:documentos.eliminar');

        /*
    |--------------------------------------------------------------------------
    | ALERTAS
    |--------------------------------------------------------------------------
    */

    Route::get('/alertas', [AlertaController::class, 'index'])->middleware('permiso:alertas.ver');
    Route::get('/mis-alertas', [AlertaController::class, 'misAlertas'])->middleware('permiso:alertas.ver');
    Route::get('/expedientes/{expediente}/alertas', [AlertaController::class, 'porExpediente'])->middleware('permiso:alertas.ver');
    Route::post('/expedientes/{expediente}/alertas', [AlertaController::class, 'store'])->middleware('permiso:alertas.crear');
    Route::get('/expedientes/{expediente}/alertas/{alerta}', [AlertaController::class, 'show'])->middleware('permiso:alertas.ver');
    Route::patch('/expedientes/{expediente}/alertas/{alerta}/leer', [AlertaController::class, 'marcarLeida'])->middleware('permiso:alertas.editar');
    Route::patch('/expedientes/{expediente}/alertas/{alerta}/atender', [AlertaController::class, 'atender'])->middleware('permiso:alertas.editar');
    Route::patch('/expedientes/{expediente}/alertas/{alerta}/anular', [AlertaController::class, 'anular'])->middleware('permiso:alertas.editar');
    Route::delete('/expedientes/{expediente}/alertas/{alerta}', [AlertaController::class, 'destroy'])->middleware('permiso:alertas.eliminar');

    /*
    |--------------------------------------------------------------------------
    | AUDITORÍA
    |--------------------------------------------------------------------------
    */

    Route::get('/auditoria', [AuditoriaController::class, 'index'])
        ->middleware('permiso:auditoria.ver');

    Route::get('/auditoria/{auditoria}', [AuditoriaController::class, 'show'])
        ->middleware('permiso:auditoria.ver');
});