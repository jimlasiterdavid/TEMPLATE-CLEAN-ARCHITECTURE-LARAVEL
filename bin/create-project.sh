#!/usr/bin/env bash
#
# Crée un nouveau projet Laravel clean architecture à partir de ce template.
#
# Interactif (les choix sont demandés) :
#   curl -fsSL https://raw.githubusercontent.com/jimlasiterdavid/TEMPLATE-CLEAN-ARCHITECTURE-LARAVEL/main/bin/create-project.sh | bash -s -- mon-projet
#
# En une ligne :
#   … | bash -s -- mon-projet --ui=livewire --api --data=eloquent --db=mysql
#
# Depuis un clone local du template :
#   ./bin/create-project.sh mon-projet [dossier-parent] [options]
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

Choix (demandés de façon interactive s'ils sont absents) :
  --ui=none|livewire|vue|react      interface web
  --api / --no-api                  exposer une API REST + Swagger
  --data=eloquent|procedures        accès aux données
  --db=pgsql|mysql|mariadb|sqlite   base de données

Options :
  --vendor=acme   préfixe du paquet composer (défaut : nom du projet)
  --author="Nom"  auteur (défaut : git config user.name)
  --email=mail    e-mail de l'auteur (défaut : git config user.email)
  --no-install    ne pas lancer composer / npm
  --no-git        ne pas initialiser de dépôt git
  -n, --defaults  aucune question, valeurs par défaut (API + Eloquent + PostgreSQL)

Variables : TEMPLATE_REPO (URL git du template), TEMPLATE_BRANCH (défaut : main)
TXT
}

NAME=""
PARENT="$(pwd)"
INIT_ARGS=()
GIT=1
POSITIONAL=0

for arg in "$@"; do
  case "$arg" in
    -h|--help) usage; exit 0 ;;
    --no-git)  GIT=0 ;;
    -*)        INIT_ARGS+=("$arg") ;;
    *)
      if [ $POSITIONAL -eq 0 ]; then NAME="$arg"; else PARENT="$arg"; fi
      POSITIONAL=$((POSITIONAL + 1)) ;;
  esac
done

if [ -z "$NAME" ] && [ -r /dev/tty ]; then
  printf '\033[36m?\033[0m Nom du projet : '
  read -r NAME < /dev/tty || true
fi
[ -n "$NAME" ] || { usage; exit 1; }

command -v php >/dev/null || die "PHP est requis (php -v)."
command -v git >/dev/null || die "git est requis."

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

cleanup_on_error() { [ -n "${TARGET:-}" ] && [ -d "$TARGET" ] && printf '\033[33m! Création interrompue : %s est incomplet.\033[0m\n' "$TARGET" >&2; }
trap cleanup_on_error ERR

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
  green "→ Téléchargement du template ($TEMPLATE_REPO, $TEMPLATE_BRANCH)"
  git clone --quiet --depth 1 --branch "$TEMPLATE_BRANCH" "$TEMPLATE_REPO" "$TARGET"
  rm -rf "$TARGET/.git"
fi

cd "$TARGET"
php bin/init.php "$NAME" ${INIT_ARGS[@]+"${INIT_ARGS[@]}"}

if [ $GIT -eq 1 ]; then
  git init --quiet -b main 2>/dev/null || git init --quiet
  git add -A
  git commit --quiet -m "Initialisation du projet $NAME" || true
  green "✔ Dépôt git initialisé"
fi

echo
green "✔ Projet créé : $TARGET"
echo "  cd $TARGET"
echo "  php artisan serve"
