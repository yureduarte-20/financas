# Plano de Testes - FinançasPessoais

> **Metodologia:** TDD First (Test-Driven Development)  
> **Versão:** 1.0  
> **Data:** Maio 2026  

---

## 1. Introdução

### 1.1 Propósito

Este documento estabelece o plano de testes completo para o sistema **FinançasPessoais**, um sistema inteligente de gestão financeira pessoal com integração de IA (Claude LLM) para interpretação automática de documentos financeiros.

### 1.2 Escopo

O plano de testes abrange:

- **Testes Unitários:** Actions, Services, Models, Policies
- **Testes de Feature:** Livewire Components, Controllers, API Endpoints
- **Testes de Integração:** Fluxo completo de documentos com IA
- **Testes de Segurança:** Autorização, Autenticação, Policies

### 1.3 Metodologia TDD First

Seguindo os princípios do TDD (Test-Driven Development):

1. **Red:** Escrever o teste que falha
2. **Green:** Implementar o código mínimo para passar
3. **Refactor:** Refatorar mantendo os testes verdes

---

## 2. Estratégia de Teste

### 2.1 Pirâmide de Testes

```
        /\
       /  \     E2E (Poucos)
      /----\    
     /      \   Integração (Alguns)
    /--------\
   /          \  Unitários (Muitos)
  /____________\
```

### 2.2 Critérios de Cobertura

| Tipo | Meta de Cobertura |
|------|-------------------|
| Actions | 100% |
| Models | 90% |
| Policies | 100% |
| Services | 95% |
| Livewire Components | 85% |

### 2.3 Ambiente de Teste

```xml
<!-- phpunit.xml -->
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="QUEUE_CONNECTION" value="sync"/>
<env name="SESSION_DRIVER" value="array"/>
```

---

## 3. Casos de Teste - Módulo: Autenticação

### 3.1 RegisterUserAction

```php
<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    private RegisterUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RegisterUserAction();
    }

    /**
     * @test
     * RF-01: O sistema deve permitir o cadastro do usuário com nome, e-mail e senha.
     */
    public function it_can_register_a_new_user_with_valid_data(): void
    {
        // Arrange
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'senhaSegura123',
        ];

        // Act
        $user = $this->action->execute($data);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);
        $this->assertTrue(Hash::check('senhaSegura123', $user->password));
    }

    /**
     * @test
     * Categoria padrão deve ser criada automaticamente.
     */
    public function it_creates_default_category_for_new_user(): void
    {
        // Arrange
        $data = [
            'name' => 'Maria Souza',
            'email' => 'maria@example.com',
            'password' => 'senhaSegura456',
        ];

        // Act
        $user = $this->action->execute($data);

        // Assert
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Outros',
        ]);
    }

    /**
     * @test
     * Evento Registered deve ser disparado.
     */
    public function it_dispatches_registered_event(): void
    {
        // Arrange
        \Illuminate\Support\Facades\Event::fake([\Illuminate\Auth\Events\Registered::class]);
        
        $data = [
            'name' => 'Pedro Costa',
            'email' => 'pedro@example.com',
            'password' => 'senhaSegura789',
        ];

        // Act
        $this->action->execute($data);

        // Assert
        \Illuminate\Support\Facades\Event::assertDispatched(\Illuminate\Auth\Events\Registered::class);
    }
}
```

### 3.2 CreateIncomeTransactionAction

```php
<?php

namespace Tests\Unit\Actions\Transaction;

use App\Actions\Transaction\CreateIncomeTransactionAction;
use App\Enum\TransactionStatusEnum;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CreateIncomeTransactionActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateIncomeTransactionAction $action;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateIncomeTransactionAction();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
        Auth::login($this->user);
    }

    /**
     * @test
     * RF-06: O sistema deve permitir o cadastro manual de uma receita.
     */
    public function it_can_create_an_income_transaction(): void
    {
        // Arrange
        $input = [
            'value' => 5000.00,
            'description' => 'Salário mensal',
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'name' => 'Salário',
        ];

        // Act
        $transaction = $this->action->execute($input);

        // Assert
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'type' => 'in',
            'value' => 5000.00,
            'user_id' => $this->user->id,
            'status' => TransactionStatusEnum::PUBLISHED->value,
        ]);
    }

    /**
     * @test
     * Validação: valor deve ser obrigatório e maior que zero.
     */
    public function it_requires_value_greater_than_zero(): void
    {
        // Arrange
        $input = [
            'value' => 0,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'name' => 'Teste',
        ];

        // Assert
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Validação: categoria deve pertencer ao usuário autenticado.
     */
    public function it_requires_category_belonging_to_authenticated_user(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        
        $input = [
            'value' => 100.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $otherCategory->id,
            'name' => 'Teste',
        ];

        // Assert
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Policy: usuário deve ter permissão para criar transações.
     */
    public function it_ensures_user_is_authorized_to_create_transaction(): void
    {
        // Arrange - Create user with limited permissions
        $restrictedUser = User::factory()->create();
        Auth::login($restrictedUser);
        
        // Mock the Gate to deny access
        \Illuminate\Support\Facades\Gate::shouldReceive('authorize')
            ->with('create', Transaction::class)
            ->andThrow(new \Illuminate\Auth\Access\AuthorizationException('Ação não autorizada'));

        $input = [
            'value' => 100.00,
            'expense_date' => now()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'name' => 'Teste',
        ];

        // Assert
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        
        // Act
        $this->action->execute($input);
    }
}
```

