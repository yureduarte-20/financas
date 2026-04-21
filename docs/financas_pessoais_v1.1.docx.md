  
**FinançasPessoais**

Sistema de Gerenciamento de Gastos e Ganhos com IA

**Documento de Visão, Requisitos Funcionais e Casos de Uso**

Versão 1.1  •  Abril de 2026

*Sistema Pessoal de Uso Individual*

# **1\. Visão Geral do Sistema**

## **1.1 Declaração de Visão**

O FinançasPessoais é um sistema simples e inteligente para controle financeiro pessoal. Seu diferencial é a capacidade de interpretar automaticamente faturas, recibos e comprovantes por meio de IA generativa (Claude LLM), eliminando a entrada manual de dados e oferecendo ao usuário uma visão clara de seus gastos e ganhos.

## **1.2 Objetivos do Sistema**

* Centralizar o registro de receitas e despesas pessoais em um único lugar.

* Automatizar a leitura e interpretação de faturas, recibos e boletos via IA.

* Categorizar transações automaticamente com base no conteúdo dos documentos.

* Gerar relatórios e dashboards simples para apoio à decisão financeira.

* Alertar o usuário sobre gastos acima do orçamento definido por categoria.

## **1.3 Escopo do Sistema**

| Dentro do Escopo Cadastro manual de receitas e despesas Upload e interpretação automática de faturas/recibos (PDF e imagem) Categorização automática via IA (Claude) Dashboard financeiro mensal Relatórios por período e categoria Definição e monitoramento de orçamentos por categoria Histórico de transações com busca e filtros |
| :---- |

| Fora do Escopo Integração automática com bancos ou Open Banking Múltiplos usuários / compartilhamento de contas Planejamento de investimentos ou consultoria financeira Emissão de notas fiscais ou documentos contábeis |
| :---- |

## **1.4 Usuário-Alvo**

Pessoa física que deseja controlar suas finanças de forma simples, sem precisar de planilhas complexas. O perfil típico inclui autonomia digital básica (upload de arquivos, uso de navegador) e desejo de automatizar o registro financeiro no dia a dia.

## **1.5 Tecnologias Envolvidas**

| Camada | Tecnologia / Componente |
| :---- | :---- |
| **IA / LLM** | Claude (Anthropic) — interpretação de documentos e categorização |
| **Frontend** | Web (React ou Next.js) ou App Mobile simples |
| **Backend** | Node.js / Python com API REST |
| **Banco de Dados** | SQLite (local) ou PostgreSQL (nuvem) |
| **Armazenamento** | Sistema de arquivos local ou S3 para documentos |
| **Autenticação** | Login simples com e-mail e senha (JWT) |

# **2\. Requisitos Funcionais**

Os requisitos funcionais estão organizados em módulos. A prioridade segue a convenção: Alta (essencial para MVP), Média (importante, mas pode ser diferida) e Baixa (desejável).

## **2.1 Módulo: Autenticação e Perfil**

| ID | Descrição | Prioridade | Módulo |
| :---- | :---- | :---- | :---- |
| **RF-01** | O sistema deve permitir o cadastro do usuário com nome, e-mail e senha. | **Alta** | Autenticação |
| **RF-02** | O sistema deve autenticar o usuário via login com e-mail e senha. | **Alta** | Autenticação |
| **RF-03** | O sistema deve permitir recuperação de senha por e-mail. | **Média** | Autenticação |
| **RF-04** | O usuário deve poder definir sua moeda padrão (Real, Dólar, Euro). | **Média** | Perfil |

## **2.2 Módulo: Transações**

| ID | Descrição | Prioridade | Módulo |
| :---- | :---- | :---- | :---- |
| **RF-05** | O sistema deve permitir o cadastro manual de uma despesa com valor, data, categoria e descrição. | **Alta** | Transações |
| **RF-06** | O sistema deve permitir o cadastro manual de uma receita com valor, data, categoria e descrição. | **Alta** | Transações |
| **RF-07** | O sistema deve permitir editar qualquer transação registrada. | **Alta** | Transações |
| **RF-08** | O sistema deve permitir excluir qualquer transação registrada. | **Alta** | Transações |
| **RF-09** | O sistema deve exibir lista de transações com filtros por período, categoria e tipo (receita/despesa). | **Alta** | Transações |
| **RF-10** | O sistema deve permitir pesquisa de transações por palavra-chave na descrição. | **Média** | Transações |
| **RF-11** | O sistema deve permitir marcar uma transação como recorrente (mensal, semanal etc.). | **Baixa** | Transações |

