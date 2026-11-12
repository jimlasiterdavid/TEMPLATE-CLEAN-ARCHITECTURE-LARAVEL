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

**Git :** le projet est créé avec un dépôt git et un premier commit sur `main` (option `--no-git` pour s'en passer). Les branches `dev` et `staging` sont ensuite à créer par l'équipe, avec `dev` comme branche par défaut : voir la section **Branches** du README du projet généré.

### Autres façons de créer un projet

- **Depuis un clone du template :** `./bin/create-project.sh mon-projet ~/projets --ui=vue`.
- **Bouton GitHub « Use this template » :** dans le clone du nouveau dépôt, lancer `php bin/init.php`. Créer ensuite les branches `dev` et `staging` (section **Branches**).
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

## Branches

Le projet est livré avec la seule branche `main`. **À la création du projet, il faut créer soi-même les branches `dev` et `staging`, puis faire de `dev` la branche par défaut.**

| Branche | Rôle | Reçoit |
|---|---|---|
| `dev` | Développement, **branche par défaut** | les branches de fonctionnalité (`feature/…`, `fix/…`) via pull request |
| `staging` | Recette / pré-production | `dev` quand une version est prête à être testée |
| `main` | Production | `staging` une fois la recette validée |

1. **Publier le projet et créer les branches** :

   ```bash
   git remote add origin git@github.com:<compte>/<projet>.git
   git push -u origin main
   git checkout -b staging && git push -u origin staging
   git checkout -b dev && git push -u origin dev
   ```

2. **Mettre `dev` comme branche par défaut** : sur GitHub, *Settings → General → Default branch* → `dev`, ou en ligne de commande `gh repo edit --default-branch dev`.

3. **Recommandé** : protéger `main` et `staging` (*Settings → Branches*) pour n'y accepter que des pull requests.

Travail au quotidien :

```bash
git checkout dev && git pull
git checkout -b feature/ma-fonctionnalite
# … commits …
git push -u origin feature/ma-fonctionnalite    # puis pull request vers dev
```

## Architecture (clean architecture)

### Le principe

Le code est organisé en **couches concentriques**, avec une seule règle : **une couche ne connaît que les couches plus internes qu'elle**.

```
┌────────────────────────────────────────────────────────────────┐
│  Présentation (HTTP, pages)            Infrastructure (base…)  │  ← détails techniques, Laravel
│   ┌────────────────────────────────────────────────────────┐   │
│   │  Application : cas d'usage (ce que l'on peut faire)    │   │
│   │   ┌────────────────────────────────────────────────┐   │   │
│   │   │  Domain : règles métier (PHP pur)              │   │   │
│   │   └────────────────────────────────────────────────┘   │   │
│   └────────────────────────────────────────────────────────┘   │
└────────────────────────────────────────────────────────────────┘
          les dépendances pointent toujours vers l'intérieur →
```

Concrètement :

- Le **métier** (Domain) ne dépend ni de Laravel, ni de la base de données, ni de l'interface. On peut le lire, le tester et le faire évoluer seul.
- Les **cas d'usage** (Application) décrivent ce que l'application sait faire. Ils sont appelés de la même façon par l'API, une page web, une commande artisan ou un job.
- La **base de données** et l'**interface** sont des détails interchangeables. Passer d'Eloquent aux procédures stockées, ou ajouter une API à côté d'une interface web, ne touche ni au métier ni aux cas d'usage.
- Le Domain déclare ce dont il a besoin par des **interfaces** (« ports »), par exemple `ExempleRepository`. L'Infrastructure fournit les implémentations (« adaptateurs »), et `CleanArchitectureServiceProvider` relie les deux.

Ces règles sont vérifiées par un test (`tests/Unit/ArchitectureTest.php`) : un import interdit fait échouer la suite.

### Arborescence

```
app/
├── Domain/                     Cœur métier, PHP pur (aucune dépendance Laravel)
│   ├── Shared/Errors/          DomainError, BusinessRuleViolation, NotFoundError
│   ├── Shared/ValueObjects/    Montant…
│   └── Exemple/                Entities/ + Repositories/ (interfaces = ports)
├── Application/                Cas d'usage (un par action) + DTO d'entrée
│   └── Exemple/                DTO/CreerExempleInput + UseCases/ (Lister, Obtenir, Creer)
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
└── Providers/                  CleanArchitectureServiceProvider : ports → adaptateurs
```

Le module **Exemple** est un modèle complet, à dupliquer pour chaque ressource.

### Parcours d'une création, de bout en bout

1. **Entrée** :
<!-- @feature api -->
   - API : `POST /api/v1/exemples` arrive dans `Api\Controllers\V1\ExempleController::store`. La requête est validée par `Api\Requests\Exemple\CreerExempleRequest`, qui renvoie 422 si les champs sont invalides.
<!-- @endfeature -->
<!-- @feature ui:livewire -->
   - Livewire : la soumission du formulaire appelle `Web\Livewire\Exemples\Index::creer()`. Les champs sont validés par les attributs `#[Validate]`.
<!-- @endfeature -->
<!-- @feature ui:inertia -->
   - Inertia : `POST /exemples` arrive dans `Web\Controllers\ExempleController::store`. La requête est validée par `Web\Requests\Exemple\CreerExempleRequest`, qui renvoie au formulaire si les champs sont invalides.
<!-- @endfeature -->
2. La présentation transforme les données validées en **DTO** `CreerExempleInput` et appelle `CreerExempleUseCase::execute()`.
3. Le **cas d'usage** applique les règles : le libellé est nettoyé puis refusé s'il est vide, et `Montant::de()` refuse un montant négatif ou non numérique puis l'arrondit à 2 décimales. Il appelle ensuite le **port** `ExempleRepository::creer()`.
4. Le conteneur Laravel fournit l'**adaptateur** lié dans `CleanArchitectureServiceProvider`.
<!-- @feature data:eloquent -->
   Ici, `EloquentExempleRepository` enregistre via le modèle `ExempleModel`.
<!-- @endfeature -->
<!-- @feature data:procedures -->
   Ici, `StoredProcedureExempleRepository` appelle la fonction PostgreSQL `ps_exemple_creer`.
<!-- @endfeature -->
5. Le repository convertit la ligne enregistrée en **entité** `Exemple` et la retourne. Aucun modèle Eloquent et aucun tableau SQL ne sortent de l'Infrastructure.
6. La présentation met en forme le résultat : réponse JSON 201, composant ré-affiché ou redirection vers la page.
7. **Erreurs** : une `DomainError` levée à n'importe quelle étape (règle métier, doublon, introuvable…) remonte jusqu'à la présentation. Celle-ci la traduit : JSON 422/404 pour l'API, message affiché sous le formulaire pour l'interface web.

### Les couches en détail

#### Domain — `app/Domain`

Le cœur métier : **ce qui resterait vrai même sans application web**.

- **Entités** (`Exemple/Entities`) : les objets métier, identifiés par un id. Leurs invariants sont vérifiés dans le constructeur : on ne peut pas créer un `Exemple` sans libellé. Leurs propriétés sont `readonly`.
- **Value objects** (`Shared/ValueObjects`) : des valeurs sans identité, comparées par leur contenu. `Montant::de()` garantit qu'un montant est numérique, positif ou nul et arrondi à 2 décimales. Partout où l'on manipule un `Montant`, il est donc forcément valide.
- **Ports** (`Exemple/Repositories`) : les interfaces dont le métier a besoin (`ExempleRepository` : `lister`, `trouver`, `creer`). Elles parlent en entités, jamais en lignes SQL ou en modèles Eloquent.
- **Erreurs** (`Shared/Errors`) : leur message est destiné à l'utilisateur.
  - `DomainError` : la classe de base.
  - `BusinessRuleViolation` : une règle métier n'est pas respectée (422 dans l'API).
  - `NotFoundError` : une ressource est introuvable (404).
