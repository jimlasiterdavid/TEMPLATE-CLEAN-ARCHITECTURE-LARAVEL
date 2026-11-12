<?php

/*
|--------------------------------------------------------------------------
| Générateur de projet — template-clean-architecture-laravel
|--------------------------------------------------------------------------
|
| Assemble un projet à partir du template selon les choix :
|   - interface web : aucune, Livewire, Inertia + Vue, Inertia + React
|   - API REST + Swagger : oui / non
|   - accès aux données : Eloquent ou procédures stockées PostgreSQL
|   - base de données : PostgreSQL, MySQL, MariaDB, SQLite
|
| Les paquets propres à chaque option sont installés au moment de la création,
| dans leur dernière version compatible : rien n'est figé dans le template.
|
| Usage : php bin/init.php [nom] [options]   (php bin/init.php --help)
|
| Pour ajouter une option : compléter UIS / DATA / DBS ci-dessous, déposer les
| fichiers dans le template et les déclarer dans PATH_FEATURES, puis entourer
| les lignes conditionnelles des fichiers partagés de marqueurs :
|     // @feature ui:vue | ui:react       (expressions : & | !)
|     ...
|     // @endfeature
| (#, <!-- -->, {{-- --}} et les commentaires bloc C sont aussi reconnus.)
|
| ⚠ Ne pas déclarer de script composer nommé "init" : Composer déclenche un
|   événement "init" à chaque commande, ce qui lancerait ce script.
*/

const TEMPLATE_DIR = 'template-clean-architecture-laravel';
const MARKER_STUDLY = 'TemplateApp';
const MARKER_SNAKE = 'template_app';
const MARKER_KEBAB = 'template-app';

/** Paquets npm communs à toutes les interfaces (installés en devDependencies). */
const UI_NPM_DEV = ['vite', 'laravel-vite-plugin', 'tailwindcss', '@tailwindcss/vite'];

const UIS = [
    'none' => [
        'label' => 'Aucune — API seule ou front séparé (Angular, Flutter, React, Vue…)',
        'features' => [],
    ],
    'livewire' => [
        'label' => 'Livewire (Blade)',
        'features' => ['ui', 'ui:livewire'],
        'composer' => ['livewire/livewire'],
    ],
    'vue' => [
        'label' => 'Inertia + Vue',
        'features' => ['ui', 'ui:inertia', 'ui:vue'],
        'composer' => ['inertiajs/inertia-laravel'],
        'npm' => ['vue', '@inertiajs/vue3'],
        'npm-dev' => ['@vitejs/plugin-vue'],
    ],
    'react' => [
        'label' => 'Inertia + React',
        'features' => ['ui', 'ui:inertia', 'ui:react'],
        'composer' => ['inertiajs/inertia-laravel'],
        'npm' => ['react', 'react-dom', '@inertiajs/react'],
        'npm-dev' => ['@vitejs/plugin-react'],
    ],
];

const API_COMPOSER = ['darkaonline/l5-swagger'];

const DATA = [
    'eloquent' => ['label' => 'Eloquent (migrations + modèles)'],
    'procedures' => ['label' => 'Procédures stockées PostgreSQL', 'db' => ['pgsql']],
];

const DBS = [
    'pgsql' => 'PostgreSQL',
    'mysql' => 'MySQL',
    'mariadb' => 'MariaDB',
    'sqlite' => 'SQLite',
];

