# Sistema de Organização Pessoal — Design v1

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
| effort | numérico | estimativa de esforço/tamanho |
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

## Algoritmo de priorização (v1, planejado para ser substituído depois)
Isolado num serviço próprio (`PriorityScorer`) para facilitar reformulação futura.

1. Items com `manual_priority` definida entram na lista ordenados por esse valor (controle manual total).
2. Demais Items entram por score automático:
   - `urgência`: cresce quanto mais perto/vencido o prazo; sem prazo = urgência neutra/baixa.
   - `facilidade`: itens de menor esforço ganham peso extra em empates de urgência (ajuda a destravar com vitórias rápidas).
   - `score = urgência * peso_prazo + facilidade * peso_esforço` (pesos configuráveis, com padrão sensato).
3. Só Items **folha sem filhos pendentes** entram na lista — o sistema sempre aponta a menor unidade acionável, nunca o "projeto" inteiro.

## Telas (frontend)

1. **Tela "Agora"** (tela inicial) — lista curta priorizada (3-5 itens), cada um com título, projeto pai (se houver), prazo, botão "começar" que já abre o cronômetro.
2. **Tela de Item** — título, descrição, prazo, esforço, prioridade manual; lista de sub-itens/passos com criação rápida de passo; cronômetro (iniciar/pausar, histórico de sessões, tempo total incluindo filhos).
3. **Criação rápida** — campos: título, descrição, prazo, esforço, prioridade manual; botão "Marcar como projeto" que salva e redireciona direto pra Tela de Item completa.
4. **Lista geral** — todos os Items, filtro por status/projeto, navegação fora da lista curta.

## Arquitetura técnica

- **Backend**: Laravel (modo API only), local, **sem autenticação** (uso pessoal single-user local).
- **Banco de dados**: SQLite (zero configuração, arquivo único).
- **Frontend**: React + Vite, buildado como **PWA** (instalável, sem barra de navegador — visual de app desktop).
- **Comunicação**: REST/JSON entre frontend e backend em localhost.
- **Caminho de evolução futura**: se precisar de recursos nativos (ícone na taskbar, notificações do SO), migrar a mesma base (frontend web + API) para Electron sem reescrever lógica — só trocar a "casca".

## Fluxo de desenvolvimento
- Projeto novo, do zero.
- Desenvolvimento orientado por agentes de código, com revisão/aprovação mínima do usuário.
