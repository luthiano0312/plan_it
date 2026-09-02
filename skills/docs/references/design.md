# Design (incluindo System Design)

## Objetivo
Responder **como** o sistema vai atender ao que foi levantado e analisado — parte do SRS/ERS validado como insumo, sem reabrir o "o quê".

## Como fazer

**1. HLD antes de LLD.** Arquitetura geral primeiro (monolito, microsserviços, event-driven, escolha de tecnologias, fluxo de dados macro), detalhe de implementação depois (classes, schema físico, contratos de API, algoritmos).

**2. Partir sempre dos RNFs validados** (ver `requisitos-analise.md`), não de preferência tecnológica da equipe — um RNF como "disponibilidade 99,9%" vira decisão concreta de arquitetura (replicação, load balancer, multi-região). O RNF nasce na Análise e "morre" (é resolvido) no Design.

**3. Validar cada decisão contra restrições já identificadas na Análise** (legado, orçamento, prazo, equipe).

**4. Formalizar contratos de API e schemas antes da implementação começar**, para times paralelos trabalharem sem bloqueio.

**5. Aprofundar em System Design só quando a escala justificar.** System Design é HLD levado a sério, não uma fase separada. Temas: escalabilidade (vertical vs. horizontal), disponibilidade/tolerância a falha, balanceamento de carga, cache, filas/processamento assíncrono, trade-offs de consistência (teorema CAP), sharding, estimativas de capacidade.
- Sistema pequeno/interno, baixo volume: HLD básico já resolve; aprofundar seria over-engineering.
- Sistema com crescimento esperado, alto tráfego, natureza distribuída: aprofundamento se justifica e evita reescrever a arquitetura depois.

## Boas práticas
- Documentar o **porquê** das decisões (ADR — ver `adr.md`), não só o resultado final.
- Validar decisões contra RNFs, não só contra RFs.
- Prototipar/testar decisões arriscadas antes de comprometer o sistema inteiro (spike técnico).
- Explicitar trade-offs — toda decisão de Design troca uma coisa por outra (ex: mais consistência custa latência).
- Revisitar a arquitetura em checkpoints, não só uma vez no início.

## Problemas comuns

| Problema | Por que evitar |
|---|---|
| Over-engineering (desenhar pra escala que o sistema nunca vai ter) | Complexidade desnecessária, mais custo de manutenção, sem benefício real |
| Under-engineering (ignorar RNFs de escala já conhecidos) | Funciona no MVP, quebra quando o uso real cresce — retrabalho caro depois |
| Pular direto pra tecnologia antes de entender o problema | Escolher stack antes de entender RNFs reais leva a decisões que não se sustentam |
| Ignorar restrições da Análise (legado, orçamento, equipe) | Design "ideal" mas inviável de implementar não serve pra nada |
| Não documentar o porquê das decisões | Decisões futuras contradizem escolhas já feitas por bons motivos, ou refazem análises já feitas |

## O que produzir e onde vai

| Documento | Obrigatório quando | Vai em |
|---|---|---|
| Documento de Arquitetura (HLD) | sempre | `docs/02-design/arquitetura-hld.md` |
| System Design (aprofundamento) | sistemas com escala/tráfego relevante | `docs/02-design/system-design.md` |
| Design Detalhado (LLD) — schema físico | sempre que houver banco de dados | `docs/02-design/modelo-de-dados.md` |
| Especificação de APIs (OpenAPI/Swagger) | integração entre serviços/times | `docs/02-design/apis/` |
| ADRs | decisão difícil de reverter ou de alto impacto | `docs/02-design/adr/` — ver `adr.md` |
| Design de UI/UX detalhado | interface relevante | `docs/02-design/ui-ux/` |