## **2.3 Módulo: Interpretador de Documentos com IA**

| ID | Descrição | Prioridade | Módulo |
| :---- | :---- | :---- | :---- |
| **RF-12** | O sistema deve aceitar upload de arquivos PDF e imagens (JPG, PNG) de faturas e recibos. | **Alta** | IA / Docs |
| **RF-13** | O sistema deve enviar o documento à API do Claude para extração dos dados financeiros. | **Alta** | IA / Docs |
| **RF-14** | O sistema deve extrair do documento: estabelecimento, valor total, data e itens (quando disponíveis). | **Alta** | IA / Docs |
| **RF-15** | O sistema deve sugerir uma categoria para a transação extraída, podendo o usuário confirmar ou alterar. | **Alta** | IA / Docs |
| **RF-16** | Após confirmação do usuário, os dados extraídos devem ser salvos como uma transação. | **Alta** | IA / Docs |
| **RF-17** | O sistema deve exibir um preview do documento junto aos dados extraídos para conferência. | **Média** | IA / Docs |
| **RF-18** | O sistema deve armazenar o documento original vinculado à transação gerada. | **Média** | IA / Docs |
| **RF-19** | Em caso de extração incompleta, o sistema deve indicar quais campos não foram identificados. | **Média** | IA / Docs |
| **RF-20** | O sistema deve suportar documentos em português e inglês. | **Baixa** | IA / Docs |

## **2.4 Módulo: Categorias e Orçamento**

| ID | Descrição | Prioridade | Módulo |
| :---- | :---- | :---- | :---- |
| **RF-21** | O sistema deve fornecer categorias padrão (Alimentação, Transporte, Saúde, Lazer, Moradia, Educação, Outros). | **Alta** | Categorias |
| **RF-22** | O usuário deve poder criar, editar e excluir categorias personalizadas. | **Média** | Categorias |
| **RF-23** | O usuário deve poder definir um orçamento mensal por categoria. | **Média** | Orçamento |
| **RF-24** | O sistema deve alertar visualmente quando o gasto de uma categoria atingir 80% do orçamento. | **Média** | Orçamento |
| **RF-25** | O sistema deve alertar quando o gasto de uma categoria ultrapassar o orçamento. | **Alta** | Orçamento |

## **2.5 Módulo: Dashboard e Relatórios**

| ID | Descrição | Prioridade | Módulo |
| :---- | :---- | :---- | :---- |
| **RF-26** | O sistema deve exibir um dashboard com resumo do mês: total de receitas, despesas e saldo. | **Alta** | Dashboard |
| **RF-27** | O dashboard deve exibir gráfico de despesas por categoria no mês atual. | **Alta** | Dashboard |
| **RF-28** | O dashboard deve exibir evolução do saldo nos últimos 6 meses. | **Média** | Dashboard |
| **RF-29** | O sistema deve gerar relatório de transações filtrado por período e categoria. | **Alta** | Relatórios |
| **RF-30** | O sistema deve permitir exportar o relatório em CSV ou PDF. | **Média** | Relatórios |

# **3\. Requisitos Não Funcionais**

| Categoria | Requisito | Critério |
| :---- | :---- | :---- |
| **Desempenho** | O processamento de um documento via IA deve ser concluído em tempo aceitável. | *\< 15 segundos* |
| **Usabilidade** | A interface deve ser intuitiva para usuários sem experiência técnica. | *SUS Score \>= 70* |
| **Segurança** | As senhas devem ser armazenadas com hash seguro (bcrypt). | *OWASP A02* |
| **Segurança** | As comunicações devem ocorrer via HTTPS. | *TLS 1.2+* |
| **Disponibilidade** | O sistema deve estar disponível para uso offline em funcionalidades básicas. | *Core offline* |
| **Manutenibilidade** | O código deve seguir padrões de clean code e ter testes unitários. | *Cobertura \> 60%* |
| **Privacidade** | Documentos enviados à IA não devem ser retidos pela API além do necessário. | *Zero retention* |