- **Interdits** : aucune classe `Illuminate\…`, aucune facade, aucun accès à la base, à la session ou à la requête HTTP. Ce sont des classes PHP ordinaires.

*Y mettre* : les règles et calculs métier (statuts, montants, dates, validations métier…).

#### Application — `app/Application`

Les **cas d'usage** : une classe par action de l'utilisateur, avec une méthode `execute()`.

- `CreerExempleUseCase` : nettoie et valide les données, puis crée via le repository.
- `ListerExemplesUseCase` : borne la pagination (page ≥ 1, au plus `PAR_PAGE_MAX` = 100 éléments) puis liste.
- `ObtenirExempleUseCase` : retourne l'exemple ou lève `NotFoundError`.
- **DTO d'entrée** (`DTO/CreerExempleInput`) : un objet simple qui transporte les données d'entrée. Le cas d'usage ne reçoit jamais une `Request` Laravel.
- Les dépendances (ports du Domain) arrivent **par le constructeur**. Le cas d'usage ignore donc s'il parle à Eloquent, à des procédures stockées ou à un faux repository de test.
- **Interdits** : comme pour le Domain, pas de Laravel. Seuls le Domain et PHP sont autorisés.

*Y mettre* : l'orchestration d'une action (vérifier, enchaîner plusieurs repositories ou services, décider quoi enregistrer). Une transaction ou un envoi d'e-mail passe par un port déclaré dans le Domain et implémenté dans l'Infrastructure.

