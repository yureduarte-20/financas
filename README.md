# 💰 FinançasPessoais

> Transforme o caos financeiro em clareza com o poder da inteligência artificial.

O **FinançasPessoais** é um sistema inteligente projetado para simplificar sua gestão financeira. Chega de preencher planilhas complexas ou digitar cada cafezinho manualmente. Com a integração da IA (Claude LLM), o sistema interpreta automaticamente seus recibos e faturas, categorizando seus gastos para que você foque no que realmente importa.

---

## ✨ Funcionalidades Principais

- **📄 Importação Inteligente**: Faça upload de PDFs ou fotos de recibos e deixe que a IA extraia valores, datas e estabelecimentos automaticamente.
- **🏷️ Categorização Automática**: O sistema sugere categorias inteligentes para cada transação, aprendendo com seu uso.
- **📊 Dashboards Dinâmicos**: Visualize sua saúde financeira através de gráficos intuitivos de receitas, despesas e saldo líquido.
- **🔔 Controle de Orçamentos**: Defina limites mensais para categorias e receba alertas automáticos quando estiver próximo de atingi-los.
- **📑 Relatórios Completos**: Exporte seus dados em CSV ou PDF para uma análise mais profunda.

---

## 🛠️ Tecnologias

Este projeto utiliza o que há de mais moderno no ecossistema PHP:

- **Framework**: [Laravel 12](https://laravel.com)
- **Frontend**: [Livewire](https://livewire.laravel.com) (Interatividade sem sair do PHP)
- **Banco de Dados**: SQLite (Simples e eficiente)
- **Styling**: [Tailwind CSS](https://tailwindcss.com)
- **UI Framework**: [Preline UI](https://preline.co) (Componentes modernos e acessíveis)
- **IA**: Anthropic Claude API (Cérebro do sistema)
- **Engine de Build**: [Vite](https://vitejs.dev)

---

## 🧩 Componentes de UI

As páginas da aplicação **devem ser construídas utilizando os componentes Blade** localizados em `resources/views/componets/`. Esses componentes encapsulam o estilo do **Preline UI + Tailwind CSS** e garantem consistência visual em toda a aplicação.

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

---

## 🚀 Como Iniciar

Para rodar o projeto localmente, siga os passos abaixo:

### Pré-requisitos

- PHP 8.2 ou superior
- Composer
- Node.js & NPM

### Instalação Rápida

O projeto conta com um script de setup automatizado para facilitar sua vida:

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/seu-usuario/financas.git
   cd financas
   ```

2. **Execute o comando de setup:**
   Este comando instalará as dependências (PHP e JS), criará o arquivo `.env`, gerará a chave da aplicação e rodará as migrações iniciais.
   ```bash
   composer setup
   ```

3. **Inicie o servidor de desenvolvimento:**
   O projeto utiliza o `concurrently` para rodar o Laravel e o Vite simultaneamente:
   ```bash
   composer dev
   ```

Acesse o sistema em: [http://localhost:8000](http://localhost:8000)

---

## 📄 Licença

Este projeto é um software de código aberto licenciado sob a [Licença MIT](LICENSE).
