# TemplateApp

Application Laravel en **clean architecture**.

Auteur : Zamble Bi Tah David

<!-- template:start -->
## Template clean architecture Laravel

Générateur de projets Laravel en clean architecture. À la création, on choisit :

- **Interface web**
  - aucune : API seule ou front séparé (Angular, Flutter, React, Vue…) ;
  - Livewire ;
  - Inertia + Vue ;
  - Inertia + React.
- **API REST + Swagger** : oui ou non. Avec une interface, on obtient le mode hybride, par exemple une app web et une API pour une app mobile.
- **Accès aux données** : Eloquent (migrations + modèles) ou procédures stockées PostgreSQL.
- **Base de données** : PostgreSQL, MySQL, MariaDB ou SQLite. Les procédures stockées imposent PostgreSQL.

Le Domain et les cas d'usage sont identiques dans tous les cas. Seuls les adaptateurs changent : contrôleurs API, composants Livewire ou pages Inertia d'un côté ; repository Eloquent ou procédures stockées de l'autre.

**Aucune version n'est figée** : les paquets propres à chaque option sont installés à la création, dans leur dernière version compatible. Le projet généré reçoit son propre `composer.lock` et son propre `package-lock.json`.

### Créer un projet

```bash
curl -fsSL https://raw.githubusercontent.com/jimlasiterdavid/TEMPLATE-CLEAN-ARCHITECTURE-LARAVEL/main/bin/create-project.sh | bash -s -- mon-projet
```

Les choix sont demandés de façon interactive. On peut aussi tout passer en une ligne :

```bash
… | bash -s -- mon-projet --ui=livewire --api --data=eloquent --db=mysql
… | bash -s -- mon-projet --ui=none --api --data=procedures          # API sur procédures stockées
… | bash -s -- mon-projet --ui=react --no-api --db=sqlite            # app React, démarrage immédiat
```

| Option | Valeurs | Défaut |
|---|---|---|
| `--ui` | `none`, `livewire`, `vue`, `react` | `none` |
| `--api` / `--no-api` | | oui |
| `--data` | `eloquent`, `procedures` | `eloquent` |
| `--db` | `pgsql`, `mysql`, `mariadb`, `sqlite` | `pgsql` |
| `--vendor`, `--author`, `--email` | métadonnées de `composer.json` | nom du projet, `git config` |
| `--no-install`, `--no-git`, `-n` (aucune question) | | |

Le nom peut s'écrire `mon-projet`, `MonProjet` ou `"Mon Projet"`. Il est décliné en `MonProjet` (`APP_NAME`, titre Swagger), `mon_projet` (base de données) et `mon-projet` (dossier, paquet composer).

Prérequis : PHP ≥ 8.3, Composer, git. Node.js/npm en plus si une interface web est choisie.

### Autres façons de créer un projet

- **Depuis un clone du template :** `./bin/create-project.sh mon-projet ~/projets --ui=vue`.
- **Bouton GitHub « Use this template » :** dans le clone du nouveau dépôt, lancer `php bin/init.php`.
- **Composer :**
  ```bash
  composer create-project jimlasiterdavid/template-clean-architecture-laravel mon-projet \
    --repository='{"type":"vcs","url":"https://github.com/jimlasiterdavid/TEMPLATE-CLEAN-ARCHITECTURE-LARAVEL"}' --stability=dev
  ```

### Faire évoluer le template

- Le générateur est `bin/init.php`. Les options sont décrites dans ses tableaux `UIS`, `DATA` et `DBS`. Les fichiers propres à une option sont déclarés dans `PATH_FEATURES` et supprimés si l'option n'est pas retenue.
- Dans les fichiers partagés, les lignes conditionnelles sont entourées de marqueurs placés dans un commentaire : `// @feature ui:vue | ui:react` … `// @endfeature`. Les expressions acceptent `&`, `|` et `!`.
- Les marqueurs `TemplateApp`, `template_app` et `template-app` sont remplacés par le nom du projet.
- Ce dépôt n'est pas une application exécutable telle quelle. Pour tester une combinaison :
  ```bash
  ./bin/create-project.sh essai /tmp --ui=livewire --api --no-git && (cd /tmp/essai && php artisan test)
  ```