#### Infrastructure — `app/Infrastructure`

Les **adaptateurs** : l'implémentation technique des ports du Domain.

- Ils traduisent les appels du domaine en requêtes vers la base, et les résultats en entités.
- Ils traduisent les erreurs techniques *attendues* en erreurs métier (doublon → `BusinessRuleViolation`). Les erreurs inattendues remontent telles quelles (500).
<!-- @feature data:eloquent -->
- **Eloquent** :
  - `Models/ExempleModel` est un détail de persistance, jamais exposé hors de cette couche.
  - `EloquentExempleRepository` fait les requêtes (`orderBy`, `forPage`, `find`, `create`) et convertit chaque modèle en entité `Exemple`.
  - Une violation de contrainte d'unicité devient `BusinessRuleViolation('Ce libellé existe déjà.')`.
  - Le schéma est décrit par les migrations (`database/migrations`), lancées avec `php artisan migrate`.
<!-- @endfeature -->
<!-- @feature data:procedures -->
- **Procédures stockées PostgreSQL** :
  - `ProcedureExecutor` est le port technique ; `PdoProcedureExecutor` l'implémente. Il construit l'appel (`ProcedureCallBuilder`), type les paramètres (`bool`, `int`, `null`, chaîne) et lit tous les jeux de résultats.
  - `StoredProcedureException` classe les erreurs. Un `RAISE EXCEPTION 'message'` (SQLSTATE P0001) devient une `BusinessRuleViolation` dont le message est nettoyé de `ERROR:`/`CONTEXT:`. Une fonction absente ou une erreur SQL reste une erreur technique (500).
  - `StoredProcedureExempleRepository` appelle `ps_exemple_liste`, `ps_exemple_detail` et `ps_exemple_creer`, puis mappe les lignes en entités.

  Les procédures qui retournent des données sont des **fonctions** (`RETURNS TABLE` / `SETOF`), appelées par `SELECT * FROM ma_fonction(...)` :

  ```php
  $this->sp->call('ps_commandes_liste', [$page, $parPage]);   // toutes les lignes
  $this->sp->first('ps_commande_detail', ['p_id' => $id]);     // 1re ligne ou null (paramètres nommés)
  ```

  - Les paramètres sont positionnels (`[1, 20]`) ou nommés (`['p_id' => 1]` → `p_id => ?`).
  - Le nom de fonction est validé (identifiant + schéma optionnel, ex. `vente.ps_x`), ce qui empêche l'injection.
  - Les fonctions d'exemple utilisées par les tests sont dans `tests/Integration/fixtures/pgsql/exemple.sql`.