/** Fichiers et dossiers conservés seulement si l'expression est vraie. */
const PATH_FEATURES = [
    // API REST + Swagger
    'app/Api' => 'api',
    'routes/api.php' => 'api',
    'config/l5-swagger.php' => 'api',
    'tests/Api' => 'api',
    'tests/Feature' => 'api',
    'tests/Unit/Api' => 'api',
    // Procédures stockées
    'app/Infrastructure/Database' => 'data:procedures',
    'app/Infrastructure/Exemple/Repositories/StoredProcedureExempleRepository.php' => 'data:procedures',
    'tests/Unit/Infrastructure' => 'data:procedures',
    'tests/Integration/IntegrationTestCase.php' => 'data:procedures',
    'tests/Integration/StoredProcedure' => 'data:procedures',
    'tests/Integration/Exemple/StoredProcedureExempleRepositoryTest.php' => 'data:procedures',
    'tests/Integration/fixtures' => 'data:procedures',
    // Eloquent
    'app/Infrastructure/Exemple/Models' => 'data:eloquent',
    'app/Infrastructure/Exemple/Repositories/EloquentExempleRepository.php' => 'data:eloquent',
    'database/migrations/2026_01_01_000000_create_exemples_table.php' => 'data:eloquent',
    'tests/Integration/Exemple/EloquentExempleRepositoryTest.php' => 'data:eloquent',
    // Interfaces web
    'app/Web' => 'ui',
    'app/Web/Livewire' => 'ui:livewire',
    'config/livewire.php' => 'ui:livewire',
    'app/Web/Controllers' => 'ui:inertia',
    'app/Web/Requests' => 'ui:inertia',
    'app/Web/Middleware' => 'ui:inertia',
    'resources' => 'ui',
    'resources/views/livewire' => 'ui:livewire',
    'resources/views/layouts' => 'ui:livewire',
    'resources/views/app.blade.php' => 'ui:inertia',
    'resources/js' => 'ui:inertia',
    'resources/js/app.js' => 'ui:vue',
    'resources/js/Pages/Exemples/Index.vue' => 'ui:vue',
    'resources/js/app.jsx' => 'ui:react',
    'resources/js/Pages/Exemples/Index.jsx' => 'ui:react',
    'vite.config.js' => 'ui',
    'tests/Web' => 'ui',
    'tests/Web/Exemple/ExemplesLivewireTest.php' => 'ui:livewire',
    'tests/Web/Exemple/ExemplesInertiaTest.php' => 'ui:inertia',
    // Propre au dépôt du template
    '.github/workflows/template.yml' => 'template',
];

/** Scripts composer retirés si l'expression est fausse. */
const SCRIPT_FEATURES = [
    'test:feature' => 'api',
    'test:api' => 'api',
    'test:web' => 'ui',
];

$root = dirname(__DIR__);
chdir($root);

[$positional, $options] = parseArgs(array_slice($argv, 1));

if (isset($options['help']) || isset($options['h'])) {
    echo <<<'TXT'
    Usage : php bin/init.php [nom-du-projet] [options]

      nom-du-projet   mon-projet, mon_projet, "Mon Projet" ou MonProjet (défaut : nom du dossier)

    Choix (posés en question si absents, en mode interactif) :
      --ui=none|livewire|vue|react    interface web (défaut : none)
      --api / --no-api                exposer une API REST + Swagger (défaut : oui)
      --data=eloquent|procedures      accès aux données (défaut : eloquent)
      --db=pgsql|mysql|mariadb|sqlite base de données (défaut : pgsql)

    Autres options :
      --vendor=acme      préfixe du paquet composer (défaut : nom du projet)
      --author="Nom"     auteur (défaut : git config user.name)
      --email=mail       e-mail de l'auteur (défaut : git config user.email)
      --no-install       ne pas lancer composer / npm (à faire ensuite)
      -n, --defaults     ne poser aucune question (valeurs par défaut)

    TXT;
    exit(0);
}

if (! file_exists($root.'/.template')) {
    fail('Ce projet a déjà été initialisé (fichier .template absent).');
}

if (strcasecmp(basename($root), TEMPLATE_DIR) === 0) {
    fail('Refus : on est dans le dépôt du template lui-même. Créez un projet avec bin/create-project.sh <nom>.');
}

$tty = (isset($options['n']) || isset($options['defaults']) || isset($options['no-interaction'])) ? null : openTty();

// ---------------------------------------------------------------------------
// 1. Choix
// ---------------------------------------------------------------------------