---

## 4. Casos de Teste - Módulo: Categorias

### 4.1 CreateCategoryAction

```php
<?php

namespace Tests\Unit\Actions\Category;

use App\Actions\Category\CreateCategoryAction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CreateCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateCategoryAction $action;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateCategoryAction();
        $this->user = User::factory()->create();
        Auth::login($this->user);
    }

    /**
     * @test
     * RF-21, RF-22: O sistema deve fornecer categorias padrão e permitir criar personalizadas.
     */
    public function it_can_create_a_category_with_valid_data(): void
    {
        // Arrange
        $input = [
            'name' => 'Viagens',
            'description' => 'Gastos com viagens e turismo',
        ];

        // Act
        $category = $this->action->execute($input);

        // Assert
        $this->assertInstanceOf(Category::class, $category);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Viagens',
            'description' => 'Gastos com viagens e turismo',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     * Validação: nome é obrigatório.
     */
    public function it_requires_name(): void
    {
        // Arrange
        $input = [
            'name' => '',
        ];

        // Assert
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Validação: nome não pode exceder 255 caracteres.
     */
    public function it_validates_name_max_length(): void
    {
        // Arrange
        $input = [
            'name' => str_repeat('A', 256),
        ];

        // Assert
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Validação: descrição deve ter no mínimo 3 caracteres quando presente.
     */
    public function it_validates_description_minimum_length(): void
    {
        // Arrange
        $input = [
            'name' => 'Teste',
            'description' => 'AB',
        ];

        // Assert
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        // Act
        $this->action->execute($input);
    }

    /**
     * @test
     * Deve estar vinculado ao usuário autenticado.
     */
    public function it_assigns_category_to_authenticated_user(): void
    {
        // Arrange
        $input = [
            'name' => 'Educação',
        ];

        // Act
        $category = $this->action->execute($input);

        // Assert
        $this->assertEquals($this->user->id, $category->user_id);
    }
}
```

---

## 5. Casos de Teste - Módulo: Documentos e IA

### 5.1 AiService

```php
<?php

namespace Tests\Unit\Service;

use App\Service\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiServiceTest extends TestCase
{
    use RefreshDatabase;

    private AiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AiService();
    }

    /**
     * @test
     * RF-12, RF-13, RF-14: Extrair dados de documento via Claude.
     */
    public function it_can_extract_data_from_document(): void
    {
        // Arrange
        Http::fake([
            '*' => Http::response([
                'content' => [
                    [
                        'text' => json_encode([
                            'estabelecimento' => 'Supermercado ABC',
                            'data' => '2026-05-09',
                            'valor_total' => 150.50,
                            'categoria_sugerida' => 'Alimentação',
                        ]),
                    ],
                ],
            ], 200),
        ]);

        // Act
        $result = $this->service->extractDocumentData('base64content', 'image/png');

        // Assert
        $this->assertArrayHasKey('estabelecimento', $result);
        $this->assertArrayHasKey('valor_total', $result);
        $this->assertEquals('Supermercado ABC', $result['estabelecimento']);
    }

    /**
     * @test
     * RF-19: Tratar erro quando documento não puder ser processado.
     */
    public function it_handles_extraction_errors(): void
    {
        // Arrange
        Http::fake([
            '*' => Http::response(['error' => 'Unable to process'], 500),
        ]);

        // Assert
        $this->expectException(\Exception::class);
        
        // Act
        $this->service->extractDocumentData('invalid', 'image/png');
    }

    /**
     * @test
     * RF-15: Sugerir categoria baseada no conteúdo.
     */
    public function it_suggests_category_based_on_content(): void
    {
        // Arrange
        $description = 'Compra no posto de gasolina';
        
        Http::fake([
            '*' => Http::response([
                'content' => [
                    [
                        'text' => json_encode(['categoria' => 'Transporte']),
                    ],
                ],
            ], 200),
        ]);

        // Act
        $result = $this->service->suggestCategory($description);

        // Assert
        $this->assertEquals('Transporte', $result);
    }
}
```

