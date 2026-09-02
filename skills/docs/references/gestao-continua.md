# Processos Contínuos — Especificação, Verificação/Validação, Gestão de Requisitos

Diferente das fases pontuais (`requisitos-levantamento.md`, `requisitos-analise.md`, `design.md`), isto não é "uma fase". É trabalho acionado **no início** (linha de base), **durante** (mudança de requisito, ADR substituído, requisito esquecido descoberto tarde) e **depois** (auditoria, manutenção) do projeto.

Use esta referência quando a tarefa for sobre algo que **já existe** e precisa ser checado, atualizado ou revalidado — não sobre descobrir/organizar algo novo.

## Especificação formal
Consolidação contínua em documentos padronizados — StRS (Stakeholder Requirements Specification), SyRS (System Requirements Specification), SRS (Software Requirements Specification), ConOps/OpsCon (Concept of Operations). Não é um evento único: é revisitada toda vez que o entendimento do problema muda.

## Verificação e Validação
- **Verificação**: os requisitos especificados estão corretos e seguem os critérios de qualidade (necessário, não ambíguo, completo, singular, factível, verificável, correto, conforme — ver `requisitos-analise.md`)?
- **Validação**: eles de fato atendem à necessidade real do stakeholder? Conecta com os Testes de Aceitação lá na frente do ciclo.

Aciona-se a qualquer momento — não só uma vez no fim da Análise. É a atividade certa quando a pergunta é "esse requisito que já existe ainda está bom?", não "que requisito novo existe?".

## Gestão de Requisitos
Processo contínuo de controlar mudanças, manter baseline e rastreabilidade **ao longo de todo o projeto** — não termina quando a codificação começa. Cenários típicos:

- **Um requisito muda no meio do projeto** → atualizar SRS/backlog + matriz de rastreabilidade, registrar o motivo da mudança.
- **O Design revela requisito mal especificado ou incompleto** → volta pontual à Análise; isso é normal, só em cascata puro é visto como falha de processo.
- **Um ADR é substituído** → ver `adr.md`; o gatilho de "por que essa decisão mudou" costuma vir de uma mudança de requisito/RNF capturada aqui.

## Onde vive
Atualiza os mesmos arquivos das fases pontuais (`srs.md`, `rastreabilidade.md`, `escopo.md`) em vez de criar arquivos novos — o processo contínuo é sobre **manter vivo** o que já existe, não sobre gerar um documento à parte.

## Uso no modo auditoria
Ao auditar um projeto (ver seção "Modo auditoria" do `SKILL.md`), esta referência é a base do racional: um projeto saudável tem evidência de que Verificação/Validação e Gestão de Requisitos aconteceram — matriz de rastreabilidade atualizada, ADRs com status coerente, ausência de requisitos contraditórios não resolvidos.
