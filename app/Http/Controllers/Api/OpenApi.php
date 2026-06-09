<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Finanças Pessoais API",
    version: "1.0.0",
    description: "API de controle financeiro pessoal com autenticação por token Sanctum e verificação de dois fatores (2FA)."
)]
#[OA\Server(
    url: "/",
    description: "Servidor Principal"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    name: "Authorization",
    in: "header",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Insira o token recebido no fluxo de verificação 2FA (formato: Bearer <token>)"
)]
class OpenApi
{
}