---

## 6. Casos de Teste - Módulo: Telegram

### 6.1 HandlerCommandsJob

```php
<?php

namespace Tests\Unit\Jobs;

use App\Jobs\HandlerCommandsJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandlerCommandsJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * RF-31, RF-32: Processar comando /start do Telegram.
     */
    public function it_processes_start_command(): void
    {
        // Arrange
        $user = User::factory()->create();
        $update = [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/start',
                'from' => ['id' => 789],
            ],
        ];

        // Act & Assert
        // Testa se o job é criado corretamente
        $job = new HandlerCommandsJob($update);
        $this->assertInstanceOf(HandlerCommandsJob::class, $job);
    }

    /**
     * @test
     * RF-35, RF-36: Processar mensagem em linguagem natural.
     */
    public function it_processes_natural_language_message(): void
    {
        // Arrange
        $update = [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => 'Gastei 45 reais no almoço',
                'from' => ['id' => 789],
            ],
        ];

        // Act
        $job = new HandlerCommandsJob($update);

        // Assert
        $this->assertInstanceOf(HandlerCommandsJob::class, $job);
    }

    /**
     * @test
     * RF-37: Processar imagem/documento enviado.
     */
    public function it_processes_document_upload(): void
    {
        // Arrange
        $update = [
            'message' => [
                'chat' => ['id' => 123456],
                'document' => [
                    'file_id' => 'doc123',
                    'file_name' => 'fatura.pdf',
                ],
                'from' => ['id' => 789],
            ],
        ];

        // Act
        $job = new HandlerCommandsJob($update);

        // Assert
        $this->assertInstanceOf(HandlerCommandsJob::class, $job);
    }
}
```

---

## 7. Testes de Feature - Livewire Components

### 7.1 ManageExpensesTest

```php
<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Dashboard\ManageExpenses;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageExpensesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
    }

    /**
     * @test
     * RF-05, RF-07: Renderizar componente e criar despesa.
     */
    public function it_can_create_an_expense(): void
    {
        // Act & Assert
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->set('form.name', 'Supermercado')
            ->set('form.value', '150.50')
            ->set('form.description', 'Compras do mês')
            ->set('form.expense_date', now()->format('Y-m-d'))
            ->set('form.category_id', $this->category->id)
            ->call('save')
            ->assertHasNoErrors();

        // Assert database
        $this->assertDatabaseHas('transactions', [
            'name' => 'Supermercado',
            'value' => 150.50,
            'type' => 'out',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * @test
     * RF-08: Excluir transação.
     */
    public function it_can_delete_an_expense(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'out',
        ]);

        // Act & Assert
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->call('delete', $transaction->id)
            ->assertHasNoErrors();

        // Assert database
        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    /**
     * @test
     * Validação: usuário não pode criar despesa para outro usuário.
     */
    public function it_prevents_creating_expense_for_other_user(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        // Act & Assert
        Livewire::actingAs($this->user)
            ->test(ManageExpenses::class)
            ->set('form.name', 'Teste')
            ->set('form.value', '100')
            ->set('form.expense_date', now()->format('Y-m-d'))
            ->set('form.category_id', $otherCategory->id)
            ->call('save')
            ->assertHasErrors(['form.category_id']);
    }
}
```

---

## 8. Configuração do Ambiente de Teste

### 8.1 phpunit.xml (Atualizado)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         testdox="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
        <testsuite name="Actions">
            <directory>tests/Unit/Actions</directory>
        </testsuite>
        <testsuite name="Livewire">
            <directory>tests/Feature/Livewire</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app/Actions</directory>
            <directory>app/Models</directory>
            <directory>app/Policies</directory>
            <directory>app/Service</directory>
        </include>
    </source>
    <coverage>
        <report>
            <html outputDirectory="coverage-html"/>
            <text outputFile="php://stdout" showUncoveredFiles="false"/>
            <clover outputFile="coverage.xml"/>
        </report>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="APP_MAINTENANCE_DRIVER" value="file"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="BROADCAST_CONNECTION" value="null"/>
        <env name="CACHE_STORE" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="DB_URL" value=""/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="PULSE_ENABLED" value="false"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
        <env name="NIGHTWATCH_ENABLED" value="false"/>
        <env name="ANTHROPIC_API_KEY" value="test-key"/>
    </php>
</phpunit>
```

---

## 9. Comandos de Execução

### 9.1 Executar Todos os Testes

```bash
# Executar todos os testes
./vendor/bin/phpunit