$rawName = $positional[0] ?? ($tty ? ask($tty, 'Nom du projet', basename($root)) : basename($root));
$words = splitWords($rawName);

if ($words === [] || preg_match('/^[0-9]/', $words[0])) {
    fail("Nom de projet invalide : « {$rawName} » (il doit commencer par une lettre).");
}

$ui = option($options, 'ui', array_keys(UIS), 'none', $tty, 'Interface web',
    array_map(fn ($u) => $u['label'], UIS));

if (isset($options['api'])) {
    $api = true;
} elseif (isset($options['no-api'])) {
    $api = false;
} else {
    $api = $ui === 'none' || ! $tty
        ? true
        : confirm($tty, 'Exposer aussi une API REST + Swagger (ex. pour une app mobile) ?', false);
}

if ($ui === 'none' && ! $api) {
    info("Ni interface ni API : le projet contiendra le cœur (Domain, Application, Infrastructure) à brancher sur le front de votre choix.");
}

$data = option($options, 'data', array_keys(DATA), 'eloquent', $tty, 'Accès aux données',
    array_map(fn ($d) => $d['label'], DATA));

$allowedDbs = DATA[$data]['db'] ?? array_keys(DBS);
$db = option($options, 'db', $allowedDbs, $allowedDbs[0], $tty, 'Base de données',
    array_intersect_key(DBS, array_flip($allowedDbs)));

$features = array_merge(
    UIS[$ui]['features'],
    $api ? ['api'] : [],
    ["data:{$data}", "db:{$db}"],
);

$studly = implode('', array_map('ucfirst', $words));
$snake = implode('_', $words);
$kebab = implode('-', $words);
$vendor = slug((string) ($options['vendor'] ?? $kebab));
$author = (string) ($options['author'] ?? trim((string) @shell_exec('git config user.name 2>/dev/null')));
$email = (string) ($options['email'] ?? trim((string) @shell_exec('git config user.email 2>/dev/null')));
$install = ! isset($options['no-install']);

echo "\n";
info("Projet           : {$studly} ({$vendor}/{$kebab})");
info('Interface web    : '.UIS[$ui]['label']);
info('API REST/Swagger : '.($api ? 'oui' : 'non'));
info('Données          : '.DATA[$data]['label']);
info('Base             : '.DBS[$db]." ({$snake})");
echo "\n";

// ---------------------------------------------------------------------------
// 2. Assemblage des fichiers
// ---------------------------------------------------------------------------

foreach (PATH_FEATURES as $path => $expr) {
    if (! evaluate($expr, $features)) {
        removePath($root.'/'.$path);
    }
}

$replacements = [
    MARKER_STUDLY => $studly,
    MARKER_SNAKE => $snake,
    MARKER_KEBAB => $kebab,
];

$excluded = ['.git', 'vendor', 'node_modules', 'storage', 'bootstrap/cache', 'bin'];

foreach (projectFiles($root, $excluded) as $file) {
    $content = file_get_contents($file);

    if ($content === false || str_contains($content, "\0")) {
        continue; // binaire
    }

    $new = applyFeatureBlocks($content, $features, $file);

    if (basename($file) === 'README.md') {
        $new = preg_replace('/<!-- template:start -->.*?<!-- template:end -->\n*/s', '', $new);
        $new = str_replace('{{AUTHOR}}', $author !== '' ? $author : 'à compléter', $new);
    }

    $new = strtr($new, $replacements);

    if ($new !== $content) {
        file_put_contents($file, $new);
    }
}

// composer.json : nom, auteur, scripts.
$composer = readJson($root.'/composer.json');
$composer['name'] = "{$vendor}/{$kebab}";
$composer['description'] = "{$studly} — application Laravel en clean architecture.";
$composer['keywords'] = array_values(array_unique(array_merge(
    ['laravel', 'clean-architecture'],
    $api ? ['api', 'swagger'] : [],
    $ui === 'livewire' ? ['livewire'] : [],
    in_array($ui, ['vue', 'react'], true) ? ['inertia', $ui] : [],
)));
unset($composer['scripts']['post-create-project-cmd']);

