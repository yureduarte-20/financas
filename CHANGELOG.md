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
- **API REST para Transações (CRUD):**
  - Endpoint para listar todas as transações do usuário autenticado, com suporte a ordenação decrescente por data e filtro por tipo (`GET /api/transactions?type=out|income`).
  - Endpoint para exibir detalhes de uma transação específica com seu relacionamento de categoria (`GET /api/transactions/{transaction}`).
  - Endpoint para criar uma transação (`POST /api/transactions`), despachando para a Action correta com base no `type` (`out` ou `income`).
  - Endpoint para atualizar uma transação (`PUT /api/transactions/{transaction}`), despachando dinamicamente para as Actions correspondentes.
  - Endpoint para excluir uma transação (`DELETE /api/transactions/{transaction}`), despachando dinamicamente para as Actions correspondentes.
- **Actions dedicadas:**
  - `ApiRegisterAction` em `app/Actions/Auth/ApiRegisterAction.php` para registrar usuário e gerar código.
  - `ApiLoginAction` em `app/Actions/Auth/ApiLoginAction.php` para validar credenciais e solicitar código.
  - `ApiVerifyCodeAction` em `app/Actions/Auth/ApiVerifyCodeAction.php` para verificar código e gerar token.
- **Controller de API:**
  - `AuthController` em `app/Http/Controllers/Api/AuthController.php` contendo os handlers das requisições JSON.
  - `CategoryController` em `app/Http/Controllers/Api/CategoryController.php` contendo os handlers do CRUD.
  - `TransactionController` em `app/Http/Controllers/Api/TransactionController.php` contendo os handlers do CRUD de transações.
- **Middleware:**
  - `ForceJsonResponse` em `app/Http/Middleware/ForceJsonResponse.php` para garantir cabeçalhos `Accept: application/json` nas rotas de API.
- **Rotas de API:**
  - Declaração em `routes/api.php` e registro no bootstrapper da aplicação.
- **Testes de Integração (Feature):**
  - Suite de testes completa em `tests/Feature/ApiAuthTest.php` validando os fluxos felizes e exceções com asserções REST completas.
  - Suite de testes completa em `tests/Feature/ApiCategoryTest.php` cobrindo o CRUD de categorias, validação e autorização por usuário.
  - Suite de testes completa em `tests/Feature/ApiTransactionTest.php` cobrindo o CRUD de transações, filtragem, validações e autorizações.

### Modificado
- `app/Models/User.php`: Adicionada a trait `Laravel\Sanctum\HasApiTokens` para permitir a emissão de tokens.
- `config/auth.php`: Adicionado o guard `api` utilizando o driver do `sanctum`.
- `bootstrap/app.php`: Registrado o arquivo de rotas `api` e injetado o middleware `ForceJsonResponse` no grupo de rotas da API.
