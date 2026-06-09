<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Actions\Auth\ApiRegisterAction;
use App\Actions\Auth\ApiLoginAction;
use App\Actions\Auth\ApiVerifyCodeAction;
use App\Actions\Auth\GenerateAuthCodeAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Register a new user and send a verification code.
     */
    public function register(Request $request, ApiRegisterAction $action): JsonResponse
    {
        $result = $action->execute($request->all());

        return response()->json([
            'message' => 'Usuário registrado com sucesso. Por favor, verifique seu e-mail com o código enviado.',
            'user' => $result['user'],
        ], 201);
    }

    /**
     * Authenticate user credentials and send 2FA code.
     */
    public function login(Request $request, ApiLoginAction $action): JsonResponse
    {
        $result = $action->execute($request->all());

        return response()->json([
            'message' => 'Código de verificação enviado para o seu e-mail.',
            'email' => $result['email'],
        ], 200);
    }

    /**
     * Verify the 2FA code and issue a Sanctum token.
     */
    public function verifyCode(Request $request, ApiVerifyCodeAction $action): JsonResponse
    {
        $result = $action->execute($request->all());

        return response()->json([
            'message' => 'Autenticação realizada com sucesso.',
            'token' => $result['token'],
            'user' => $result['user'],
        ], 200);
    }

    /**
     * Resend verification/2FA code.
     */
    public function resendCode(Request $request, GenerateAuthCodeAction $action): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'type' => ['required', 'string', 'in:registration,api_login'],
        ]);

        $action->execute($validated['email'], $validated['type']);

        return response()->json([
            'message' => 'Novo código de verificação enviado para o seu e-mail.',
        ], 200);
    }

    /**
     * Revoke the user's current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ], 200);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }
}