<!-- @endfeature -->

*Y mettre* : tout ce qui parle au monde extérieur (base, API tierces, paiement, SMS, fichiers, cache…). On déclare toujours l'interface dans le Domain et l'implémentation ici.

<!-- @feature api -->
#### Api — `app/Api`

La présentation **HTTP JSON**, versionnée (`routes/api.php` → `/api/v1/…`).

- **Contrôleurs** (`Controllers/V1`) : volontairement fins. Ils valident (FormRequest), appellent un cas d'usage puis renvoient une `ApiResponse`. Aucune logique métier ni requête SQL.
- **Requests** (`Requests/…`) : règles de validation Laravel et conversion en DTO (`toInput()`).
- **ApiResponse** : le format unique de toutes les réponses.

  ```json
  { "success": true,  "message": "OK", "data": { } }
  { "success": false, "message": "Ce libellé existe déjà." }
  { "success": false, "message": "Les données envoyées sont invalides.", "errors": { "montant": ["…"] } }
  ```

- **ApiExceptionRenderer** : convertit toute exception levée sous `/api/*` en JSON, même sans en-tête `Accept`.
  - Validation → 422 + `errors`.
  - `BusinessRuleViolation` → 422, avec le message tel quel.
  - `NotFoundError` ou route inconnue → 404.
  - Méthode non autorisée → 405.
  - Autre erreur → 500, détail masqué si `APP_DEBUG=false`.
- **OpenApi** : chaque route est documentée par des attributs `#[OA\...]` sur les contrôleurs, et la définition globale est dans `OpenApi/OpenApiSpec.php`.
  - Swagger UI : <http://localhost:8000/documentation>. Spécification JSON : <http://localhost:8000/docs>, à importer côté client pour générer les appels.
  - En local, la doc se régénère à chaque visite (`L5_SWAGGER_GENERATE_ALWAYS=true`). En production, lancer `php artisan l5-swagger:generate` au déploiement.
- **CORS** : les origines autorisées à appeler l'API depuis un navigateur sont listées dans `CORS_ALLOWED_ORIGINS`. Les applications mobiles natives ne sont pas concernées.
- `GET /api/v1/health` donne l'état de l'API et de la connexion à la base, pour la supervision.
<!-- @endfeature -->

<!-- @feature ui:livewire -->
#### Web — `app/Web` (Livewire)

La présentation **web**, rendue côté serveur avec Livewire.

- **Composants** (`Livewire/Exemples/Index`) : ils jouent le même rôle qu'un contrôleur fin. Les propriétés publiques sont liées au formulaire (`wire:model`) et validées par `#[Validate]`. Une action (`creer()`) appelle un cas d'usage et l'état est ré-affiché.
  - Une `DomainError` est interceptée et affichée dans l'erreur `erreur` du formulaire.
  - La page courante est conservée dans l'URL (`#[Url]`).
- **Vues** : `resources/views/livewire/…` pour les composants, `resources/views/layouts/app.blade.php` pour le layout. Le style est assuré par Tailwind CSS, compilé par Vite.
- `config/livewire.php` place les composants dans `app/Web/Livewire`. `php artisan make:livewire Nom` les crée donc directement au bon endroit.
- **WebExceptionRenderer** : hors composants, une `BusinessRuleViolation` ramène au formulaire avec le message, et une `NotFoundError` donne une page 404.
- Les routes sont dans `routes/web.php` (`/` redirige vers `/exemples`).
<!-- @endfeature -->

<!-- @feature ui:inertia -->
#### Web — `app/Web` (Inertia)

La présentation **web** : des contrôleurs Laravel qui renvoient des pages JavaScript, sans API intermédiaire.

<!-- @endfeature -->
<!-- @feature ui:vue -->
Les pages sont des composants **Vue**.

<!-- @endfeature -->
<!-- @feature ui:react -->
Les pages sont des composants **React**.