# **4\. Casos de Uso**

Os casos de uso a seguir descrevem as principais interações do usuário com o sistema. O ator principal em todos os casos é o Usuário (proprietário da conta).

## **4.1 UC-01: Registrar Despesa Manualmente**

| UC-01 — Registrar Despesa Manualmente |  |
| :---- | :---- |
| **Identificador** | UC-01 |
| **Nome** | Registrar Despesa Manualmente |
| **Ator Principal** | Usuário |
| **Pré-condições** | Usuário autenticado no sistema. |
| **Fluxo Principal** | 1\. Usuário acessa a tela de Nova Transação. 2\. Seleciona o tipo 'Despesa'. 3\. Preenche valor, data, categoria e descrição. 4\. Confirma o registro. 5\. Sistema salva e exibe a transação na lista. |
| **Fluxos Alternativos** | 4a. Campos obrigatórios em branco: sistema exibe mensagem de validação e não salva. |
| **Pós-condições** | Nova despesa registrada e visível no dashboard e relatórios. |

## **4.2 UC-02: Registrar Receita Manualmente**

| UC-02 — Registrar Receita Manualmente |  |
| :---- | :---- |
| **Identificador** | UC-02 |
| **Nome** | Registrar Receita Manualmente |
| **Ator Principal** | Usuário |
| **Pré-condições** | Usuário autenticado no sistema. |
| **Fluxo Principal** | 1\. Usuário acessa a tela de Nova Transação. 2\. Seleciona o tipo 'Receita'. 3\. Preenche valor, data, categoria (ex.: Salário, Freelance) e descrição. 4\. Confirma o registro. 5\. Sistema salva e atualiza o saldo. |
| **Fluxos Alternativos** | 4a. Valor negativo informado: sistema exibe erro de validação. |
| **Pós-condições** | Nova receita registrada; saldo do mês atualizado no dashboard. |

## **4.3 UC-03: Interpretar Fatura ou Recibo via IA**

| UC-03 — Interpretar Fatura ou Recibo via IA |  |
| :---- | :---- |
| **Identificador** | UC-03 |
| **Nome** | Interpretar Fatura ou Recibo via IA |
| **Ator Principal** | Usuário |
| **Pré-condições** | Usuário autenticado. Arquivo PDF ou imagem disponível no dispositivo. |
| **Fluxo Principal** | 1\. Usuário acessa 'Importar Documento'. 2\. Faz upload do arquivo (PDF/JPG/PNG). 3\. Sistema exibe preview do documento. 4\. Sistema envia o documento para a API do Claude. 5\. Claude extrai: estabelecimento, data, valor total e itens. 6\. Sistema exibe os dados extraídos para revisão do usuário. 7\. Usuário confirma ou edita os dados. 8\. Sistema salva como nova transação vinculada ao documento. |
| **Fluxos Alternativos** | 5a. Documento ilegível ou sem dados financeiros: sistema informa que não foi possível extrair dados e solicita cadastro manual. 7a. Usuário edita categoria sugerida antes de confirmar. |
| **Pós-condições** | Transação registrada com documento original anexado. |

## **4.4 UC-04: Visualizar Dashboard Financeiro**

| UC-04 — Visualizar Dashboard Financeiro |  |
| :---- | :---- |
| **Identificador** | UC-04 |
| **Nome** | Visualizar Dashboard Financeiro |
| **Ator Principal** | Usuário |
| **Pré-condições** | Usuário autenticado. Pelo menos uma transação registrada. |
| **Fluxo Principal** | 1\. Usuário acessa a tela inicial (Dashboard). 2\. Sistema exibe o mês atual com: total de receitas, total de despesas, saldo líquido. 3\. Exibe gráfico de pizza/barra com despesas por categoria. 4\. Exibe barra de progresso de orçamento por categoria. 5\. Exibe alertas de categorias acima do orçamento. |
| **Fluxos Alternativos** | 1a. Nenhuma transação no mês: sistema exibe estado vazio com orientação para registrar. |
| **Pós-condições** | Usuário visualiza panorama financeiro do mês. |

