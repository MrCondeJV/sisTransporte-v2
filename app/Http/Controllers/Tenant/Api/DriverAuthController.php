<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DriverAuthController extends Controller
{
    /**
     * Iniciar sesión como conductor y obtener token Bearer Sanctum.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'nullable|email',
            'document_number' => 'nullable|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $user = null;

        if ($request->filled('email')) {
            $user = User::where('email', $request->email)->first();
        } elseif ($request->filled('document_number')) {
            $employee = Employee::where('document_number', $request->document_number)->first();
            if ($employee && $employee->user_id) {
                $user = User::find($employee->user_id);
            }
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        if (isset($user->is_active) && ! $user->is_active) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'La cuenta de usuario se encuentra inactiva.',
            ], 403);
        }

        $employee = Employee::where('user_id', $user->id)->first();

        $deviceName = $request->device_name ?? 'driver_mobile_app';
        $token = $user->createToken($deviceName, ['driver'])->plainTextToken;

        return response()->json([
            'message' => 'Autenticación exitosa',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
            ],
            'driver' => $employee ? [
                'id' => $employee->id,
                'name' => $employee->name,
                'document_number' => $employee->document_number,
                'phone' => $employee->phone,
                'driver_license_number' => $employee->driver_license_number,
                'driver_license_category' => $employee->driver_license_category,
                'driver_license_expiration' => $employee->driver_license_expiration?->toDateString(),
                'license_status' => $employee->license_status,
                'status' => $employee->status,
            ] : null,
        ]);
    }

    /**
     * Obtener el perfil y estado normativo del conductor autenticado.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'driver' => $employee ? [
                'id' => $employee->id,
                'name' => $employee->name,
                'document_number' => $employee->document_number,
                'phone' => $employee->phone,
                'driver_license_number' => $employee->driver_license_number,
                'driver_license_category' => $employee->driver_license_category,
                'driver_license_expiration' => $employee->driver_license_expiration?->toDateString(),
                'license_status' => $employee->license_status,
                'is_eligible' => empty($employee->getEligibilityErrors()),
                'eligibility_errors' => $employee->getEligibilityErrors(),
            ] : null,
        ]);
    }

    /**
     * Cerrar sesión revocando el token de acceso actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente.',
        ]);
    }
}