<!-- @endfeature -->
<!-- @feature ui:inertia -->
- **Contrôleurs** (`Controllers/ExempleController`) : volontairement fins. Ils valident (FormRequest dans `Requests/`), appellent un cas d'usage, puis font `Inertia::render('Exemples/Index', [...])` ou redirigent avec un message flash.
- **Pages** : `resources/js/pages/…`. `Inertia::render('Exemples/Index')` correspond à `resources/js/pages/Exemples/Index.*`.
  - Les formulaires utilisent `useForm`. Les erreurs de validation arrivent dans `errors.<champ>` et l'erreur métier dans `errors.erreur`.
- **HandleInertiaRequests** : les données partagées avec toutes les pages (`appName`, `flash.succes`). La vue racine est `resources/views/app.blade.php`.
- **WebExceptionRenderer** : une `BusinessRuleViolation` ramène au formulaire avec le message, et une `NotFoundError` donne une page 404.
- Les routes sont dans `routes/web.php` (`/` redirige vers `/exemples`). Les assets sont compilés par Vite, avec Tailwind CSS.
<!-- @endfeature -->

#### Providers — `app/Providers`

- **CleanArchitectureServiceProvider** : la table `$bindings` relie chaque port à son adaptateur (`ExempleRepository::class => …Repository::class`). Pour changer d'implémentation, on ne modifie que cette ligne.
- **AppServiceProvider** : la configuration générale de l'application.

#### Règles vérifiées automatiquement

`tests/Unit/ArchitectureTest.php` analyse les `use` de chaque fichier et échoue si :

- `Domain` importe Laravel (`Illuminate\…`), `Application`, `Infrastructure`, `Api` ou `Web` ;
- `Application` importe Laravel, `Infrastructure`, `Api` ou `Web` ;
- `Infrastructure` importe `Api` ou `Web` ;
- `Api` importe `Infrastructure` ou `Web`, ou `Web` importe `Infrastructure` ou `Api`. La présentation passe toujours par les cas d'usage.

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

### La stratégie

Chaque couche a **sa propre suite de tests**. Plus une couche est interne, plus ses tests sont nombreux, rapides et indépendants du framework :

```
                ┌───────────────┐
                │  Integration  │  vraie base de données          → lent, peu nombreux
             ┌──┴───────────────┴──┐
             │  Feature / Api / Web │  HTTP ou pages, de bout en bout → stockage en mémoire
          ┌──┴─────────────────────┴──┐
          │       Application          │  cas d'usage                → sans Laravel ni base
       ┌──┴────────────────────────────┴──┐
       │              Unit                 │  domaine, architecture   → millisecondes
       └───────────────────────────────────┘
```

- **Le stockage est remplacé par un faux en mémoire** dans toutes les suites, sauf Integration. `tests/Support/Fakes/InMemoryExempleRepository` implémente le même port `ExempleRepository` que le vrai repository. C'est l'intérêt direct de la clean architecture : on teste toute l'application sans base de données, donc vite et de façon fiable.
- Un test Laravel remplace le repository par le faux avec `$this->app->instance(ExempleRepository::class, new InMemoryExempleRepository)`. Pour simuler une erreur précise (doublon, panne…), on utilise un mock Mockery.
- La configuration de test est dans `phpunit.xml` : SQLite en mémoire, cache et session en tableau, e-mails non envoyés.

### Lancer les tests

```bash
php artisan test                         # toutes les suites
php artisan test --testsuite=Unit        # une suite (Unit, Application, Integration…)
php artisan test --filter=MontantTest    # une classe ou une méthode
composer test:unit                       # raccourcis : test:application, test:integration…
```

### Les suites en détail

#### Unit — `tests/Unit`

Ce sont des classes testées isolément, la plupart sans démarrer Laravel (`PHPUnit\Framework\TestCase`).