- La CI GitHub (`.github/workflows/template.yml`) génère plusieurs combinaisons et lance leurs tests, avec un vrai PostgreSQL pour les procédures stockées. Elle tourne à chaque push et **chaque semaine**, pour détecter au plus tôt une nouvelle version de Laravel, Livewire, Inertia, Vite… qui casserait le template.
<!-- template:end -->

## Stack

- Laravel, PHP ≥ 8.3
<!-- @feature api -->
- API REST versionnée (`/api/v1`) documentée avec Swagger (OpenAPI)
<!-- @endfeature -->
<!-- @feature ui:livewire -->
- Interface web **Livewire** + Tailwind CSS (Vite)
<!-- @endfeature -->
<!-- @feature ui:vue -->
- Interface web **Inertia + Vue** + Tailwind CSS (Vite)
<!-- @endfeature -->
<!-- @feature ui:react -->
- Interface web **Inertia + React** + Tailwind CSS (Vite)
<!-- @endfeature -->
<!-- @feature data:eloquent -->
- Données via **Eloquent** (migrations + modèles)
<!-- @endfeature -->
<!-- @feature data:procedures -->
- Données via **procédures stockées PostgreSQL** (pas de migrations ni de modèles Eloquent)
<!-- @endfeature -->
<!-- @feature db:pgsql -->
- Base PostgreSQL
<!-- @endfeature -->
<!-- @feature db:mysql -->
- Base MySQL
<!-- @endfeature -->
<!-- @feature db:mariadb -->
- Base MariaDB
<!-- @endfeature -->
<!-- @feature db:sqlite -->
- Base SQLite (`database/database.sqlite`)
<!-- @endfeature -->

## Démarrage

```bash
composer setup        # dépendances, .env, clé d'application…
```

<!-- @feature !db:sqlite -->
Configurer ensuite la connexion `DB_*` dans `.env`.
<!-- @endfeature -->
<!-- @feature data:eloquent & !db:sqlite -->
Créer la base, puis lancer `php artisan migrate`.
<!-- @endfeature -->

```bash
composer dev          # serveur + processus de développement (http://localhost:8000)
php artisan test      # tous les tests
```

<!-- @feature ui -->
Les assets front sont compilés par Vite : `npm run dev` en développement (rechargement à chaud), `npm run build` pour la production.
<!-- @endfeature -->

## Architecture (clean architecture)

Les dépendances pointent toujours vers l'intérieur. Les couches de présentation et l'infrastructure dépendent de `Application`, qui dépend de `Domain`. C'est vérifié automatiquement par `tests/Unit/ArchitectureTest.php`.

```
app/
├── Domain/                     Cœur métier, PHP pur (aucune dépendance Laravel)
│   ├── Shared/Errors/          DomainError, BusinessRuleViolation, NotFoundError
│   ├── Shared/ValueObjects/    Montant…
│   └── Exemple/                Entities/ + Repositories/ (interfaces = ports)
├── Application/                Cas d'usage (un par action) + DTO d'entrée
│   └── Exemple/UseCases/       ListerExemplesUseCase, ObtenirExempleUseCase, CreerExempleUseCase
├── Infrastructure/             Adaptateurs techniques (implémentent les ports du domaine)
<!-- @feature data:eloquent -->
│   └── Exemple/                Models/ExempleModel + Repositories/EloquentExempleRepository
<!-- @endfeature -->
<!-- @feature data:procedures -->
│   ├── Database/StoredProcedure/  ProcedureExecutor (port) + PdoProcedureExecutor, ProcedureCallBuilder
│   └── Exemple/Repositories/   StoredProcedureExempleRepository
<!-- @endfeature -->
<!-- @feature api -->
├── Api/                        Présentation HTTP JSON
│   ├── Controllers/V1/         Contrôleurs fins : FormRequest → cas d'usage → ApiResponse
│   ├── Requests/               Validation des entrées
│   ├── Responses/ApiResponse   Format JSON unique
│   ├── Exceptions/             ApiExceptionRenderer : toute exception → JSON
│   └── OpenApi/                Définition Swagger globale
<!-- @endfeature -->
<!-- @feature ui:livewire -->
├── Web/                        Présentation web (Livewire)
│   ├── Livewire/Exemples/      Composants : valider → cas d'usage → vue Blade
│   └── Exceptions/             WebExceptionRenderer : erreurs du domaine → formulaire / 404
<!-- @endfeature -->
<!-- @feature ui:inertia -->
├── Web/                        Présentation web (Inertia)
│   ├── Controllers/            Contrôleurs fins : FormRequest → cas d'usage → Inertia::render
│   ├── Requests/               Validation des entrées
│   ├── Middleware/             HandleInertiaRequests (données partagées, messages flash)
│   └── Exceptions/             WebExceptionRenderer : erreurs du domaine → formulaire / 404
<!-- @endfeature -->
└── Providers/CleanArchitectureServiceProvider   Liaison interfaces → implémentations
```