## **4.5 UC-05: Definir Orçamento Mensal por Categoria**

| UC-05 — Definir Orçamento Mensal por Categoria |  |
| :---- | :---- |
| **Identificador** | UC-05 |
| **Nome** | Definir Orçamento Mensal por Categoria |
| **Ator Principal** | Usuário |
| **Pré-condições** | Usuário autenticado. |
| **Fluxo Principal** | 1\. Usuário acessa Configurações \> Orçamentos. 2\. Seleciona uma categoria. 3\. Informa o valor limite mensal. 4\. Confirma. 5\. Sistema passa a monitorar os gastos nessa categoria e emite alertas quando necessário. |
| **Fluxos Alternativos** | 3a. Valor zerado ou negativo: sistema não aceita e exibe validação. |
| **Pós-condições** | Orçamento salvo; alertas ativados para a categoria. |

## **4.6 UC-06: Gerar e Exportar Relatório**

| UC-06 — Gerar e Exportar Relatório |  |
| :---- | :---- |
| **Identificador** | UC-06 |
| **Nome** | Gerar e Exportar Relatório |
| **Ator Principal** | Usuário |
| **Pré-condições** | Usuário autenticado. Transações registradas no período desejado. |
| **Fluxo Principal** | 1\. Usuário acessa Relatórios. 2\. Seleciona período (mês, trimestre, personalizado) e categorias desejadas. 3\. Sistema gera o relatório com lista de transações e totais. 4\. Usuário escolhe exportar em CSV ou PDF. 5\. Sistema gera o arquivo e inicia o download. |
| **Fluxos Alternativos** | 3a. Nenhuma transação no período: sistema informa resultado vazio. |
| **Pós-condições** | Arquivo de relatório gerado e disponível para download. |

## **4.7 UC-07: Editar ou Excluir Transação**

| UC-07 — Editar ou Excluir Transação |  |
| :---- | :---- |
| **Identificador** | UC-07 |
| **Nome** | Editar ou Excluir Transação |
| **Ator Principal** | Usuário |
| **Pré-condições** | Usuário autenticado. Transação existente no sistema. |
| **Fluxo Principal** | 1\. Usuário localiza a transação na lista (por busca ou scroll). 2\. Seleciona 'Editar'. 3\. Altera os campos desejados. 4\. Confirma. 5\. Sistema salva as alterações e atualiza dashboard e relatórios. (Para excluir: passo 2 \- 'Excluir' \> confirmar exclusão.) |
| **Fluxos Alternativos** | 4a. Campos obrigatórios vazios: sistema exibe validação. Exclusão: usuário cancela na confirmação — nenhuma alteração. |
| **Pós-condições** | Transação atualizada ou removida; totais recalculados. |

# **5\. Fluxo de Integração com IA**

| Fluxo: Upload de Documento \-\> Interpretação Claude \-\> Registro 1\. Usuário faz upload do documento (PDF ou imagem) via interface. 2\. Backend recebe o arquivo e converte para formato base64 se necessário. 3\. Backend monta prompt estruturado: envia o documento \+ instrução de extração para a API do Claude. 4\. Prompt solicita ao Claude: estabelecimento, CNPJ (se houver), data, valor total, itens detalhados e categoria sugerida. 5\. Claude retorna JSON estruturado com os campos extraídos. 6\. Backend valida e normaliza os dados retornados. 7\. Frontend exibe os dados para revisão e confirmação do usuário. 8\. Após confirmação, dados são persistidos como transação no banco de dados. |
| :---- |

## **5.1 Exemplo de Prompt Enviado ao Claude**

| // Prompt enviado ao Claude (simplificado) Analise o documento financeiro anexo e extraia: \- estabelecimento: string \- data: string (YYYY-MM-DD) \- valor\_total: number \- itens: \[{descricao, quantidade, valor}\] \- categoria\_sugerida: string Retorne SOMENTE JSON valido, sem texto adicional. |
| :---- |

## **5.2 Modelo de Dados Principal**

