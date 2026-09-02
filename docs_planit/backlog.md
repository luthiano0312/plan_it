# Backlog de Requisitos — Planit

> Fonte: `levantamento-bruto.md` (entrevista informal, levantamento inicial).
> Formato: Backlog com RF/RNF classificados (projeto individual, sem contrato/regulação — não se justifica um SRS formal completo).

## 1. Visão geral
Sistema pessoal de organização de tarefas, hábitos e lembretes, com foco em reduzir dois atritos identificados no levantamento: (1) não saber por onde começar e (2) falta de vontade de agir mesmo sabendo o que fazer. A resposta principal do sistema é priorizar automaticamente e apontar sempre a próxima ação, para tirar do usuário a decisão de "o que fazer agora".

## 2. Requisitos Funcionais (RF)

| ID     | Descrição                                                                                                              | Prioridade (MoSCoW) | Origem / rastreabilidade                                                                                                 |
| ------ | ---------------------------------------------------------------------------------------------------------------------- | ------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| RF-001 | Criar uma tarefa/atividade com atributos que alimentam a priorização (ex.: prazo, esforço/dificuldade, tempo estimado) | Must                | "criar uma task... o sistema automaticamente saber como priorizar"                                                       |
| RF-002 | Calcular automaticamente uma prioridade para cada tarefa a partir desses atributos                                     | Must                | idem                                                                                                                     |
| RF-003 | Permitir sobrepor manualmente a prioridade calculada pelo sistema                                                      | Should              | inferido — necessário para o usuário corrigir o sistema quando o cálculo automático erra; **validar**                    |
| RF-004 | Exibir uma lista curta e priorizada das tarefas a fazer (visão "o que fazer agora")                                    | Must                | "lista com as tarefas diárias com uma ordem de prioridade"                                                               |
| RF-005 | Exibir uma visão tipo calendário (dia/semana/mês) com as atividades                                                    | Could               | "uma visão do dia/semana/mês, como a do google calendar" — **validar se é MVP ou evolução**                              |
| RF-006 | Distribuir automaticamente as atividades em horários dentro da visão de calendário                                     | Could               | "com os horários e as atividades... feito automaticamente" — mecanismo não está claro, ver "Perguntas em aberto"         |
| RF-007 | Criar um lembrete: informação avulsa/efêmera que não é uma tarefa                                                      | Must                | "funcionalidade de lembretes... não necessariamente são uma tarefa"                                                      |
| RF-008 | Associar um lembrete a uma data (quando aplicável)                                                                     | Should              | "tipo lembrar de uma data"                                                                                               |
| RF-009 | Deixar de exibir/arquivar um lembrete depois que ele perde relevância                                                  | Should              | "depois que passar eu não preciso mais ser lembrada, algo passageiro"                                                    |
| RF-010 | Registrar um hábito e marcar, dia a dia, se foi cumprido ou não                                                        | Must                | "habit tracker... colocar o que eu fiz ou deixei de fazer"                                                               |
| RF-011 | Visualizar a continuidade/histórico de um hábito ao longo do tempo                                                     | Should              | "ajude na parte de continuidade/manutenção de hábitos"                                                                   |
| RF-012 | Marcar uma tarefa (ou passo de uma tarefa) como concluída                                                              | Must                | implícito em qualquer sistema de tarefas — **validar**                                                                   |
| RF-013 | Organizar tarefas maiores em sub-tarefas/passos (hierarquia)                                                           | Could               | inferido do relato sobre projetos com múltiplas etapas — **não está explícito no levantamento, validar se entra no MVP** |

## 3. Requisitos Não-Funcionais (RNF)

| ID | Categoria | Descrição | Prioridade |
|---|---|---|---|
| RNF-001 | Usabilidade | Registrar uma tarefa deve exigir o mínimo de campos e cliques possível (baixo atrito de entrada) — atrito alto foi apontado como causa central da procrastinação | Must |
| RNF-002 | Confiabilidade | Nenhuma tarefa pode "sumir" de vista por causa de tarefas mais urgentes aparecendo — é a causa de esquecimento relatada | Must |
| RNF-003 | Uso individual | Sistema para 1 usuário; não há requisito de autenticação/multiusuário nesta fase | Must |
| RNF-004 | Disponibilidade/acesso | Em aberto: local-only (uso num único dispositivo) ou acessível de qualquer lugar? Não foi dito no levantamento — decisão pendente que impacta bastante o Design | A decidir |

## 4. Regras de negócio (preliminares)

| ID | Regra |
|---|---|
| RN-001 | A prioridade de uma tarefa é derivada de mais de um fator (ex.: urgência do prazo, esforço/dificuldade); a combinação exata desses fatores é uma decisão de Design, não está fechada aqui |
| RN-002 | Um lembrete não é uma tarefa: não entra na lista priorizada de "o que fazer", é só uma informação a reter |

## 5. Casos de uso (informal — único ator: o usuário)

1. **Registrar tarefa** — usuário informa título e os atributos disponíveis; sistema posiciona a tarefa na lista priorizada automaticamente.
2. **Consultar o que fazer agora** — usuário abre o sistema e vê a lista curta já ordenada, sem precisar decidir por onde começar.
3. **Registrar lembrete** — usuário anota algo a lembrar, com ou sem data; o lembrete some sozinho quando deixa de ser relevante.
4. **Registrar hábito do dia** — usuário marca se cumpriu ou não um hábito; sistema mantém o histórico.
5. **Concluir tarefa** — usuário marca a tarefa como feita e ela some da lista priorizada.

Com um único ator e fluxos simples, não há necessidade de diagramas UML formais de caso de uso nesta fase (ver critério em `requisitos-analise.md`) — a lista acima já é suficiente.

## 6. Perguntas em aberto (levantar antes de fechar o Design)
- Qual o algoritmo exato de priorização (pesos entre urgência, esforço, tempo estimado)?
- Como funciona, na prática, a distribuição automática de horários na visão de calendário (RF-006)? É MVP ou fica para depois?
- Como o usuário é notificado de um lembrete ou hábito pendente — só dentro do app, ou notificação do sistema operacional?
- Hábitos têm frequência configurável (diária, dias específicos da semana) ou só diária no MVP?
- Hierarquia de tarefas (RF-013) e revisão espaçada/flashcards (mencionados no relato, mas nunca como pedido direto) entram no MVP ou ficam de fora, como no design anterior arquivado?
- RNF-004: o sistema roda só localmente ou precisa ser acessível de outro dispositivo/lugar?

## 7. Rastreabilidade
Todos os RF/RNF acima remontam a trechos do levantamento bruto em `levantamento-bruto.md`. Por ser um backlog pessoal (não um SRS regulado), não há matriz de rastreabilidade separada — a coluna "Origem" nas tabelas acima cumpre esse papel.
