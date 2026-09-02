# Análise de Requisitos

## Objetivo
Transformar os requisitos brutos coletados no Levantamento em algo estruturado, classificado e formal que a equipe técnica consegue usar para projetar o sistema. É a ponte entre "o que o cliente quer" e "o que vamos construir".

## Como fazer

**1. Classificar** em Requisitos Funcionais (RF — o que o sistema faz) e Não-Funcionais (RNF — como se comporta: performance, segurança, usabilidade, disponibilidade, escalabilidade). RNFs são especialmente importantes porque viram decisão concreta no Design (ver `design.md`).

**2. Verificar a qualidade de cada requisito** contra estes atributos (ISO/IEC/IEEE 29148:2018):

| Atributo | Significado |
|---|---|
| Necessário | Realmente precisa existir |
| Não ambíguo | Só uma interpretação possível |
| Completo | Não deixa lacunas |
| Singular | Trata de uma única coisa por vez |
| Factível/viável | Possível de implementar no prazo/orçamento |
| Verificável | Dá para testar se foi atendido |
| Correto | Reflete a real necessidade do stakeholder |
| Conforme | Segue o padrão/template adotado |

O **conjunto** de requisitos deve ser: completo, consistente e delimitado (bounded). Essa mesma tabela é reusada em `gestao-continua.md` para revalidar requisitos já existentes.

**3. Priorizar** — MoSCoW (Must/Should/Could/Won't) ou matriz impacto x urgência.

**4. Definir o escopo** — limites do sistema (responsabilidade dele vs. externo), restrições de negócio e técnicas, critérios de aceitação, registro formal (Documento de Visão). Separar claramente "in scope" e "out of scope", sempre por escrito.

**5. Extrair e catalogar regras de negócio** — dentro dos RFs quando poucas; catálogo separado quando numerosas ou reutilizadas por vários módulos.

**6. Modelar** — casos de uso formalizados (UML + fluxo principal/alternativo/pré-pós-condições), diagramas de fluxo para regras complexas, DER conceitual, diagrama de classes conceitual (se orientado a objetos), protótipos de baixa fidelidade (aqui para **validar**, diferente do Levantamento onde servem pra descobrir), glossário de termos.

## Boas práticas
- Manter rastreabilidade: todo requisito, regra e caso de uso deve linkar de volta a uma necessidade de stakeholder.
- Revisar contra a tabela de qualidade antes de encerrar a fase.
- Fazer a definição de escopo por escrito e validada, nunca implícita.

## Problemas comuns

| Problema | Por que evitar |
|---|---|
| Misturar RF e RNF sem classificar | Dificulta priorização; requisito de performance tratado como funcional pode ser esquecido no plano de testes |
| Regras de negócio espalhadas sem catálogo | Difícil rastrear e atualizar; risco de implementação inconsistente entre módulos |
| Escopo não formalizado | Abre espaço para scope creep e disputa com o cliente |
| Pular a verificação de qualidade | Requisitos ambíguos só são descobertos como problema na fase de testes, onde é mais caro corrigir |
| Modelagem excessivamente técnica cedo demais | Detalhe técnico (tipos de dado, índices) é papel do Design, não da Análise |

## O que produzir e onde vai

| Documento | Obrigatório quando | Vai em |
|---|---|---|
| SRS/ERS com RF/RNF classificados | sempre que houver SRS formal | `docs/01-requisitos/srs.md` |
| Documento de Visão / Escopo | quase sempre, mesmo que curto | `docs/01-requisitos/escopo.md` |
| Catálogo de Regras de Negócio | regras numerosas/reutilizadas | `docs/01-requisitos/regras-de-negocio.md` |
| Casos de Uso (formalizados) | muitos atores, fluxos complexos | `docs/01-requisitos/casos-de-uso/` |
| DER conceitual / Diagrama de Classes conceitual | modelagem relevante | `docs/01-requisitos/` (evolui pro Design) |
| Glossário | domínio complexo/especializado | `docs/01-requisitos/glossario.md` |
| Matriz de Rastreabilidade (baseline inicial) | sistemas regulados, projetos grandes/multi-equipe | `docs/01-requisitos/rastreabilidade.md` |
