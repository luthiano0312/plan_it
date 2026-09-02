# Escopo — Planit

## In scope
- Criar e concluir tarefas com atributos que alimentam uma priorização automática (RF-001, RF-002, RF-012).
- Lista curta priorizada ("o que fazer agora") como tela central do sistema (RF-004).
- Lembretes (informações avulsas, não são tarefas) (RF-007, RF-008, RF-009).
- Hábitos com registro diário de cumprimento (RF-010, RF-011).

## Out of scope (nesta fase — candidatos a MVP posterior)
- Visão de calendário com distribuição automática de horários (RF-005, RF-006) — mecanismo ainda não está claro (ver `backlog.md`, seção "Perguntas em aberto"); avaliar depois do núcleo funcionando.
- Hierarquia de tarefas em sub-tarefas/projetos (RF-013) — não veio como pedido explícito no levantamento; entra só se o usuário confirmar a necessidade.
- Revisão espaçada / flashcards e protocolo fixo para "não sei o que fazer" — mencionados no relato, mas nunca formalizados como caso de uso; ficam de fora até virarem um requisito explícito.
- Múltiplos usuários / autenticação — sistema é de uso individual (RNF-003).

## Restrições de negócio
- Prazo: nenhum prazo externo — desenvolvimento pessoal, sem pressão contratual.
- Orçamento: nenhum orçamento formal — decisões de custo (ex.: hospedagem, se houver) ficam a critério do próprio usuário no Design.
- Equipe: 1 pessoa, acumulando os papéis de stakeholder e desenvolvedor.

## Restrições técnicas
- Infraestrutura existente: nenhuma — projeto novo, sem legado a manter compatibilidade.
- Compatibilidade com legado: não se aplica.
- Tecnologias já adotadas: nenhuma decisão de tecnologia foi tomada ainda — Design será refeito do zero (a base anterior, Laravel + SQLite + React PWA, está arquivada em `_archive/design-v1.md` só como referência histórica, sem obrigar reuso).

## Critérios de aceitação do escopo
- O usuário consegue criar uma tarefa em poucos passos e ela aparece corretamente posicionada na lista priorizada, sem precisar decidir manualmente a ordem.
- A lista priorizada nunca "esconde" uma tarefa pendente — o RNF-002 é validado na prática, não só no papel.
- Lembretes e hábitos funcionam como funcionalidades separadas da lista de tarefas, sem se misturar na priorização.
