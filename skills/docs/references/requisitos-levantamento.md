# Levantamento (Elicitação) de Requisitos

## Objetivo
Entender **o problema** antes de pensar na solução. Responder "o que o sistema precisa fazer e por quê?" — não "como ele vai fazer".

## Como fazer — técnicas

| Técnica | Quando usar | Observação |
|---|---|---|
| **Entrevistas** | Aprofundar em pontos específicos | Estruturadas ou semiestruturadas; ótimas para esclarecer ambiguidade na hora |
| **Questionários** | Muitos usuários espalhados geograficamente | Escala bem, mas é menos profundo que entrevista |
| **Observação direta / Etnografia** | Entender o processo real de trabalho | Revela problemas que o próprio usuário nem menciona, por já ser "natural" pra ele |
| **Workshops / JAD** | Alinhar visões divergentes rapidamente | Reúne múltiplos stakeholders ao mesmo tempo |
| **Análise de Documentos** | Sistemas legados, processos já documentados | Manuais, relatórios, planilhas em uso |
| **Protótipos** | Requisitos de interface, descoberta de necessidades ocultas | Nessa fase servem para **descobrir** requisitos |
| **Casos de Uso** | Extrair cenários de interação | Aqui funcionam como **técnica de elicitação**, não como artefato formal ainda |
| **User Stories / Storytelling** | Métodos ágeis | Formato: "Como [usuário], eu quero [ação] para que [benefício]" |

Passos: identificar stakeholders → coletar (técnicas acima) → negociar prioridades preliminarmente → documentar inicialmente → validar com stakeholders → começar a gerir mudanças desde já (ver `gestao-continua.md`).

Combine mais de uma técnica sempre que possível — cada uma tem pontos cegos diferentes (ex: entrevista + observação direta).

## Boas práticas
- Envolver o **usuário final**, não só o gestor — quem opera vê necessidades que a liderança não vê.
- Registrar requisitos assim que forem ditos, mesmo brutos — não confiar na memória.
- Validar continuamente com o stakeholder, em vez de guardar tudo para o final.

## Problemas comuns

| Problema | Por que evitar |
|---|---|
| Requisitos implícitos (cliente acha "óbvio" e não fala) | Equipe técnica não tem como saber; descoberto tarde, custa caro |
| Conflito entre stakeholders não resolvido | Retrabalho quando setores diferentes descobrem que pediram coisas incompatíveis |
| Requisitos voláteis sem controle | Scope creep — cronograma/orçamento saem do controle |
| Excesso de detalhe técnico cedo demais | Foco deve ser o *problema*; antecipar tecnologia engessa decisões que são do Design |
| Vago demais para ser útil | Requisito impreciso não é testável |
| Falta de envolvimento do usuário final | Requisitos "de gabinete" não refletem a realidade operacional |

## O que produzir e onde vai

| Documento | Obrigatório quando | Vai em | Template |
|---|---|---|---|
| SRS **ou** Backlog de User Stories (não os dois) | contrato/licitação, sistema regulado, terceirização → SRS; time ágil → Backlog | `docs/01-requisitos/srs.md` ou `backlog.md` | `assets/templates/srs.md` |
| Registro de stakeholders | quase sempre, mesmo informal | `docs/01-requisitos/stakeholders.md` | — |
| Diagramas de Casos de Uso (elicitação) | muitos atores, fluxos complexos | `docs/01-requisitos/casos-de-uso/` | — |
| Protótipos | interface complexa ou incerteza de UX | `docs/02-design/ui-ux/` (versão validada) | — |

Se a decisão for "isso precisa mesmo virar documento formal?", volte ao checklist no `SKILL.md` antes de gerar qualquer arquivo.
