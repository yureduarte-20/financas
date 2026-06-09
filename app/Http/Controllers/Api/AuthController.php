<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Actions\Auth\ApiRegisterAction;
use App\Actions\Auth\ApiLoginAction;
use App\Actions\Auth\ApiVerifyCodeAction;
use App\Actions\Auth\GenerateAuthCodeAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Register a new user and send a verification code.
     */
    #[OA\Post(
        path: "/api/auth/register",
        summary: "Registrar um novo usuário",
        description: "Cria uma conta de usuário e dispara o envio de um código 2FA de 6 dígitos via e-mail.",
        tags: ["Autenticação"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "João Silva"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "joao@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "password123"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Usuário registrado com sucesso.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Usuário registrado com sucesso. Por favor, verifique seu e-mail com o código enviado."),
                        new OA\Property(property: "user", type: "object", properties: [
                            new OA\Property(property: "id", type: "string", format: "uuid", example: "9c1c9ff5-5858-45bf-97c3-bc7948a4c84d"),
                            new OA\Property(property: "name", type: "string", example: "João Silva"),
                            new OA\Property(property: "email", type: "string", example: "joao@example.com"),
                        ])
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Erro de validação nos campos informados."
            )
        ]
    )]
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
    #[OA\Post(
        path: "/api/auth/login",
        summary: "Iniciar o login (Fase 1)",
        description: "Valida as credenciais (e-mail e senha) e envia um código 2FA de 6 dígitos para o e-mail cadastrado.",
        tags: ["Autenticação"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "joao@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Credenciais corretas. Código 2FA enviado.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Código de verificação enviado para o seu e-mail."),
                        new OA\Property(property: "email", type: "string", example: "joao@example.com"),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Credenciais inválidas."
            ),
            new OA\Response(
                response: 422,
                description: "Erro de validação (campos obrigatórios)."
            )
        ]
    )]
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
    #[OA\Post(
        path: "/api/auth/verify-code",
        summary: "Confirmar código 2FA e obter token (Fase 2)",
        description: "Valida o código de 6 dígitos enviado por e-mail e gera o token de acesso Sanctum.",
        tags: ["Autenticação"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "code", "device_name"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "joao@example.com"),
                    new OA\Property(property: "code", type: "string", example: "123456"),
                    new OA\Property(property: "device_name", type: "string", example: "Celular João"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Token gerado com sucesso.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Autenticação realizada com sucesso."),
                        new OA\Property(property: "token", type: "string", example: "1|abcdefghijklmnop..."),
                        new OA\Property(property: "user", type: "object", properties: [
                            new OA\Property(property: "id", type: "string", format: "uuid", example: "9c1c9ff5-5858-45bf-97c3-bc7948a4c84d"),
                            new OA\Property(property: "name", type: "string", example: "João Silva"),
                            new OA\Property(property: "email", type: "string", example: "joao@example.com"),
                        ])
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Código inválido ou expirado."
            )
        ]
    )]
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
    #[OA\Post(
        path: "/api/auth/resend-code",
        summary: "Reenviar código de ativação/2FA",
        description: "Gera e reenvia um novo código de verificação para o e-mail informado.",
        tags: ["Autenticação"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "type"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "joao@example.com"),
                    new OA\Property(property: "type", type: "string", enum: ["registration", "api_login"], example: "api_login"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Código reenviado com sucesso.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Novo código de verificação enviado para o seu e-mail."),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Parâmetros inválidos."
            )
        ]
    )]
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
    #[OA\Post(
        path: "/api/auth/logout",
        summary: "Revogar token de acesso (Logout)",
        description: "Invalida o token Sanctum atual utilizado na requisição.",
        tags: ["Autenticação"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Logout efetuado com sucesso.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Logout realizado com sucesso."),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado."
            )
        ]
    )]
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
    #[OA\Get(
        path: "/api/auth/user",
        summary: "Obter dados do usuário logado",
        description: "Retorna as informações de cadastro do usuário atualmente autenticado.",
        tags: ["Autenticação"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Dados do usuário.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "user", type: "object", properties: [
                            new OA\Property(property: "id", type: "string", format: "uuid", example: "9c1c9ff5-5858-45bf-97c3-bc7948a4c84d"),
                            new OA\Property(property: "name", type: "string", example: "João Silva"),
                            new OA\Property(property: "email", type: "string", example: "joao@example.com"),
                        ])
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado."
            )
        ]
    )]
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }
}
