<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponses;

    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_admin' => false,
            ]);

            $token = $user->createToken('API Token')->plainTextToken;

            return $this->createdResponse([
                'user' => $user,
                'token' => $token,
            ], 'Usuário registrado com sucesso');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
            
            $user = User::firstWhere('email', $validated['email']);

            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse('Credenciais inválidas', null, 401);
            }

            $token = $user->createToken('API Token')->plainTextToken;

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
            ], 'Autenticação realizada com sucesso');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return $this->successResponse(null, 'Logout realizado com sucesso');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'Usuário obtido com sucesso');
    }

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
                'current_password' => 'required_with:password|string',
                'password' => 'sometimes|required|string|min:8|confirmed',
            ]);

            if (isset($validated['current_password'])) {
                if (! Hash::check($validated['current_password'], $user->password)) {
                    return $this->errorResponse('Senha atual incorreta', null, 422);
                }
            }

            $updateData = array_filter([
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
                'password' => isset($validated['password']) ? Hash::make($validated['password']) : null,
            ]);

            $user->update($updateData);

            return $this->successResponse($user->fresh(), 'Perfil atualizado com sucesso');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        }
    }
}
