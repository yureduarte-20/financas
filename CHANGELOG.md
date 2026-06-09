# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

## [Não lançado] - 2026-06-09

### Adicionado
- **Laravel Sanctum:** Instalado e configurado para autenticação por token nativo.
- **Autenticação API REST com 2FA:**
  - Fluxo de registro de usuário com geração de código 2FA (`POST /api/auth/register`).
  - Fluxo de login em duas etapas: verificação de credenciais e envio de código 2FA via e-mail (`POST /api/auth/login`).
  - Fluxo de verificação de código 2FA com emissão de token Sanctum (`POST /api/auth/verify-code`).
  - Fluxo de reenvio de código de verificação (`POST /api/auth/resend-code`).
  - Endpoint para obter dados do usuário autenticado (`GET /api/auth/user`).
  - Endpoint de logout para revogação do token do dispositivo (`POST /api/auth/logout`).
- **API REST para Categorias (CRUD):**
  - Endpoint para listar todas as categorias do usuário autenticado (`GET /api/categories`).
  - Endpoint para exibir detalhes de uma categoria específica (`GET /api/categories/{category}`).
  - Endpoint para criar uma categoria (`POST /api/categories`).
  - Endpoint para atualizar uma categoria (`PUT /api/categories/{category}`).
  - Endpoint para excluir uma categoria (`DELETE /api/categories/{category}`).
- **Actions dedicadas:**
  - `ApiRegisterAction` em `app/Actions/Auth/ApiRegisterAction.php` para registrar usuário e gerar código.
  - `ApiLoginAction` em `app/Actions/Auth/ApiLoginAction.php` para validar credenciais e solicitar código.
  - `ApiVerifyCodeAction` em `app/Actions/Auth/ApiVerifyCodeAction.php` para verificar código e gerar token.
- **Controller de API:**
  - `AuthController` em `app/Http/Controllers/Api/AuthController.php` contendo os handlers das requisições JSON.
  - `CategoryController` em `app/Http/Controllers/Api/CategoryController.php` contendo os handlers do CRUD.
- **Middleware:**
  - `ForceJsonResponse` em `app/Http/Middleware/ForceJsonResponse.php` para garantir cabeçalhos `Accept: application/json` nas rotas de API.
- **Rotas de API:**
  - Declaração em `routes/api.php` e registro no bootstrapper da aplicação.
- **Testes de Integração (Feature):**
  - Suite de testes completa em `tests/Feature/ApiAuthTest.php` validando os fluxos felizes e exceções com asserções REST completas.
  - Suite de testes completa em `tests/Feature/ApiCategoryTest.php` cobrindo o CRUD de categorias, validação e autorização por usuário.

### Modificado
- `app/Models/User.php`: Adicionada a trait `Laravel\Sanctum\HasApiTokens` para permitir a emissão de tokens.
- `config/auth.php`: Adicionado o guard `api` utilizando o driver do `sanctum`.
- `bootstrap/app.php`: Registrado o arquivo de rotas `api` e injetado o middleware `ForceJsonResponse` no grupo de rotas da API.