Le module **Exemple** est un modèle complet à dupliquer pour chaque ressource.
Pour changer de stockage, il suffit d'écrire un autre repository qui implémente l'interface du domaine et de le lier dans `CleanArchitectureServiceProvider`. Les cas d'usage et la présentation ne changent pas.

<!-- @feature api -->
## API REST

### Format des réponses

```json
{ "success": true,  "message": "OK", "data": { } }
{ "success": false, "message": "Ce libellé existe déjà." }
{ "success": false, "message": "Les données envoyées sont invalides.", "errors": { "montant": ["…"] } }
```

- Succès / création → 200 / 201.
- Validation Laravel → 422 + `errors`.
- Erreur métier (`BusinessRuleViolation`) → 422, avec le message renvoyé tel quel.
- Ressource introuvable → 404.
- Erreur technique → 500, détail masqué si `APP_DEBUG=false`.

### Documentation Swagger

- Swagger UI : <http://localhost:8000/documentation>
- Spécification OpenAPI JSON : <http://localhost:8000/docs>, à importer côté client pour générer les appels.
- Elle est générée depuis les attributs `#[OA\...]` de `app/Api`. En local, elle se régénère à chaque visite (`L5_SWAGGER_GENERATE_ALWAYS=true`). En production, lancer `php artisan l5-swagger:generate` au déploiement.
- Le test `tests/Api/DocumentationTest.php` échoue si une route `/api/v1` n'est pas documentée.

### CORS

Les origines autorisées à appeler l'API depuis un navigateur sont listées dans `CORS_ALLOWED_ORIGINS`, séparées par des virgules. Les applications mobiles natives ne sont pas concernées.
<!-- @endfeature -->

<!-- @feature ui:livewire -->
## Interface Livewire

- Les composants sont dans `app/Web/Livewire` (`config/livewire.php`) et leurs vues dans `resources/views/livewire`. Le layout est `resources/views/layouts/app.blade.php`.
- Un composant valide les champs, appelle un cas d'usage et affiche le résultat. Les erreurs du domaine (`DomainError`) sont affichées dans l'erreur `erreur` du formulaire.
- `php artisan make:livewire NomDuComposant` crée directement le composant dans `app/Web/Livewire`.
<!-- @endfeature -->

<!-- @feature ui:inertia -->
## Interface Inertia

- Les contrôleurs sont dans `app/Web/Controllers` et les pages dans `resources/js/pages`. `Inertia::render('Exemples/Index')` correspond à `resources/js/pages/Exemples/Index.*`.
- Une erreur métier ramène l'utilisateur au formulaire, avec le message dans `errors.erreur`. Les messages de succès sont partagés dans `flash.succes` (voir `HandleInertiaRequests`).
<!-- @endfeature -->

<!-- @feature data:eloquent -->
## Données : Eloquent