foreach (SCRIPT_FEATURES as $script => $expr) {
    if (! evaluate($expr, $features)) {
        unset($composer['scripts'][$script]);
    }
}

$setup = ['composer install', "@php -r \"file_exists('.env') || copy('.env.example', '.env');\"", '@php artisan key:generate'];
if ($db === 'sqlite') {
    $setup[] = "@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"";
}
if ($data === 'eloquent') {
    $setup[] = '@php artisan migrate --graceful';
}
if ($ui !== 'none') {
    $setup[] = 'npm install';
    $setup[] = 'npm run build';
}
$composer['scripts']['setup'] = $setup;

if ($author !== '') {
    $composer['authors'] = [array_filter(['name' => $author, 'email' => $email])];
}
writeJson($root.'/composer.json', $composer);

// package.json (dépendances ajoutées par npm install, dernières versions).
if ($ui !== 'none') {
    writeJson($root.'/package.json', [
        '$schema' => 'https://www.schemastore.org/package.json',
        'private' => true,
        'type' => 'module',
        'scripts' => ['build' => 'vite build', 'dev' => 'vite'],
    ]);
}

if (! file_exists($root.'/.env')) {
    copy($root.'/.env.example', $root.'/.env');
}

if ($db === 'sqlite' && ! file_exists($root.'/database/database.sqlite')) {
    touch($root.'/database/database.sqlite');
}

// Nettoyage des fichiers propres au template.
@unlink($root.'/.template');
removePath($root.'/.github/workflows/template.yml');
@rmdir($root.'/.github/workflows');
@rmdir($root.'/.github');

info('Fichiers du projet assemblés.');

// ---------------------------------------------------------------------------
// 3. Installation (dernières versions compatibles)
// ---------------------------------------------------------------------------

$composerPackages = array_values(array_unique(array_merge(
    UIS[$ui]['composer'] ?? [],
    $api ? API_COMPOSER : [],
)));
$npmPackages = UIS[$ui]['npm'] ?? [];
$npmDevPackages = $ui === 'none' ? [] : array_merge(UI_NPM_DEV, UIS[$ui]['npm-dev'] ?? []);

$todo = [];

if ($install) {
    $composerCmd = composerCommand();

    if ($composerPackages !== []) {
        step('Ajout des paquets : '.implode(', ', $composerPackages));
        run("{$composerCmd} require ".implode(' ', array_map('escapeshellarg', $composerPackages)).' --no-update --no-interaction');
    }

    step('Installation des dépendances PHP');
    run("{$composerCmd} update --no-interaction --ansi");

    run(escapeshellarg(PHP_BINARY).' artisan key:generate --ansi');

    if ($data === 'eloquent') {
        if ($db === 'sqlite') {
            run(escapeshellarg(PHP_BINARY).' artisan migrate --force --ansi');
        } else {
            $todo[] = 'créer la base « '.$snake.' » puis : php artisan migrate';
        }
    }

    if ($ui !== 'none') {
        if (commandExists('npm')) {
            step('Installation des dépendances front (npm)');
            run('npm install --no-fund --no-audit --save-dev '.implode(' ', array_map('escapeshellarg', $npmDevPackages)));
            if ($npmPackages !== []) {
                run('npm install --no-fund --no-audit '.implode(' ', array_map('escapeshellarg', $npmPackages)));
            }
            run('npm run build');
        } else {
            warn('npm introuvable : installez Node.js puis lancez « npm install '.implode(' ', $npmPackages).' && npm install -D '.implode(' ', $npmDevPackages).' && npm run build ».');
        }
    }
} else {
    if ($composerPackages !== []) {
        $todo[] = 'composer require '.implode(' ', $composerPackages);
    }
    $todo[] = 'composer update && php artisan key:generate';
    if ($ui !== 'none') {
        $todo[] = 'npm install -D '.implode(' ', $npmDevPackages).($npmPackages ? ' && npm install '.implode(' ', $npmPackages) : '').' && npm run build';
    }
    if ($data === 'eloquent') {
        $todo[] = 'php artisan migrate';
    }
}

