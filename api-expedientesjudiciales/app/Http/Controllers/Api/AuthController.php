<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $usuario = Usuario::with('perfil.permisos')
            ->where('username', $request->username)
            ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            if ($usuario) {
                $usuario->increment('intentos_fallidos');
            }

            throw ValidationException::withMessages([
                'username' => ['Las credenciales ingresadas no son correctas.'],
            ]);
        }

        if ($usuario->estado_usuario !== 'ACTIVO') {
            throw ValidationException::withMessages([
                'username' => ['El usuario no se encuentra activo.'],
            ]);
        }

        $usuario->update([
            'ultimo_acceso_at' => now(),
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
        ]);

        $token = $usuario->createToken('api-token')->plainTextToken;

        AuditoriaService::registrar(
            'usuarios',
            $usuario->id,
            'LOGIN',
            null,
            ['username' => $usuario->username],
            $usuario->id,
            $request
        );

        return response()->json([
            'message' => 'Inicio de sesión correcto.',
            'token' => $token,
            'usuario' => [
                'id' => $usuario->id,
                'username' => $usuario->username,
                'nombre_completo' => $usuario->nombre_completo,
                'perfil' => $usuario->perfil?->nombre,
                'estado_usuario' => $usuario->estado_usuario,
                'permisos' => $usuario->perfil?->permisos->pluck('codigo')->values(),
            ],
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'usuario' => $request->user()->load(['perfil.permisos', 'especialidad']),
        ]);
    }

    public function logout(Request $request)
    {
        $usuario = $request->user();

        AuditoriaService::registrar(
            'usuarios',
            $usuario->id,
            'LOGOUT',
            null,
            ['username' => $usuario->username],
            $usuario->id,
            $request
        );

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }
}