| Campo | Tipo | Descrição |
| :---- | :---- | :---- |
| **id** | UUID | Identificador único da transação |
| **tipo** | ENUM | RECEITA ou DESPESA |
| **valor** | DECIMAL | Valor monetário da transação |
| **data** | DATE | Data da ocorrência |
| **descricao** | TEXT | Descrição livre |
| **categoria\_id** | FK | Referência à categoria |
| **origem** | ENUM | MANUAL ou IA |
| **documento\_url** | TEXT | Caminho do arquivo original (opcional) |
| **criado\_em** | TIMESTAMP | Data de criação do registro |

# **6\. Integração com Telegram (Chatbot)**

O FinançasPessoais oferece um chatbot no Telegram como canal alternativo de interação. O objetivo é permitir que o usuário registre transações, consulte saldos e envie documentos diretamente pelo Telegram, sem precisar abrir o aplicativo principal — ideal para uso no dia a dia e em mobilidade.

## **6.1 Visão Geral da Integração**

| Como funciona O bot é criado via @BotFather no Telegram e recebe um token de acesso à API. O backend do FinançasPessoais registra um webhook para receber todas as mensagens enviadas ao bot. Cada usuário do sistema vincula sua conta ao Telegram por meio de um código de ativação único. O bot aceita comandos de texto, mensagens em linguagem natural e envio de imagens/PDFs. Mensagens em linguagem natural são processadas pelo Claude para extração de intenção e dados. |
| :---- |

## **6.2 Requisitos Funcionais — Módulo Telegram**

| ID | Descrição | Prioridade | Módulo |
| :---- | :---- | :---- | :---- |
| **RF-31** | O sistema deve permitir que o usuário vincule sua conta ao Telegram mediante código de ativação gerado no app. | **Alta** | Telegram |
| **RF-32** | O bot deve aceitar o comando /start para iniciar a vinculação ou exibir menu de ajuda. | **Alta** | Telegram |
| **RF-33** | O bot deve aceitar o comando /saldo para retornar o resumo financeiro do mês atual (receitas, despesas e saldo). | **Alta** | Telegram |
| **RF-34** | O bot deve aceitar o comando /extrato para listar as últimas 5 transações do usuário. | **Alta** | Telegram |
| **RF-35** | O bot deve aceitar mensagens em linguagem natural para registrar despesas (ex.: 'gastei 45 reais no almoço'). | **Alta** | Telegram |
| **RF-36** | O bot deve aceitar mensagens em linguagem natural para registrar receitas (ex.: 'recebi 3000 de salário hoje'). | **Alta** | Telegram |
| **RF-37** | O bot deve processar imagens e PDFs de faturas ou recibos enviados pelo usuário, usando o Claude para extração. | **Alta** | Telegram |
| **RF-38** | Após extração via IA, o bot deve solicitar confirmação do usuário antes de salvar a transação. | **Alta** | Telegram |
| **RF-39** | O bot deve enviar notificações proativas quando uma categoria ultrapassar 80% do orçamento definido. | **Média** | Telegram |
| **RF-40** | O bot deve enviar um resumo financeiro semanal automático toda segunda-feira de manhã. | **Média** | Telegram |
| **RF-41** | O bot deve aceitar o comando /ajuda listando todos os comandos disponíveis. | **Média** | Telegram |
| **RF-42** | O bot deve aceitar o comando /categorias para listar as categorias disponíveis. | **Média** | Telegram |
| **RF-43** | O bot deve permitir desvincular a conta com o comando /desconectar. | **Média** | Telegram |
| **RF-44** | O bot deve responder em no máximo 5 segundos para mensagens simples de texto. | **Alta** | Telegram |
| **RF-45** | O bot deve tratar mensagens ambíguas pedindo confirmação ou esclarecimento ao usuário. | **Média** | Telegram |

## **6.3 Comandos do Bot**

| Comando | Descrição | Exemplo de uso |
| :---- | :---- | :---- |
| **/start** | Inicia o bot e exibe instruções de vinculação. | */start* |
| **/saldo** | Exibe resumo financeiro do mês atual. | */saldo* |
| **/extrato** | Lista as últimas 5 transações. | */extrato* |
| **/categorias** | Lista as categorias disponíveis. | */categorias* |
| **/ajuda** | Exibe lista de todos os comandos. | */ajuda* |
| **/desconectar** | Desvincula a conta do Telegram. | */desconectar* |
| **Texto livre** | Registra despesa ou receita em linguagem natural. | *Gastei R$32 no mercado* |
| **Foto / PDF** | Envia fatura ou recibo para interpretação automática via IA. | *(enviar arquivo)* |