if ($data === 'procedures') {
    $todo[] = 'créer la base PostgreSQL « '.$snake.' » et ses fonctions (voir README)';
}

if ($db !== 'sqlite') {
    array_unshift($todo, 'renseigner DB_* dans .env');
}

// Le générateur se supprime en dernier.
removePath($root.'/bin');

echo "\n";
info("Projet « {$studly} » prêt.");
foreach ($todo as $item) {
    echo "   • {$item}\n";
}

exit(0);

// ===========================================================================

function parseArgs(array $args): array
{
    $positional = [];
    $options = [];

    foreach ($args as $arg) {
        if (preg_match('/^--?([^=]+)(?:=(.*))?$/s', $arg, $m)) {
            $options[$m[1]] = $m[2] ?? true;
        } else {
            $positional[] = $arg;
        }
    }

    return [$positional, $options];
}

/** Valeur d'une option : argument, sinon question, sinon défaut. */
function option(array $options, string $key, array $allowed, string $default, $tty, string $question, array $labels): string
{
    if (isset($options[$key])) {
        $value = strtolower((string) $options[$key]);
        $aliases = ['postgres' => 'pgsql', 'postgresql' => 'pgsql', 'sp' => 'procedures', 'inertia-vue' => 'vue', 'inertia-react' => 'react', 'api' => 'none'];
        $value = $aliases[$value] ?? $value;

        if (! in_array($value, $allowed, true)) {
            fail("--{$key}={$options[$key]} invalide. Valeurs possibles : ".implode(', ', $allowed).'.');
        }

        return $value;
    }

    if (count($allowed) === 1 || ! $tty) {
        return in_array($default, $allowed, true) ? $default : $allowed[0];
    }

    return choose($tty, $question, $labels, $default);
}

function openTty()
{
    if (DIRECTORY_SEPARATOR === '\\') {
        return stream_isatty(STDIN) ? STDIN : null;
    }

    $tty = @fopen('/dev/tty', 'r');

    return $tty ?: null;
}

function ask($tty, string $question, string $default): string
{
    echo "\033[36m?\033[0m {$question} [{$default}] : ";
    $line = trim((string) fgets($tty));

    return $line === '' ? $default : $line;
}

function choose($tty, string $question, array $labels, string $default): string
{
    $keys = array_keys($labels);

    while (true) {
        echo "\033[36m?\033[0m {$question}\n";
        foreach ($keys as $i => $key) {
            $mark = $key === $default ? ' (défaut)' : '';
            echo '   '.($i + 1).") {$labels[$key]}{$mark}\n";
        }
        echo '   Choix [1-'.count($keys).'] : ';

        $line = strtolower(trim((string) fgets($tty)));

        if ($line === '') {
            return $default;
        }
        if (ctype_digit($line) && isset($keys[(int) $line - 1])) {
            return $keys[(int) $line - 1];
        }
        if (in_array($line, $keys, true)) {
            return $line;
        }

        warn('Choix invalide.');
    }
}

function confirm($tty, string $question, bool $default): bool
{
    echo "\033[36m?\033[0m {$question} ".($default ? '[O/n]' : '[o/N]').' : ';
    $line = strtolower(trim((string) fgets($tty)));

    return $line === '' ? $default : in_array($line, ['o', 'oui', 'y', 'yes'], true);
}

/** Expression : termes séparés par & (et) et | (ou), préfixe ! (non). « a & b | c ». */
function evaluate(string $expr, array $features): bool
{
    foreach (preg_split('/\s*\|\s*/', trim($expr)) as $alternative) {
        $ok = true;

        foreach (preg_split('/\s*&\s*/', $alternative) as $term) {
            $negate = str_starts_with($term, '!');
            $name = trim(ltrim($term, '!'));

            if (in_array($name, $features, true) === $negate) {
                $ok = false;
                break;
            }
        }

        if ($ok) {
            return true;
        }
    }

    return false;
}

