# Arquivo de entrada (`CLAUDE.md` / `AGENTS.md`)

## Função
Ser o **roteiro sempre carregado** que diz ao agente de código onde procurar o quê — o equivalente ao README para agentes, mas escrito pra ser lido em toda sessão, por isso precisa ser curto e denso, não uma cópia da documentação detalhada.

## O que deve conter
- **Convenções do projeto de forma explícita e literal.** Humanos inferem contexto ("siga o padrão do projeto"); agentes seguem o que está escrito. Escreva o padrão exato ou linke pro arquivo que o define.
- **Comandos executáveis, não descrições vagas.** `npm run test:unit` é melhor que "rode os testes unitários".
- **Links diretos** para `docs/02-design/arquitetura-hld.md`, `docs/02-design/adr/`, `docs/how-to/`.
- **Aviso para checar ADRs antes de propor mudança arquitetural relevante** — evita o agente "reinventar" uma decisão já descartada.
- **Regra explícita de carregamento dos ADRs**: por padrão só `docs/02-design/adr/*.md` (ativos); `docs/02-design/adr/archive/` só quando for investigar uma decisão passada ou antes de propor mudança arquitetural grande. Sem essa linha explícita, a separação em `archive/` não impede o agente de carregar tudo mesmo assim — ver `adr.md` para o porquê.

## O que NÃO fazer
- Não duplicar conteúdo que já está em `docs/` — isso cria duas fontes de verdade que podem divergir, e um agente não "desconfia" da divergência do jeito que um humano desconfiaria.
- Não deixar instruções implícitas ("siga boas práticas") — para um agente, isso não é uma instrução acionável.

## Template
Ver `assets/templates/claude-md.md` para uma estrutura de partida.

## Não substitui a documentação detalhada
É o índice que evita que a fragmentação — boa pra humanos, que navegam por estrutura — vire um problema de descoberta pra um agente, que geralmente lê via grep/busca ou carrega arquivos inteiros pro contexto sem "sentir" a estrutura de pastas como um humano sentiria.