- `Domain/MontantTest` : arrondi à 2 décimales, égalité par valeur, refus d'un montant non numérique ou négatif.
- `Domain/ExempleTest` : sérialisation de l'entité en tableau, refus d'un libellé vide.
- `ArchitectureTest` : le respect des dépendances entre couches (voir « Règles vérifiées automatiquement »).
<!-- @feature api -->
- `Api/ApiExceptionRendererTest` : traduction des exceptions en JSON. Une erreur métier donne 422 avec son message, une ressource introuvable donne 404. Une erreur technique donne 500, avec un message masqué hors debug et détaillé en debug.
<!-- @endfeature -->
<!-- @feature data:procedures -->
- `Infrastructure/ProcedureCallBuilderTest` : le SQL généré pour PostgreSQL (paramètres positionnels et nommés), MySQL/MariaDB et SQL Server. Un driver non supporté est refusé, ainsi que toute tentative d'injection dans un nom de fonction ou de paramètre.
- `Infrastructure/StoredProcedureExceptionTest` : le classement erreur métier / erreur technique selon le SQLSTATE (`P0001`, `45000`, code ≥ 50000), et le nettoyage du message PostgreSQL (`ERROR:`, `CONTEXT:`).
<!-- @endfeature -->

*Y ajouter* : un test par règle métier (entité, value object), et un test pour chaque composant technique sans dépendance externe.

#### Application — `tests/Application`

Ces tests vérifient les cas d'usage avec le repository en mémoire, sans HTTP ni base de données.

- `CreerExempleUseCaseTest` : le libellé est nettoyé et le montant arrondi. Un montant négatif est refusé **sans appeler le repository**, et un libellé vide est refusé.
- `ListerExemplesUseCaseTest` : la page demandée est retournée, et la pagination est normalisée (page 0 → 1, taille > 100 → 100).
- `ObtenirExempleUseCaseTest` : l'exemple est retourné, ou `NotFoundError` est levée s'il est absent.

*Y ajouter* : un test par cas d'usage, qui couvre le chemin normal et chaque refus.

<!-- @feature api -->
#### Feature — `tests/Feature`

Ce sont des **scénarios utilisateur complets**, qui enchaînent plusieurs appels à l'API. Toutes les couches sont réelles sauf le stockage, qui est en mémoire.

- `Exemple/GestionExemplesTest` :
  - créer, puis consulter, puis lister, en vérifiant le nettoyage du libellé et l'arrondi du montant ;
  - parcourir les pages ;
  - vérifier qu'une création refusée ne laisse aucune trace.

*Y ajouter* : un scénario par parcours métier important.

#### Api — `tests/Api`

Ces tests vérifient le **contrat HTTP** de l'API, tel que le voient ses clients.

- `V1/ExempleApiTest` :
  - la structure JSON de la liste, du détail et de la création (201) ;
  - les validations en 422 (pagination invalide, données invalides, corps vide) ;
  - 404 pour un id introuvable ou non numérique, 405 pour une méthode non autorisée ;
  - une erreur métier du stockage en 422 avec son message, une erreur technique masquée en 500 ;
  - une réponse JSON même sans en-tête `Accept`.
- `V1/HealthTest` : le format de `/api/v1/health`, une route inconnue en JSON 404, les en-têtes CORS pour les origines configurées.
- `DocumentationTest` : Swagger UI est servi, la spécification OpenAPI est accessible, et **chaque route `/api/v1` est documentée**. Une route ajoutée sans `#[OA\...]` fait échouer ce test.

*Y ajouter* : les codes, la structure JSON et les erreurs de chaque nouvel endpoint.
<!-- @endfeature -->

<!-- @feature ui -->
#### Web — `tests/Web`

Ces tests vérifient l'**interface web** : pages, formulaires, validation et erreurs, avec le stockage en mémoire. Vite est désactivé (`withoutVite()`), il n'est donc pas nécessaire de compiler les assets pour les lancer.