/** Conserve ou retire les blocs « @feature expr … @endfeature ». */
function applyFeatureBlocks(string $content, array $features, string $file): string
{
    if (! str_contains($content, '@feature') && ! str_contains($content, '@endfeature')) {
        return $content;
    }

    $comment = '(?:\/\/|#|<!--|\{\{--|\/\*)';
    $close = '\s*(?:-->|--\}\}|\*\/)?\s*$';
    $lines = preg_split('/(?<=\n)/', $content);
    $out = '';
    $keep = null;

    foreach ($lines as $line) {
        if (preg_match('/^\s*'.$comment.'\s*@feature\s+(.+?)'.$close.'/', $line, $m)) {
            if ($keep !== null) {
                fail("Marqueur @feature imbriqué dans {$file}.");
            }
            $keep = evaluate($m[1], $features);

            continue;
        }

        if (preg_match('/^\s*'.$comment.'\s*@endfeature'.$close.'/', $line)) {
            if ($keep === null) {
                fail("@endfeature sans @feature dans {$file}.");
            }
            $keep = null;

            continue;
        }

        if ($keep !== false) {
            $out .= $line;
        }
    }

    if ($keep !== null) {
        fail("@feature non fermé dans {$file}.");
    }

    return $out;
}

/** « Mon Projet », mon-projet, mon_projet, MonProjet -> ['mon', 'projet'] */
function splitWords(string $name): array
{
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
    $name = $ascii !== false ? $ascii : $name;
    $name = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $name);

    return preg_split('/[^A-Za-z0-9]+/', strtolower($name), -1, PREG_SPLIT_NO_EMPTY);
}

function slug(string $value): string
{
    return implode('-', splitWords($value)) ?: 'app';
}

function projectFiles(string $root, array $excluded): Generator
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            function (SplFileInfo $file) use ($root, $excluded) {
                $relative = ltrim(substr($file->getPathname(), strlen($root)), '/');

                return ! in_array($relative, $excluded, true) && $file->getFilename() !== 'node_modules';
            },
        ),
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && ! $file->isLink()) {
            yield $file->getPathname();
        }
    }
}

function removePath(string $path): void
{
    if (is_link($path) || is_file($path)) {
        unlink($path);

        return;
    }

    if (! is_dir($path)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($items as $item) {
        $item->isDir() && ! $item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($path);
}

function readJson(string $file): array
{
    return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
}

function writeJson(string $file, array $data): void
{
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
}

function composerCommand(): string
{
    $binary = getenv('COMPOSER_BINARY');

    if ($binary && is_file($binary)) {
        return escapeshellarg(PHP_BINARY).' '.escapeshellarg($binary);
    }

    commandExists('composer') || fail('Composer est introuvable (ou relancez avec --no-install).');

    return 'composer';
}

function commandExists(string $command): bool
{
    $check = DIRECTORY_SEPARATOR === '\\' ? 'where' : 'command -v';

    return trim((string) @shell_exec("{$check} ".escapeshellarg($command).' 2>'.(DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null'))) !== '';
}

function run(string $command): void
{
    passthru($command, $code);

    if ($code !== 0) {
        fail("Échec de la commande : {$command}");
    }
}

function step(string $message): void
{
    fwrite(STDOUT, "\n\033[36m→\033[0m {$message}\n");
}

function info(string $message): void
{
    fwrite(STDOUT, "\033[32m✔\033[0m {$message}\n");
}

function warn(string $message): void
{
    fwrite(STDOUT, "\033[33m!\033[0m {$message}\n");
}

function fail(string $message): never
{
    fwrite(STDERR, "\033[31m✘ {$message}\033[0m\n");
    exit(1);
}
