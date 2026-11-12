# TemplateApp

API REST TemplateApp (Laravel 13, PHP 8.5) consommée par les fronts **Angular** et **Flutter**.
Toute la logique de données passe par des **procédures stockées PostgreSQL** : pas de migrations ni de modèles Eloquent.

Auteur : {{AUTHOR}}

<!-- template:start -->
## Créer un nouveau projet depuis ce template

> Cette section disparaît automatiquement des projets générés.

Le nom peut s'écrire `mon-projet`, `mon_projet`, `MonProjet` ou `"Mon Projet"`. Il est décliné automatiquement :

| Usage | Exemple |
|---|---|
| `APP_NAME`, titre Swagger, README | `MonProjet` |
| Base / rôle PostgreSQL (`.env`, `phpunit.xml`) | `mon_projet`, `mon_projet_test` |
| Dossier et paquet composer | `mon-projet`, `vendor/mon-projet` |

### Option 1 — une seule commande (recommandée)

```bash
curl -fsSL https://raw.githubusercontent.com/jimlasiterdavid/TEMPLATE-CLEAN-ARCHITECTURE-LARAVEL/main/bin/create-project.sh | bash -s -- mon-projet
```

Clone le template, renomme tout, `composer install`, génère `APP_KEY`, puis `git init` avec un premier commit.
Options : `--vendor=acme`, `--author="Nom"`, `--email=…`, `--no-install`, `--no-git`, dossier parent en 2ᵉ argument.
Dépôt privé : `curl … | TEMPLATE_REPO=git@github.com:jimlasiterdavid/TEMPLATE-CLEAN-ARCHITECTURE-LARAVEL.git bash -s -- mon-projet`
(ou téléchargez le script avec `gh` / un token).

Depuis un clone local du template :

```bash
./bin/create-project.sh mon-projet ~/projets
```

### Option 2 — Composer

```bash
composer create-project jimlasiterdavid/template-clean-architecture-laravel mon-projet \
  --repository='{"type":"vcs","url":"https://github.com/jimlasiterdavid/TEMPLATE-CLEAN-ARCHITECTURE-LARAVEL"}' \
  --stability=dev
```

(Sans `--repository` ni `--stability` si le paquet est publié sur Packagist avec un tag.) Le nom est déduit du dossier.

### Option 3 — bouton GitHub « Use this template »

Activer *Settings → Template repository* sur le dépôt, puis dans le clone du nouveau dépôt :

```bash
php bin/init.php            # ou : php bin/init.php mon-projet --vendor=acme
composer setup
git add -A && git commit -m "Initialisation"
```

### Ce que fait `bin/init.php`

Remplace les marqueurs `TemplateApp` / `template_app` / `template-app` dans tout le projet,
met à jour `composer.json` (nom, auteur depuis `git config`), crée `.env`, puis supprime
`bin/`, `.template` et cette section. Il ne s'exécute qu'une fois.