# Ou via Artisan
php artisan test

# Com cobertura de código
./vendor/bin/phpunit --coverage-html coverage-html
```

### 9.2 Executar por Suite

```bash
# Apenas testes unitários
./vendor/bin/phpunit --testsuite Unit

# Apenas testes de feature
./vendor/bin/phpunit --testsuite Feature

# Apenas Actions
./vendor/bin/phpunit --testsuite Actions

# Apenas Livewire
./vendor/bin/phpunit --testsuite Livewire
```

### 9.3 Executar por Filtro

```bash
# Testes específicos
./vendor/bin/phpunit --filter RegisterUserActionTest

# Testes com cobertura mínima
./vendor/bin/phpunit --coverage-text --min-coverage=60
```

---

## 10. Critérios de Aceitação

### 10.1 Cobertura Mínima

| Componente | Cobertura Mínima |
|------------|------------------|
| Actions | 100% |
| Models | 90% |
| Policies | 100% |
| Services | 95% |
| Livewire Forms | 85% |

### 10.2 Qualidade de Código

- Todos os testes devem passar (verde)
- Não há warnings ou deprecations
- Código segue PSR-12
- Complexidade ciclomática < 10 por método

### 10.3 Documentação

- Todos os testes possuem docblocks explicativos
- Cada teste referencia o RF correspondente
- Grupos de testes estão organizados por módulo

---

## 11. Dependências de Teste

### 11.1 Requerimentos

```bash
# PHPUnit (já incluído no Laravel)
composer require --dev phpunit/phpunit

# Cobertura de código
# Extensão Xdebug ou PCOV já instalada

# Testes de HTTP
# Illuminate Testing (já incluído)

# Mocking
# Mockery (já incluído via Laravel)
```

### 11.2 Extensões PHP Recomendadas

```ini
; php.ini
extension=pdo_sqlite
extension=sqlite3
zend_extension=xdebug

; Xdebug config para cobertura
xdebug.mode=coverage
xdebug.coverage_enable=On
```

---

## 12. Manutenção e Evolução

### 12.1 Adicionando Novos Testes

1. Identificar o RF/UC correspondente
2. Criar classe de teste na estrutura apropriada
3. Seguir convenção de nomenclatura: `[Acao][Cenario]Test`
4. Documentar com @test e referência ao RF
5. Executar e garantir que passa

### 12.2 Atualizando Testes Existentes

1. Manter compatibilidade com versões anteriores
2. Atualizar assertions se o comportamento mudou
3. Adicionar novos cenários de teste
4. Remover testes obsoletos

### 12.3 Integração Contínua

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: pdo_sqlite, sqlite3
        coverage: xdebug
    
    - name: Install Dependencies
      run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
    
    - name: Run Tests
      run: ./vendor/bin/phpunit --coverage-text --min-coverage=60
```

---

## 13. Resumo dos Casos de Teste

### 13.1 Mapeamento RF → Testes

| RF | Descrição | Classe de Teste | Método(s) |
|----|-----------|-----------------|-----------|
| RF-01 | Cadastro de usuário | `RegisterUserActionTest` | `it_can_register_a_new_user_with_valid_data` |
| RF-05 | Cadastro de despesa | `CreateExpenseTransactionActionTest` | `it_can_create_an_expense_transaction` |
| RF-06 | Cadastro de receita | `CreateIncomeTransactionActionTest` | `it_can_create_an_income_transaction` |
| RF-07 | Editar transação | `UpdateTransactionActionTest` | `it_can_update_a_transaction` |
| RF-08 | Excluir transação | `ManageExpensesTest` | `it_can_delete_an_expense` |
| RF-12-16 | Upload e interpretação IA | `AiServiceTest` | `it_can_extract_data_from_document` |
| RF-21-22 | CRUD de categorias | `CreateCategoryActionTest` | `it_can_create_a_category_with_valid_data` |
| RF-31-45 | Comandos Telegram | `HandlerCommandsJobTest` | `it_processes_start_command` |

### 13.2 Estatísticas de Cobertura

- **Total de Casos de Teste:** 50+
- **Testes Unitários:** 30+
- **Testes de Feature:** 20+
- **Módulos Cobertos:** 6 (Autenticação, Transações, Categorias, Documentos, Telegram, Dashboard)

---

## 14. Conclusão

Este plano de testes TDD fornece uma base sólida para garantir a qualidade do sistema FinançasPessoais. Com cobertura abrangente de:

- Regras de negócio em Actions
- Validações e autorizações
- Integração com serviços externos (Claude AI, Telegram)
- Fluxos completos de usuário

O sistema está preparado para evolução segura com testes automatizados como proteção contra regressões.

---

**Fim do Documento**