# PlanIt v1 — Spec de Implementação

Status: aprovado para plano de implementação
Data: 2026-08-25
Fonte original: docs_planit/design-v1.md

## Problema

- Múltiplas frentes de organização sobrepostas: tarefas, escola, anotações, revisão espaçada, hábitos, projetos, rotina, proatividade.
- Dois sintomas centrais:
  1. **Não saber por onde começar** (paralisia de decisão ao sentar pra trabalhar).
  2. **Falta de vontade mesmo já sabendo o que fazer** (provável sintoma de atrito alto, não preguiça).
- Hipótese de solução: **reduzir atrito** + **ter protocolos** (não precisar decidir do zero toda vez).

## Escopo do v1

Construir apenas o núcleo: **Items (tarefas/projetos) + lista curta priorizada + quebra em passos + cronômetro**.

Fora do v1 (evoluir depois em cima dessa base): hábitos, rotinas, anotações, revisão espaçada, protocolo fixo de "não sei o que fazer".

## Modelo de dados

### Item

| Campo | Tipo | Notas |
|---|---|---|
| title | string | obrigatório |
| description | text | opcional |
| parent_id | nullable FK (self) | define hierarquia (projeto = tem filhos) |
| due_date | nullable date | |
| effort | int (1–5) | estimativa de esforço/tamanho; 1 = trivial, 5 = grande |
| manual_priority | nullable numérico | quando definido, sobrepõe o score automático |
| status | enum | pendente / em_andamento / concluído |
| completed_at | nullable datetime | |

- **Tarefa** = Item sem filhos (folha).
- **Projeto** = Item com filhos.
- Não há tabela separada para "passo" — um passo é só um Item filho.

### TimeEntry

| Campo | Tipo | Notas |
|---|---|---|
| item_id | FK | |
| started_at | datetime | |
| ended_at | nullable datetime | |

Tempo total de um projeto = soma dos TimeEntry de todos os descendentes.

**Regra de exclusividade:** só pode existir **um TimeEntry aberto (`ended_at IS NULL`) por vez em todo o sistema**. Iniciar o cronômetro em um Item fecha (seta `ended_at = now()`) qualquer TimeEntry aberto em outro Item automaticamente.

## Regras de transição de status

- Iniciar o cronômetro em um Item muda seu `status` para `em_andamento` automaticamente (se ainda estiver `pendente`).
- Quando **todos os filhos diretos** de um Item-projeto atingem `status = concluído`, o próprio projeto é marcado `concluído` automaticamente (`completed_at = now()`).
- Marcar um Item como `concluído` manualmente sempre é permitido, independente do estado dos filhos.
- Essas transições automáticas devem ser centralizadas num único ponto do domínio (não espalhadas por controllers), para manter a lógica testável e substituível.

## Algoritmo de priorização (v1, planejado para ser substituído depois)

Isolado num serviço próprio (`PriorityScorer`) para facilitar reformulação futura.

1. Items com `manual_priority` definida entram na lista ordenados por esse valor (controle manual total, ignora o score automático).
2. Demais Items entram por score automático:
   - **Urgência**: função linear da proximidade do prazo, com **saturação** num teto quando o prazo já venceu (um item vencido há 1 dia e um vencido há 30 dias têm a mesma urgência máxima — não cresce sem limite). Sem prazo = urgência neutra/baixa (valor fixo baixo, não zero, pra não sumir da lista pra sempre).
   - **Facilidade**: inversamente proporcional ao `effort` (1 = mais fácil, 5 = mais difícil); dá peso extra a itens de menor esforço em empates de urgência, pra ajudar a destravar com vitórias rápidas.
   - `score = urgência * peso_prazo + facilidade * peso_esforço` (pesos configuráveis, com padrão sensato definido no serviço, não hardcoded espalhado).
3. Só Items **folha sem filhos pendentes** entram na lista — o sistema sempre aponta a menor unidade acionável, nunca o "projeto" inteiro. (Um Item folha sem filho algum já se qualifica trivialmente.)

## Telas (frontend)

1. **Tela "Agora"** (tela inicial) — lista curta priorizada (3–5 itens), cada um com título, projeto pai (se houver), prazo, botão "começar" que já abre o cronômetro.
2. **Tela de Item** — título, descrição, prazo, esforço, prioridade manual; lista de sub-itens/passos com criação rápida de passo; cronômetro (iniciar/pausar, histórico de sessões, tempo total incluindo filhos).
3. **Criação rápida** — campos: título, descrição, prazo, esforço, prioridade manual; botão "Marcar como projeto" que salva e redireciona direto pra Tela de Item completa.
4. **Lista geral** — todos os Items, filtro por status/projeto, navegação fora da lista curta.

## Arquitetura técnica

- **Backend**: Laravel (modo API only), última versão estável no momento do build, local, **sem autenticação** (uso pessoal single-user local).
- **Banco de dados**: SQLite (zero configuração, arquivo único).
- **Frontend**: React + Vite, buildado como **PWA** (instalável, sem barra de navegador — visual de app desktop). Node LTS.
- **Comunicação**: REST/JSON entre frontend e backend em localhost.
- **Caminho de evolução futura**: se precisar de recursos nativos (ícone na taskbar, notificações do SO), migrar a mesma base (frontend web + API) para Electron sem reescrever lógica — só trocar a "casca".

## Estratégia de testes

- Desenvolvimento orientado por TDD (skill `test-driven-development`) para a lógica de domínio com regras reais:
  - `PriorityScorer` (score automático, saturação de urgência, ordenação por `manual_priority`).
  - Transições automáticas de status (cronômetro → em_andamento; filhos concluídos → projeto concluído).
  - Regra de exclusividade do TimeEntry (fechar o anterior ao abrir um novo).
  - Cálculo de tempo total de projeto (soma recursiva de descendentes).
- Endpoints REST cobertos por testes de feature (request → resposta/efeito no banco), sem necessidade de cobertura exaustiva de casos triviais de CRUD.
- Frontend React: testes leves focados em comportamento (ex: "começar" abre cronômetro, lista curta reordena), não em cobertura de UI pixel a pixel.

## Fluxo de desenvolvimento

- Projeto novo, do zero.
- Desenvolvimento orientado por agentes de código, com revisão/aprovação mínima do usuário.
- Ordem sugerida (a detalhar no plano de implementação): modelo de dados + migrations → `PriorityScorer` isolado (TDD) → regras de status/TimeEntry (TDD) → API REST → frontend (telas 1–4) → empacotamento PWA.
