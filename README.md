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

## 🤖 Diretrizes e Arquitetura (LLMs & Agentes)

Para informações detalhadas sobre as regras de arquitetura do projeto (como o padrão de **Actions**), componentes reutilizáveis de interface (**UI Components**) e o sistema de cores (**Design System**), consulte o arquivo [AGENTS.md](file:///home/sti/Documentos/php/financas/AGENTS.md).

---

## 🚀 Como Iniciar

> [!IMPORTANT]
> **Ambiente Docker**: Todos os comandos Artisan, Composer ou do sistema **devem** ser executados via Docker utilizando o prefixo:
> `docker compose exec app <comando>` (ou `docker compose -f docker-compose.dev.yaml exec app <comando>` no ambiente de desenvolvimento). Evite rodá-los diretamente em sua máquina local.

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
   docker compose -f docker-compose.dev.yaml exec app composer setup
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