## **6.4 Casos de Uso — Telegram**

### **UC-08: Vincular Conta ao Telegram**

| UC-08 — Vincular Conta ao Telegram |  |
| :---- | :---- |
| **Identificador** | UC-08 |
| **Nome** | Vincular Conta ao Telegram |
| **Ator Principal** | Usuário |
| **Pré-condições** | Usuário autenticado no app web. Possui conta no Telegram. |
| **Fluxo Principal** | 1\. Usuário acessa Configurações \> Telegram no app web. 2\. Sistema gera um código de ativação de 6 dígitos com validade de 10 minutos. 3\. Usuário abre o bot @FinancasPessoaisBot no Telegram e envia /start. 4\. Bot solicita o código de ativação. 5\. Usuário envia o código. 6\. Sistema valida o código e vincula o chat\_id do Telegram à conta. 7\. Bot confirma a vinculação e exibe o menu de comandos. |
| **Fluxos Alternativos** | 5a. Código expirado: bot informa expiração e solicita gerar novo código no app. 5b. Código inválido: bot informa erro e permite nova tentativa (até 3 vezes). |
| **Pós-condições** | Conta vinculada. Usuário pode usar o bot para interagir com o sistema. |

### **UC-09: Registrar Despesa via Telegram (Linguagem Natural)**

| UC-09 — Registrar Despesa via Linguagem Natural no Telegram |  |
| :---- | :---- |
| **Identificador** | UC-09 |
| **Nome** | Registrar Despesa via Linguagem Natural no Telegram |
| **Ator Principal** | Usuário |
| **Pré-condições** | Conta vinculada ao Telegram (UC-08 concluído). |
| **Fluxo Principal** | 1\. Usuário envia mensagem de texto ao bot (ex.: 'Gastei 45 reais no almoço hoje'). 2\. Backend recebe a mensagem e envia ao Claude com contexto de extração de transação. 3\. Claude identifica: tipo=DESPESA, valor=45.00, descrição='Almoço', data=hoje, categoria sugerida='Alimentação'. 4\. Bot responde com resumo formatado e botões de confirmação: \[Confirmar\] \[Editar\] \[Cancelar\]. 5\. Usuário toca em \[Confirmar\]. 6\. Sistema salva a transação e bot confirma o registro. |
| **Fluxos Alternativos** | 3a. Claude não consegue extrair valor ou data: bot pede complemento ao usuário. 5a. Usuário toca \[Editar\]: bot solicita o campo a corrigir em nova mensagem. 5b. Usuário toca \[Cancelar\]: nenhuma transação é salva. |
| **Pós-condições** | Despesa registrada no sistema, visível no dashboard e relatórios. |

### **UC-10: Enviar Fatura pelo Telegram para Interpretação via IA**

| UC-10 — Enviar Fatura pelo Telegram para Interpretação via IA |  |
| :---- | :---- |
| **Identificador** | UC-10 |
| **Nome** | Enviar Fatura pelo Telegram para Interpretação via IA |
| **Ator Principal** | Usuário |
| **Pré-condições** | Conta vinculada ao Telegram. |
| **Fluxo Principal** | 1\. Usuário fotografa um recibo e envia a imagem ao bot (ou envia um PDF). 2\. Bot confirma recebimento e informa que está processando. 3\. Backend baixa o arquivo da API do Telegram e envia ao Claude para extração. 4\. Claude retorna: estabelecimento, data, valor total e itens. 5\. Bot exibe os dados extraídos com botões \[Confirmar\] \[Editar\] \[Cancelar\]. 6\. Usuário confirma. 7\. Sistema salva a transação com o documento vinculado. |
| **Fluxos Alternativos** | 3a. Arquivo corrompido ou ilegível: bot informa erro e solicita novo envio. 4a. Claude não consegue identificar dados financeiros: bot sugere cadastro manual. |
| **Pós-condições** | Transação salva com documento original anexado, acessível via app web. |

