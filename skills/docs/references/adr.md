# ADR (Architecture Decision Record)

## O que é
Documento curto que registra **uma decisão arquitetural importante**, o **contexto** que levou a ela, e as **consequências** de tê-la tomado. Existe porque decisões técnicas relevantes tendem a ser esquecidas — quem tomou a decisão sai do time, e alguém no futuro (humano ou agente de código) perde tempo reabrindo uma discussão já resolvida, ou refazendo o mesmo erro já descartado.

## Quando criar um
Só decisões **difíceis de reverter** ou de **alto impacto arquitetural**: escolha de banco de dados, escolha de linguagem/framework, dividir um monólito, síncrono vs. assíncrono numa comunicação crítica. Trocar o nome de uma variável não precisa de ADR; trocar de REST pra GraphQL, sim.

## Estrutura fixa e curta
```markdown
# ADR-0003: Usar PostgreSQL para o serviço de pedidos

## Status
Aceito

## Contexto
O serviço de pedidos precisa de transações atômicas para garantir
que estoque e pagamento sejam atualizados juntos...

## Decisão
Vamos usar PostgreSQL como banco principal do serviço de pedidos.

## Consequências
- Positivo: transações ACID nativas
- Negativo: schema mais rígido, migrações precisam de mais cuidado
```
Template completo em `assets/templates/adr.md`.

## Regras
- **Numerado sequencialmente**: `0001-titulo.md`, `0002-titulo.md` — cria um histórico cronológico.
- **Imutável quando aceito.** Decisão mudou? Cria-se um **novo** ADR que referencia o antigo (`Status: Substituído por ADR-0015`). O conteúdo do ADR antigo nunca é reescrito — só o campo `Status` é atualizado antes de arquivar.
- Vive versionado junto do código, em `docs/02-design/adr/`.
- É a ponte mais direta entre RNF (levantado na Análise) e decisão técnica concreta (tomada no Design) — vale linkar de volta ao RNF que motivou, quando fizer sentido pra rastreabilidade.

## Arquivamento de ADRs substituídos (`adr/archive/`)

Em workflows com agente de código, deixar ADRs ativos e substituídos misturados na mesma pasta gera **context rot**: o agente carrega decisões descartadas junto com as válidas, e pode agir sobre uma decisão que já foi rejeitada. A separação:

- `docs/02-design/adr/` → só ADRs com `Status: Aceito` (ativos)
- `docs/02-design/adr/archive/` → ADRs com `Status: Substituído por ADR-XXXX`

Regras para isso funcionar de verdade:

1. **O número original nunca muda** ao mover um ADR para `archive/` — a ordem cronológica é o valor principal do ADR.
2. **Atualizar o `Status` antes de mover.** O conteúdo continua imutável; o `Status` é justamente o registro de que foi substituído — isso é metadado, não reescrita da decisão.
3. **O vínculo é bidirecional**: o novo ADR referencia o antigo (`Substitui ADR-0003`) e o antigo aponta pro novo (`Substituído por ADR-0015`).
4. **A separação de pastas só evita context rot se o carregamento respeitar isso.** Se o `CLAUDE.md`/`AGENTS.md` mandar carregar `adr/` de forma recursiva, `archive/` entra junto e a separação não ajuda em nada — a regra de carregamento precisa estar escrita explicitamente lá (ver `claude-md-agents.md`).

## Ao criar um ADR nesta skill
1. Verifique se já existe um ADR ativo sobre o mesmo tópico em `docs/02-design/adr/`. Se existir e a decisão mudou: atualize o `Status` do antigo, mova-o para `archive/` mantendo o número, e crie o novo com referência bidirecional.
2. Se não existir, use o próximo número sequencial disponível (olhando os arquivos já existentes em `adr/` e `adr/archive/` juntos, já que o número é único no histórico inteiro do projeto, não só nos ativos).
3. Preencha Status/Contexto/Decisão/Consequências — não pule Consequências, incluindo os pontos negativos/trade-offs, não só os positivos.