- Les modèles Eloquent sont un détail d'infrastructure (`app/Infrastructure/<Module>/Models`). Le repository les convertit en entités du domaine, qui seules sortent de l'infrastructure.
- Les erreurs de base attendues (contrainte d'unicité…) sont traduites en `BusinessRuleViolation`.
- Migrations : `database/migrations`, à lancer avec `php artisan migrate`.
<!-- @endfeature -->

<!-- @feature data:procedures -->
## Données : procédures stockées PostgreSQL

Les procédures qui retournent des données sont des **fonctions** (`RETURNS TABLE` / `SETOF`), appelées par `SELECT * FROM ma_fonction(...)`.

```php
// Dans un repository de l'Infrastructure
$this->sp->call('ps_commandes_liste', [$page, $parPage]);   // toutes les lignes
$this->sp->first('ps_commande_detail', ['p_id' => $id]);     // 1re ligne ou null (paramètres nommés)
```

- Paramètres positionnels (`[1, 20]`) ou nommés (`['p_id' => 1]` → `p_id => ?`).
- Typage automatique : `bool`, `int`, `null`, chaîne.
- Le nom de fonction est validé (identifiant + schéma optionnel, ex. `vente.ps_x`), ce qui empêche l'injection.
- Une erreur métier se lève côté base avec `RAISE EXCEPTION 'Solde insuffisant';`. Le message, sans `ERROR:`/`CONTEXT:`, devient une `BusinessRuleViolation` affichée à l'utilisateur.
- Des fonctions d'exemple (pour les tests) sont dans `tests/Integration/fixtures/pgsql/exemple.sql`.
<!-- @endfeature -->

## Ajouter une ressource

1. `Domain/<Module>/Entities` + interface `Domain/<Module>/Repositories/<Module>Repository`
2. `Application/<Module>/UseCases` (+ DTO)
<!-- @feature data:eloquent -->
3. Migration + `Infrastructure/<Module>/Models/<Module>Model` + `Infrastructure/<Module>/Repositories/Eloquent<Module>Repository`
<!-- @endfeature -->
<!-- @feature data:procedures -->
3. `Infrastructure/<Module>/Repositories/StoredProcedure<Module>Repository`
<!-- @endfeature -->
4. Liaison dans `CleanArchitectureServiceProvider::$bindings`
<!-- @feature api -->
5. API : `Api/Requests`, `Api/Controllers/V1` avec attributs `#[OA\...]`, route dans `routes/api.php`
<!-- @endfeature -->
<!-- @feature ui:livewire -->
6. Web : composant `Web/Livewire/<Module>` + vue `resources/views/livewire/…` + route dans `routes/web.php`
<!-- @endfeature -->
<!-- @feature ui:inertia -->
6. Web : `Web/Requests`, `Web/Controllers`, page `resources/js/pages/<Module>/…` + routes dans `routes/web.php`
<!-- @endfeature -->
7. Tests dans chaque suite (voir ci-dessous)

## Tests

```bash
php artisan test                      # tout
composer test:unit                    # ou test:application, test:integration…
```

- **Unit** (`tests/Unit`) : domaine, règles d'architecture, composants techniques. Aucune base.
- **Application** (`tests/Application`) : cas d'usage avec un repository en mémoire.
<!-- @feature api -->
- **Feature** (`tests/Feature`) : scénarios complets via l'API (créer → consulter → lister…), en mémoire.
- **Api** (`tests/Api`) : contrat HTTP (codes, structure JSON, CORS, erreurs) et documentation Swagger à jour.
<!-- @endfeature -->
<!-- @feature ui -->
- **Web** (`tests/Web`) : pages et formulaires de l'interface, en mémoire.
<!-- @endfeature -->
<!-- @feature data:eloquent -->
- **Integration** (`tests/Integration`) : repository Eloquent et migrations sur SQLite en mémoire.
<!-- @endfeature -->
<!-- @feature data:procedures -->
- **Integration** (`tests/Integration`) : vraies fonctions PL/pgSQL sur la base `INTEGRATION_DB_*` (voir `phpunit.xml`). Ces tests sont ignorés si la base est injoignable.
<!-- @endfeature -->
