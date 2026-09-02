# CLAUDE.md

> Roteiro sempre carregado no início da sessão. Mantenha curto e denso — não copie a documentação detalhada pra cá, aponte pra ela.

## Sobre o projeto
Planit é um sistema pessoal (uso individual) de organização: tarefas com priorização automática, lembretes e hábitos. Objetivo central é reduzir o atrito de decidir "o que fazer agora" (ver `docs/01-requisitos/backlog.md`).

> Projeto ainda na fase de Requisitos — Design (arquitetura, stack, modelo de dados) será feito do zero e ainda não existe. Não assuma a stack do `_archive/design-v1.md`, que é só histórico.

## Convenções
- Estilo de código: [linter/formatter usado, ex: `eslint` + `prettier`, config em `.eslintrc`]
- Padrão de commit: [ex: Conventional Commits — `feat:`, `fix:`, `chore:`]
- Branch: [ex: `feature/`, `fix/`, PR obrigatório antes de merge em `main`]

## Comandos
- Rodar testes unitários: `[comando exato]`
- Rodar testes de integração: `[comando exato]`
- Build local: `[comando exato]`
- Subir ambiente local: `[comando exato]`

## Documentação — onde procurar
- Arquitetura geral: `docs/02-design/arquitetura-hld.md`
- Modelo de dados: `docs/02-design/modelo-de-dados.md`
- Decisões arquiteturais (ADRs): `docs/02-design/adr/*.md` — **carregue apenas estes por padrão, não recursivo**
- Histórico de decisões substituídas: `docs/02-design/adr/archive/` — **só abra aqui se for investigar por que uma abordagem anterior foi descartada, ou antes de propor uma mudança arquitetural grande**
- Requisitos: `docs/01-requisitos/srs.md` (ou `backlog.md`)
- Guias operacionais: `docs/how-to/`

## Antes de propor uma mudança arquitetural
Verifique `docs/02-design/adr/` primeiro — a decisão pode já ter sido tomada e documentada com o motivo.
