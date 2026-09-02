---
name: docs
description: Guia como conduzir e documentar cada fase do ciclo de desenvolvimento de software (Levantamento de Requisitos, Análise, Design/System Design) e os processos contínuos de Verificação/Validação e Gestão de Requisitos, incluindo estrutura de pastas, ADRs (com archive/ para evitar context rot em agentes de código) e convenção de CLAUDE.md/AGENTS.md. Use esta skill sempre que o usuário pedir para documentar um projeto de software, estruturar uma pasta docs/, escrever um SRS/backlog/documento de arquitetura/ADR, decidir se algo precisa virar documento formal, configurar um CLAUDE.md/AGENTS.md, auditar a documentação existente de um projeto, ou pedir ajuda para conduzir levantamento/análise de requisitos — mesmo que o usuário não use os termos "SDLC" ou "engenharia de requisitos" explicitamente (ex: "ajuda a organizar a documentação desse repo", "como estruturo isso pro agente de código entender o projeto", "preciso registrar por que escolhemos esse banco de dados").
---

# SDLC Docs

Skill para conduzir e documentar as fases do ciclo de desenvolvimento de software, com estrutura pensada para ser lida tanto por humanos quanto por agentes de código.

## Como usar esta skill

Não carregue todas as referências de uma vez. Primeiro identifique que tipo de tarefa é essa, depois abra só o(s) arquivo(s) relevante(s).

### Passo 1 — Isso é trabalho novo ou manutenção de algo que já existe?

- **Trabalho novo** (descobrir ou organizar algo que ainda não existe) → vá para o Passo 2.
- **Manutenção** (um requisito mudou, algo precisa ser revalidado, uma decisão foi substituída, uma auditoria foi pedida) → abra `references/gestao-continua.md` diretamente. Não é uma "fase" — pode acontecer a qualquer momento do projeto.

### Passo 2 — Em qual fase pontual isso cai?

| Se o usuário quer... | Abra |
|---|---|
| Descobrir/coletar requisitos (entrevista, questionário, backlog inicial, "o que o sistema precisa fazer") | `references/requisitos-levantamento.md` |
| Organizar/classificar requisitos já coletados (RF/RNF, escopo, regras de negócio, casos de uso formais) | `references/requisitos-analise.md` |
| Definir arquitetura, HLD/LLD, System Design, schema de banco, contratos de API | `references/design.md` |
| Registrar uma decisão arquitetural (ADR) | `references/adr.md` |
| Configurar o arquivo de entrada do projeto para agente de código | `references/claude-md-agents.md` |
| Criar a estrutura de pastas `docs/` de um projeto do zero | rode `scripts/scaffold_docs.sh`, depois siga para a fase relevante acima |
| Decidir se algo específico precisa virar documento formal | use o checklist na seção "Checklist rápido" abaixo, sem precisar abrir mais nada |
| Auditar a documentação existente de um projeto | veja "Modo auditoria" abaixo |

Cada `references/*.md` segue a mesma estrutura: Objetivo → Como fazer → Boas práticas → Problemas comuns → O que produzir e onde vai (com tabela de obrigatoriedade). Templates prontos ficam em `assets/templates/`.

## Estrutura de pastas do projeto (resultado final)

```
CLAUDE.md / AGENTS.md
docs/
├── 01-requisitos/   (stakeholders, srs/backlog, casos-de-uso/, regras-de-negocio, glossario, escopo, rastreabilidade)
├── 02-design/        (arquitetura-hld, system-design, modelo-de-dados, apis/, ui-ux/, adr/ com adr/archive/)
├── how-to/
├── reference/
└── explanation/
```

Detalhes de cada arquivo estão nas referências de fase — não repita a estrutura completa aqui além desse resumo.

## Princípio geral (aplica em qualquer fase)

Todo entregável existe para reduzir risco de mal-entendido ou retrabalho. Se o risco que ele mitiga é baixo no contexto do projeto, simplifique ou elimine — documentação por documentação não agrega valor. As tabelas de obrigatoriedade em cada `references/*.md` são critério de decisão, não checklist fixo a cumprir sempre.

## Checklist rápido — algo vira documento formal?

1. Se essa informação se perder, causa retrabalho ou mal-entendido caro? → documentar.
2. É uma decisão difícil de reverter? → ADR (`references/adr.md`).
3. Vários times vão consultar isso de forma assíncrona? → documento formal.
4. Existe exigência contratual/regulatória/de auditoria? → formal é obrigatório.
5. É manutenção de algo que já existe (mudou, precisa revalidação)? → não cria documento novo, vai pro Passo 1 acima (`gestao-continua.md`), atualiza o existente.
6. Nenhum dos anteriores, time pequeno/ágil? → pode viver como backlog/comentário/ADR curto.

## Modo auditoria

Quando o usuário pedir para revisar a documentação de um projeto existente, verifique nesta ordem e reporte só o que estiver fora do esperado (não liste o que já está certo):

1. Existe `CLAUDE.md`/`AGENTS.md` na raiz? Ele aponta explicitamente para `docs/02-design/adr/` com a instrução de não carregar `archive/` por padrão? (ver `references/claude-md-agents.md`)
2. A pasta `docs/02-design/adr/` tem ADRs com `Status: Substituído por ADR-XXXX` que **não** foram movidos para `archive/`? Isso é o problema de context rot descrito em `references/adr.md`.
3. Existe SRS **e** backlog ao mesmo tempo cobrindo a mesma coisa? Isso é duplicação de fonte de verdade — sinalizar.
4. Existem requisitos sem RF/RNF classificado, ou sem ligação na matriz de rastreabilidade, num projeto que já tem matriz?
5. Existem decisões arquiteturais relevantes (troca de banco, framework, monólito/microsserviços) sem ADR correspondente?

Use `references/gestao-continua.md` para o racional de verificação/validação contínua ao montar esse diagnóstico.
