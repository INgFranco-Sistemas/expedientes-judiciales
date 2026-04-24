<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::with(['perfil', 'especialidad'])
            ->orderBy('id', 'desc');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('username', 'ILIKE', "%{$buscar}%")
                    ->orWhere('nombres', 'ILIKE', "%{$buscar}%")
                    ->orWhere('apellidos', 'ILIKE', "%{$buscar}%")
                    ->orWhere('nombre_completo', 'ILIKE', "%{$buscar}%")
                    ->orWhere('dni', 'ILIKE', "%{$buscar}%")
                    ->orWhere('correo_institucional', 'ILIKE', "%{$buscar}%");
            });
        }

        if ($request->filled('estado_usuario')) {
            $query->where('estado_usuario', $request->estado_usuario);
        }

        if ($request->filled('perfil_id')) {
            $query->where('perfil_id', $request->perfil_id);
        }

        return response()->json(
            $query->paginate($request->get('per_page', 10))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:usuarios,username'],
            'password' => ['required', 'string', 'min:8'],
            'nombres' => ['required', 'string', 'max:150'],
            'apellidos' => ['required', 'string', 'max:150'],
            'dni' => ['required', 'string', 'size:8', 'unique:usuarios,dni'],
            'correo_institucional' => ['nullable', 'email', 'max:150', 'unique:usuarios,correo_institucional'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'especialidad_id' => ['nullable', 'exists:especialidades,id'],
            'perfil_id' => ['required', 'exists:perfiles,id'],
            'estado_usuario' => ['required', Rule::in(['ACTIVO', 'INACTIVO', 'BLOQUEADO', 'CESADO'])],
            'fecha_inicio_asignacion' => ['nullable', 'date'],
            'fecha_termino_asignacion' => ['nullable', 'date', 'after_or_equal:fecha_inicio_asignacion'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['nombre_completo'] = trim($data['nombres'] . ' ' . $data['apellidos']);

        $usuario = Usuario::create($data);

        AuditoriaService::registrar(
            'usuarios',
            $usuario->id,
            'CREATE',
            null,
            $usuario->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'usuario' => $usuario->load(['perfil', 'especialidad']),
        ], 201);
    }

    public function show(Usuario $usuario)
    {
        return response()->json([
            'usuario' => $usuario->load(['perfil', 'especialidad']),
        ]);
    }

    public function update(Request $request, Usuario $usuario)
    {
        $antes = $usuario->toArray();

        $data = $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('usuarios', 'username')->ignore($usuario->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'nombres' => ['required', 'string', 'max:150'],
            'apellidos' => ['required', 'string', 'max:150'],
            'dni' => [
                'required',
                'string',
                'size:8',
                Rule::unique('usuarios', 'dni')->ignore($usuario->id),
            ],
            'correo_institucional' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('usuarios', 'correo_institucional')->ignore($usuario->id),
            ],
            'telefono' => ['nullable', 'string', 'max:20'],
            'especialidad_id' => ['nullable', 'exists:especialidades,id'],
            'perfil_id' => ['required', 'exists:perfiles,id'],
            'estado_usuario' => ['required', Rule::in(['ACTIVO', 'INACTIVO', 'BLOQUEADO', 'CESADO'])],
            'fecha_inicio_asignacion' => ['nullable', 'date'],
            'fecha_termino_asignacion' => ['nullable', 'date', 'after_or_equal:fecha_inicio_asignacion'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['nombre_completo'] = trim($data['nombres'] . ' ' . $data['apellidos']);

        $usuario->update($data);

        AuditoriaService::registrar(
            'usuarios',
            $usuario->id,
            'UPDATE',
            $antes,
            $usuario->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'usuario' => $usuario->fresh()->load(['perfil', 'especialidad']),
        ]);
    }

    public function cambiarEstado(Request $request, Usuario $usuario)
    {
        $data = $request->validate([
            'estado_usuario' => ['required', Rule::in(['ACTIVO', 'INACTIVO', 'BLOQUEADO', 'CESADO'])],
        ]);

        $antes = $usuario->toArray();

        $usuario->update([
            'estado_usuario' => $data['estado_usuario'],
        ]);

        AuditoriaService::registrar(
            'usuarios',
            $usuario->id,
            'UPDATE',
            $antes,
            $usuario->fresh()->toArray(),
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Estado del usuario actualizado correctamente.',
            'usuario' => $usuario->fresh()->load(['perfil', 'especialidad']),
        ]);
    }

    public function destroy(Request $request, Usuario $usuario)
    {
        $antes = $usuario->toArray();

        $usuario->delete();

        AuditoriaService::registrar(
            'usuarios',
            $usuario->id,
            'DELETE_LOGICO',
            $antes,
            null,
            $request->user()->id,
            $request
        );

        return response()->json([
            'message' => 'Usuario eliminado correctamente.',
        ]);
    }
}