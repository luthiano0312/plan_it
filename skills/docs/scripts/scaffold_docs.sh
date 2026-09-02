#!/usr/bin/env bash
# Cria a estrutura de pastas docs/ + stub de CLAUDE.md/AGENTS.md num projeto novo.
# Uso: scaffold_docs.sh [diretorio-alvo]  (default: diretório atual)

set -euo pipefail

TARGET="${1:-.}"
SKILL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

mkdir -p "$TARGET/docs/01-requisitos/casos-de-uso"
mkdir -p "$TARGET/docs/02-design/apis"
mkdir -p "$TARGET/docs/02-design/ui-ux"
mkdir -p "$TARGET/docs/02-design/adr/archive"
mkdir -p "$TARGET/docs/how-to"
mkdir -p "$TARGET/docs/reference"
mkdir -p "$TARGET/docs/explanation"

# .gitkeep nas pastas que começam vazias, pra não sumir no git
for d in \
  "$TARGET/docs/01-requisitos/casos-de-uso" \
  "$TARGET/docs/02-design/apis" \
  "$TARGET/docs/02-design/ui-ux" \
  "$TARGET/docs/02-design/adr" \
  "$TARGET/docs/02-design/adr/archive" \
  "$TARGET/docs/how-to" \
  "$TARGET/docs/reference" \
  "$TARGET/docs/explanation"
do
  [ -z "$(ls -A "$d" 2>/dev/null)" ] && touch "$d/.gitkeep"
done

# Copia os templates só se ainda não existir o arquivo (não sobrescreve trabalho já feito)
copy_if_absent() {
  local src="$1" dest="$2"
  if [ ! -f "$dest" ]; then
    cp "$src" "$dest"
    echo "criado: $dest"
  else
    echo "já existe, mantido: $dest"
  fi
}

copy_if_absent "$SKILL_DIR/assets/templates/escopo.md" "$TARGET/docs/01-requisitos/escopo.md"
copy_if_absent "$SKILL_DIR/assets/templates/claude-md.md" "$TARGET/CLAUDE.md"

echo ""
echo "Estrutura criada em: $TARGET/docs/"
echo "Próximo passo: decidir SRS vs. backlog (ver references/requisitos-levantamento.md) e preencher CLAUDE.md."