### **UC-11: Consultar Saldo pelo Telegram**

| UC-11 — Consultar Saldo pelo Telegram |  |
| :---- | :---- |
| **Identificador** | UC-11 |
| **Nome** | Consultar Saldo pelo Telegram |
| **Ator Principal** | Usuário |
| **Pré-condições** | Conta vinculada ao Telegram. |
| **Fluxo Principal** | 1\. Usuário envia /saldo. 2\. Sistema busca as transações do mês atual. 3\. Bot responde com mensagem formatada: total de receitas, total de despesas, saldo líquido e alerta de categorias acima do orçamento (se houver). |
| **Fluxos Alternativos** | 2a. Nenhuma transação no mês: bot informa saldo zerado e sugere registrar a primeira transação. |
| **Pós-condições** | Usuário visualiza panorama financeiro do mês diretamente no Telegram. |

## **6.5 Fluxo Técnico da Integração**

| Arquitetura do Bot Telegram 1\. Telegram envia evento via Webhook POST para o endpoint /telegram/webhook do backend. 2\. Backend autentica o chat\_id do remetente consultando a tabela de contas vinculadas. 3\. Backend classifica o tipo de mensagem: comando (/start, /saldo...), texto livre ou mídia (foto/PDF). 4\. Para texto livre e mídia: backend chama a API do Claude com o conteúdo e um system prompt específico. 5\. Claude retorna JSON estruturado com os dados extraídos. 6\. Backend monta a resposta e envia de volta ao usuário via Telegram Bot API (sendMessage / sendButtons). 7\. Após confirmação do usuário, backend persiste a transação no banco de dados. |
| :---- |

## **6.6 Notificações Proativas**

| Evento | Mensagem Enviada | Gatilho |
| :---- | :---- | :---- |
| **Orçamento em 80%** | *⚠️ Você já usou 80% do orçamento de \[Categoria\] este mês.* | Ao registrar transação |
| **Orçamento estourado** | *🚨 Limite de \[Categoria\] ultrapassado\! Gasto: R$X / Limite: R$Y.* | Ao registrar transação |
| **Resumo semanal** | *📊 Resumo da semana: Receitas R$X | Despesas R$Y | Saldo R$Z.* | Toda segunda-feira 8h |
| **Resumo mensal** | *📅 Fechamento de \[Mês\]: saldo final R$X. Veja o relatório completo no app.* | 1º dia do mês seguinte |

# **7\. Roadmap de Desenvolvimento Sugerido**

## **Fase 1 — MVP (4 a 6 semanas)**

* **RF-01, 02:** Autenticação básica (login/cadastro).

* **RF-05 a 09:** CRUD manual de transações (receitas e despesas).

* **RF-21:** Categorias padrão.

* **RF-26, 27:** Dashboard simples com totais do mês.

## **Fase 2 — IA e Orçamento (3 a 4 semanas)**

* **RF-12 a 16:** Upload e interpretação de documentos via Claude.

* **RF-23 a 25:** Definição de orçamento e alertas.

* **RF-17:** Preview de documento na tela de confirmação.

## **Fase 3 — Relatórios e Polimento (2 a 3 semanas)**

* **RF-29, 30:** Geração de relatórios e exportação.

* **RF-28:** Evolução de saldo histórico.

* **RF-10:** Busca e filtros avançados.

* **RF-22:** Categorias personalizadas.

## **Fase 4 — Integração Telegram (2 a 3 semanas)**

* **RF-31, 32:** Criação do bot e configuração do webhook.

* **RF-33, 34, 41, 42:** Comandos /saldo, /extrato, /ajuda, /categorias.

* **RF-35, 36:** Registro por linguagem natural (texto).

* **RF-37, 38:** Upload de documentos pelo bot com interpretação via IA.

* **RF-39, 40:** Notificações proativas (orçamento e resumo semanal).

| Controle de Versões do Documento v1.0 — Abril/2026: Versão inicial com visão, requisitos e casos de uso. v1.1 — Abril/2026: Adicionada seção 6 — Integração com Telegram (chatbot). |
| :---- |