Pour faire évoluer le template, travaillez directement dans ce dépôt en gardant ces marqueurs
(n'exécutez pas `bin/init.php` ici : il refuse de tourner dans le dossier `template-clean-architecture-laravel`).
<!-- template:end -->


## Architecture (clean architecture)

Les dépendances pointent toujours vers l'intérieur : `Api` / `Infrastructure` → `Application` → `Domain`
(vérifié automatiquement par `tests/Unit/ArchitectureTest.php`).

```
app/
├── Domain/                      Cœur métier, PHP pur (aucune dépendance Laravel)
│   ├── Shared/Errors/           DomainError, BusinessRuleViolation (422), NotFoundError (404)
│   ├── Shared/ValueObjects/     Montant…
│   └── Exemple/                 Entities/ + Repositories/ (interfaces = ports)
├── Application/                 Cas d'usage (un par action) + DTO d'entrée
│   └── Exemple/UseCases/        ListerExemplesUseCase, ObtenirExempleUseCase, CreerExempleUseCase
├── Infrastructure/              Adaptateurs techniques
│   ├── Database/StoredProcedure/  ProcedureExecutor (port) + PdoProcedureExecutor, ProcedureCallBuilder
│   └── Exemple/Repositories/    StoredProcedureExempleRepository (appelle les procédures, mappe en entités)
├── Api/                         Couche d'exposition HTTP
│   ├── Controllers/V1/          Contrôleurs fins : FormRequest → cas d'usage → ApiResponse
│   ├── Requests/                Validation des entrées
│   ├── Responses/ApiResponse    Format JSON unique
│   ├── Exceptions/              ApiExceptionRenderer : toute exception → JSON
│   └── OpenApi/                 Définition Swagger globale
└── Providers/CleanArchitectureServiceProvider   Liaison interfaces → implémentations
```

Le module **Exemple** est un modèle complet à dupliquer pour chaque ressource.

## Format des réponses

```json
{ "success": true,  "message": "OK", "data": { } }
{ "success": false, "message": "Ce libellé existe déjà." }
{ "success": false, "message": "Les données envoyées sont invalides.", "errors": { "montant": ["…"] } }
```

| Situation | HTTP |
|---|---|
| Succès / création | 200 / 201 |
| Validation Laravel | 422 + `errors` |
| Erreur métier levée par la procédure (`RAISE EXCEPTION '…'`) | 422, message renvoyé tel quel au front |
| Ressource introuvable | 404 |
| Erreur technique (fonction absente, SQL…) | 500, détail masqué si `APP_DEBUG=false` |

## Procédures stockées PostgreSQL

Les procédures qui retournent des données sont des **fonctions** (`RETURNS TABLE` / `SETOF`),
appelées par `SELECT * FROM ma_fonction(...)`.

```php
// Dans un repository de l'Infrastructure
$this->sp->call('ps_transactions_liste', [$page, $parPage]);           // toutes les lignes
$this->sp->first('ps_transaction_detail', ['p_id' => $id]);            // 1re ligne ou null (paramètres nommés)
```

- Paramètres positionnels (`[1, 20]`) ou nommés (`['p_id' => 1]` → `p_id => ?`).
- Typage automatique : `bool`, `int`, `null`, chaîne.
- Nom de fonction validé (identifiant + schéma optionnel `paiement.ps_x`) : pas d'injection possible.
- Une erreur métier se lève côté base avec `RAISE EXCEPTION 'Solde insuffisant';` : le message (sans `ERROR:`/`CONTEXT:`) est renvoyé au front en 422.

### Ajouter une ressource

1. `Domain/<Module>/Entities` + interface `Domain/<Module>/Repositories/<Module>Repository`
2. `Application/<Module>/UseCases` (+ DTO)
3. `Infrastructure/<Module>/Repositories/StoredProcedure<Module>Repository`
4. Liaison dans `CleanArchitectureServiceProvider::$bindings`
5. `Api/Requests`, `Api/Controllers/V1` avec attributs `#[OA\...]`, route dans `routes/api.php`
6. Tests dans chaque suite (voir ci-dessous)

## Installation

```bash
composer setup                # install, .env, clé
# Configurer DB_* dans .env (PostgreSQL)
php artisan serve             # http://localhost:8000
```

En local : PostgreSQL 17 Homebrew sur le port **5433**, rôle/mot de passe `template_app`, bases `template_app` et `template_app_test`.

## Documentation Swagger

- Swagger UI : <http://localhost:8000/documentation>
- Spécification OpenAPI JSON : <http://localhost:8000/docs> (à importer côté Angular/Flutter pour générer les clients)
- Générée depuis les attributs `#[OA\...]` de `app/Api`. En local elle se régénère à chaque visite
  (`L5_SWAGGER_GENERATE_ALWAYS=true`) ; en production lancer `php artisan l5-swagger:generate` au déploiement.

## Tests

| Suite | Dossier | Ce qui est testé | Base |
|---|---|---|---|
| **Unit** | `tests/Unit` | Domaine (Montant, entités), ProcedureCallBuilder, classement des erreurs, rendu des exceptions, règles d'architecture | aucune |
| **Application** | `tests/Application` | Cas d'usage avec un repository en mémoire | aucune |
| **Feature** | `tests/Feature` | Scénarios fonctionnels complets (créer → consulter → lister…) | mémoire |
| **Api** | `tests/Api` | Contrat HTTP : codes, structure JSON, en-têtes, CORS, 404/405/422/500, doc Swagger à jour | mémoire |
| **Integration** | `tests/Integration` | Vraies fonctions PL/pgSQL (`fixtures/pgsql`) + appel HTTP de bout en bout | PostgreSQL `template_app_test` |

```bash
php artisan test                      # tout
composer test:unit                    # ou test:application, test:feature, test:api, test:integration
```

Les tests d'intégration sont ignorés si la base `INTEGRATION_DB_*` (voir `phpunit.xml`) est injoignable.

## CORS

Origines autorisées dans `CORS_ALLOWED_ORIGINS` (séparées par des virgules, ex. `http://localhost:4200`).
Les apps Flutter mobiles ne sont pas concernées par CORS (seulement Flutter Web).