<!-- @endfeature -->
<!-- @feature ui:livewire -->
- `Exemple/ExemplesLivewireTest`, avec `Livewire::test()` :
  - la page s'affiche avec le composant, et l'accueil redirige vers `/exemples` ;
  - la création fonctionne (champs vidés, libellé nettoyé, montant formaté) ;
  - les erreurs de validation sont signalées ;
  - une erreur métier est affichée ;
  - la pagination fonctionne.
<!-- @endfeature -->
<!-- @feature ui:inertia -->
- `Exemple/ExemplesInertiaTest`, avec `assertInertia()` :
  - le composant de page et ses props (`exemples`, `page`, `parPage`) ;
  - la redirection de l'accueil ;
  - la création, suivie d'une redirection et du message flash ;
  - les erreurs de validation en session ;
  - une erreur métier renvoyée au formulaire (`errors.erreur`) ;
  - la pagination.
<!-- @endfeature -->
<!-- @feature ui -->

*Y ajouter* : pour chaque écran, l'affichage, la soumission valide, les erreurs de validation et les erreurs métier.
<!-- @endfeature -->

#### Integration — `tests/Integration`

Ces tests utilisent la **vraie base de données** et les vrais adaptateurs de l'Infrastructure. C'est la seule suite qui vérifie le SQL réellement exécuté.

<!-- @feature data:eloquent -->
- `Exemple/EloquentExempleRepositoryTest`, sur SQLite en mémoire avec les migrations du projet (`RefreshDatabase`) :
  - le conteneur fournit bien le repository Eloquent ;
  - une création est convertie en entité et présente en base ;
  - la recherche et la pagination fonctionnent ;
  - un libellé en double devient une erreur métier.
<!-- @endfeature -->
<!-- @feature data:eloquent & api -->
  - Un test de bout en bout passe par HTTP jusqu'à la base, avec le doublon renvoyé en 422.
<!-- @endfeature -->
<!-- @feature data:procedures -->
- Ces tests utilisent un **vrai PostgreSQL**. Les fonctions de test sont chargées depuis `tests/Integration/fixtures/pgsql/exemple.sql` avant chaque test.
  - `StoredProcedure/PdoProcedureExecutorTest` :
    - lecture des lignes, `first()` qui retourne `null` s'il n'y a aucune ligne ;
    - paramètres nommés, typage des booléens et de `null` ;
    - `RAISE EXCEPTION` transformé en erreur métier, fonction inexistante en erreur technique.
  - `Exemple/StoredProcedureExempleRepositoryTest` : la conversion en entité, la recherche et la pagination.
<!-- @endfeature -->
<!-- @feature data:procedures & api -->
    Il contient aussi un test de bout en bout, de HTTP jusqu'aux fonctions PL/pgSQL, avec le doublon renvoyé en 422.
<!-- @endfeature -->
<!-- @feature data:procedures -->
- La connexion se configure dans `phpunit.xml` avec les variables `INTEGRATION_DB_*` (par défaut `template_app_test` sur le port 5432, utilisateur `template_app`). Pour préparer la base en local :

  ```bash
  createuser -P template_app                        # mot de passe : template_app
  createdb -O template_app template_app_test
  ```

- Si la base est injoignable, ces tests sont **ignorés** (et non en échec), pour pouvoir lancer les autres suites sans PostgreSQL. En CI, définir `INTEGRATION_DB_REQUIRED=true` : une base absente fait alors échouer la suite.
<!-- @endfeature -->

*Y ajouter* : pour chaque repository, la conversion en entités, les requêtes (filtres, pagination) et la traduction des erreurs de la base.

### Écrire les tests d'une nouvelle ressource

1. **Unit** : les règles de l'entité et des value objects.
2. **Application** : chaque cas d'usage, avec un faux repository en mémoire dans `tests/Support/Fakes`.
<!-- @feature api -->
3. **Api** : le contrat de chaque endpoint. **Feature** : les parcours complets.
<!-- @endfeature -->
<!-- @feature ui -->
3. **Web** : chaque écran et formulaire.
<!-- @endfeature -->
4. **Integration** : le repository réel contre la base.
