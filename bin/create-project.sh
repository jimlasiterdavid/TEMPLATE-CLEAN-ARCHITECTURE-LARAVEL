#!/usr/bin/env bash
#
# Crée un nouveau projet Laravel clean architecture à partir de ce template.
#
# Depuis GitHub (aucune installation préalable) :
#   curl -fsSL https://raw.githubusercontent.com/jimlasiterdavid/TEMPLATE-CLEAN-ARCHITECTURE-LARAVEL/main/bin/create-project.sh | bash -s -- mon-projet
#
# Depuis un clone local du template :
#   ./bin/create-project.sh mon-projet [dossier-parent] [--vendor=acme] [--author="Nom"] [--email=mail] [--no-install] [--no-git]
#
# Variables d'environnement :
#   TEMPLATE_REPO    URL git du template (défaut : ci-dessous)
#   TEMPLATE_BRANCH  branche à utiliser (défaut : main)
#
set -euo pipefail

TEMPLATE_REPO="${TEMPLATE_REPO:-https://github.com/jimlasiterdavid/TEMPLATE-CLEAN-ARCHITECTURE-LARAVEL.git}"
TEMPLATE_BRANCH="${TEMPLATE_BRANCH:-main}"

green() { printf '\033[32m%s\033[0m\n' "$*"; }
die()   { printf '\033[31m✘ %s\033[0m\n' "$*" >&2; exit 1; }

usage() {
  cat <<'TXT'
Usage : create-project.sh <nom-du-projet> [dossier-parent] [options]

  nom-du-projet   mon-projet, mon_projet, MonProjet ou "Mon Projet"
  dossier-parent  où créer le projet (défaut : dossier courant)

Options :
  --vendor=acme   préfixe du paquet composer (défaut : nom du projet)
  --author="Nom"  auteur (défaut : git config user.name)
  --email=mail    e-mail de l'auteur (défaut : git config user.email)
  --no-install    ne pas lancer composer install
  --no-git        ne pas initialiser de dépôt git

Variables : TEMPLATE_REPO (URL git du template), TEMPLATE_BRANCH (défaut : main)
TXT
}

NAME=""
PARENT="$(pwd)"
INIT_ARGS=()
INSTALL=1
GIT=1
POSITIONAL=0

for arg in "$@"; do
  case "$arg" in
    -h|--help)    usage; exit 0 ;;
    --no-install) INSTALL=0 ;;
    --no-git)     GIT=0 ;;
    --vendor=*|--author=*|--email=*) INIT_ARGS+=("$arg") ;;
    -*)           die "Option inconnue : $arg" ;;
    *)
      if [ $POSITIONAL -eq 0 ]; then NAME="$arg"; else PARENT="$arg"; fi
      POSITIONAL=$((POSITIONAL + 1)) ;;
  esac
done

[ -n "$NAME" ] || { usage; exit 1; }

command -v php >/dev/null || die "PHP est requis (php -v)."
command -v git >/dev/null || die "git est requis."
[ $INSTALL -eq 0 ] || command -v composer >/dev/null || die "Composer est requis (ou utilisez --no-install)."

# Nom du dossier : mon-projet (minuscules, tirets)
DIR_NAME="$(printf '%s' "$NAME" | sed -E 's/([a-z0-9])([A-Z])/\1-\2/g' | tr '[:upper:]' '[:lower:]' | sed -E 's/[^a-z0-9]+/-/g; s/^-+//; s/-+$//')"
[ -n "$DIR_NAME" ] || die "Nom de projet invalide : $NAME"

mkdir -p "$PARENT"
TARGET="$(cd "$PARENT" && pwd)/$DIR_NAME"
[ ! -e "$TARGET" ] || die "Le dossier $TARGET existe déjà."

# Source : le clone local si le script est lancé depuis le template, sinon GitHub.
SCRIPT_PATH="${BASH_SOURCE[0]:-}"
LOCAL_TEMPLATE=""
if [ -n "$SCRIPT_PATH" ] && [ -f "$SCRIPT_PATH" ]; then
  candidate="$(cd "$(dirname "$SCRIPT_PATH")/.." && pwd)"
  [ -f "$candidate/.template" ] && LOCAL_TEMPLATE="$candidate"
fi

if [ -n "$LOCAL_TEMPLATE" ]; then
  green "→ Copie du template local ($LOCAL_TEMPLATE)"
  mkdir -p "$TARGET"
  if git -C "$LOCAL_TEMPLATE" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    # Fichiers suivis + nouveaux fichiers non ignorés (jamais vendor, .env…)
    (cd "$LOCAL_TEMPLATE" && git ls-files -z --cached --others --exclude-standard \
      | while IFS= read -r -d '' f; do [ -e "$f" ] && printf '%s\0' "$f"; done \
      | tar --null -T - -cf -) | tar -xf - -C "$TARGET"
  else
    tar -C "$LOCAL_TEMPLATE" --exclude=./vendor --exclude=./node_modules --exclude=./.git --exclude=./.env -cf - . | tar -xf - -C "$TARGET"
  fi
else
  green "→ Clonage de $TEMPLATE_REPO ($TEMPLATE_BRANCH)"
  git clone --quiet --depth 1 --branch "$TEMPLATE_BRANCH" "$TEMPLATE_REPO" "$TARGET"
  rm -rf "$TARGET/.git"
fi

cd "$TARGET"

green "→ Renommage du projet"
php bin/init.php "$NAME" ${INIT_ARGS[@]+"${INIT_ARGS[@]}"}

if [ $INSTALL -eq 1 ]; then
  green "→ Installation des dépendances"
  composer install --no-interaction --ansi
  php artisan key:generate --ansi
fi

if [ $GIT -eq 1 ]; then
  green "→ Initialisation git"
  git init --quiet -b main 2>/dev/null || git init --quiet
  git add -A
  git commit --quiet -m "Initialisation du projet $NAME depuis template-clean-architecture-laravel" || true
fi

echo
green "✔ Projet créé : $TARGET"
echo "  cd $TARGET"
echo "  # configurer DB_* dans .env, puis :"
echo "  php artisan serve        # http://localhost:8000/documentation"
