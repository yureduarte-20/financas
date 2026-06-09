# Diretrizes de Desenvolvimento (AGENTS)

Este documento descreve as convenções, arquitetura e regras de desenvolvimento que devem ser seguidas ao implementar novas funcionalidades neste repositório. Destinado especialmente para agentes autônomos de IA e desenvolvedores.

> [!IMPORTANT]
> **Execução de Comandos (Docker)**: Todos os comandos Artisan, Composer ou do sistema devem ser executados via Docker, utilizando obrigatoriamente o prefixo:
> `docker compose exec app <comando>` (ou `docker compose -f docker-compose.dev.yaml exec app <comando>` no ambiente de desenvolvimento). Não execute os comandos diretamente na sua máquina local.

---

## 🧩 Componentes de UI

As páginas da aplicação **devem ser construídas utilizando os componentes Blade** localizados em `resources/views/components/` (ou informados no projeto). Esses componentes encapsulam o estilo do **Preline UI + Tailwind CSS** e garantem consistência visual em toda a aplicação.

> ⚠️ **Regra de Ouro**: Nunca escreva classes CSS diretamente nas views. Utilize sempre os componentes abaixo.

### Componentes disponíveis

| Componente | Tag Blade | Descrição |
| :--- | :--- | :--- |
| **Input** | `<x-input>` | Campo de texto com suporte a label, validação e ícone |
| **Native Select** | `<x-native-select>` | Select nativo HTML com label e estado de erro |
| **Button** | `<x-button>` | Botão com variantes de cor (`primary`, `danger`, `success`…) e tamanho |
| **Button Outline** | `<x-button.outline>` | Versão outline do botão com as mesmas variantes |
| **Alert** | `<x-alert>` | Alertas tipados (`info`, `success`, `danger`, `warning`) com fechamento |
| **Errors** | `<x-errors>` | Exibe todos os erros de validação da sessão com auto-dismiss |
| **Dropdown** | `<x-dropdown>` | Menu dropdown com animação Alpine.js |
| **Dropdown Link** | `<x-dropdown-link>` | Item de link estilizado para uso dentro do `<x-dropdown>` |
| **Confirm Dialog** | `<x-confirm-dialog>` | Modal de confirmação reativo com callbacks `accept`/`reject` via Alpine.js |

### Exemplo de uso

```blade
{{-- Formulário com componentes --}}
<x-input label="E-mail" type="email" wire:model="email" required />

<x-native-select label="Categoria" wire:model="categoria_id">
    @foreach($categorias as $cat)
        <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
    @endforeach
</x-native-select>

<x-button color="primary" type="submit">Salvar</x-button>
<x-button.outline color="danger" wire:click="cancelar">Cancelar</x-button.outline>
...
```

---

## ⚙️ Actions — Centralização das Regras de Negócio

Toda **regra de negócio** da aplicação **deve** ser encapsulada em uma **Action**. Esse padrão garante que a lógica crítica (validação, autorização, persistência) fique centralizada, testável e desacoplada do mecanismo de entrada (Livewire, API, comando CLI, webhook Telegram, etc.).

### 📁 Estrutura

As Actions ficam em `app/Actions/` e estendem a classe base abstrata `AbstractAction`:

```
app/Actions/
├── AbstractAction.php          # Classe base com validacao e execucao
├── Auth/                       # Actions de autenticacao
├── Category/                   # CRUD de categorias
├── Profile/                    # Atualizacao de perfil/senha
└── Transaction/                # CRUD de transacoes (despesas e receitas)
```

### 🧩 Anatomia de uma Action

Toda Action concreta deve implementar dois métodos:

| Método | Retorno | Descrição |
|--------|---------|-----------|
| `rules()` | `array<string, Rule\|string>` | Regras de validação do input (Laravel Validation) |
| `execute(array $input): mixed` | `mixed` | Lógica principal: autorizar, persistir, retornar resultado |

A validação é feita automaticamente pela classe base através do método `validate()`, chamado dentro de `execute()`.

### ✅ Benefícios

1. **Regras de negócio centralizadas** — Não se espalham entre Controllers, Livewire Components ou Jobs.
2. **Testabilidade isolada** — Cada Action pode ser testada unitariamente sem depender de HTTP ou Livewire.
3. **Reutilização entre canais** — A mesma Action pode ser chamada pelo formulário Livewire, pelo bot do Telegram (via `HandlerCommandsJob`) ou por um comando Artisan.
4. **Validação explícita** — O método `rules()` define claramente quais campos são esperados e seus formatos.
5. **Autorização centralizada** — O `Gate::authorize()` é chamado dentro da `execute()`, garantindo que nenhum fluxo bypass a política de acesso.

### 🔗 Integração com Livewire Forms

Os formulários Livewire estendem `AbstractActionForm` e se vinculam a uma Action através do método `getAction()`:

```php
class CreateIncomeTransactionForm extends AbstractActionForm
{
    public string $name = '';
    public string $value = '';
    // ...

    public function getAction(): \App\Actions\AbstractAction
    {
        return app()->make(\App\Actions\Transaction\CreateIncomeTransactionAction::class);
    }
}
```

O método `submit()` do form executa: validação → Action → retorno, de forma padronizada.

### ⚠️ Regra de Ouro

> Nunca escreva lógica de negócio diretamente em Controllers, Livewire Components ou Views. **Crie uma Action** e delegue a ela a execução.

---

## 🎨 Design System & Cores

O projeto utiliza um sistema de **Tokens Semânticos** configurado no `resources/css/app.css` (Tailwind CSS v4). Isso garante que a identidade visual seja consistente e fácil de manter.

### Escolhas Cromáticas (Identidade)

| Tipo | Cor Base | Descrição |
| :--- | :--- | :--- |
| **Primary** | `Azul Céu` | Cor principal para ações, marca e estados ativos. |
| **Accent** | `Âmbar` | Cor de destaque para chamadas de atenção e detalhes. |

### Tokens Semânticos (Uso Geral)

Utilize estes tokens para garantir que as cores de estado sejam consistentes:
- `primary`, `accent`, `success`, `danger`, `warning`, `info`, `surface`.

Exemplo: `bg-primary`, `text-danger`, `border-success`.

### Suporte ao Modo Escuro (Dark Mode)

A aplicação possui suporte nativo ao modo escuro com tokens específicos:

| Token | Finalidade | Cor (Dark) |
| :--- | :--- | :--- |
| `dark-bg` | Fundo principal da página | `#0a0a0a` |
| `dark-surface` | Fundo de cards, sidebar e dropdowns | `#161615` |
| `dark-border` | Divisórias e bordas sutis | `#1e1e1e` |
| `dark-text` | Texto principal (alta legibilidade) | `#ededec` |
| `dark-muted` | Texto secundário ou desativado | `#a1a1aa` |

**Exemplo de implementação robusta:**
```html
<div class="bg-white dark:bg-dark-surface border-gray-200 dark:border-dark-border">
    <p class="text-gray-900 dark:text-dark-text">Conteúdo Principal</p>
    <p class="text-gray-500 dark:text-dark-muted">Informação secundária</p>
</div>
```
