<?php

/*
|--------------------------------------------------------------------------
| Initialisation d'un nouveau projet à partir du template
|--------------------------------------------------------------------------
|
| Renomme le projet (marqueurs TemplateApp / template_app / template-app)
| avec le nom choisi, puis supprime tout ce qui est propre au template.
|
| Usage :
|   php bin/init.php                     # nom déduit du dossier courant
|   php bin/init.php mon-projet          # nom explicite
|   php bin/init.php mon-projet --vendor=acme --author="Jean Dupont" --email=jean@acme.ci
|
| Le nom peut s'écrire mon-projet, mon_projet, "Mon Projet" ou MonProjet :
|   MonProjet   -> APP_NAME, titre Swagger, README
|   mon_projet  -> base de données / utilisateur PostgreSQL
|   mon-projet  -> nom du paquet composer (vendor/mon-projet)
|
| ⚠ Ne pas déclarer de script composer nommé "init" : Composer déclenche un
|   événement "init" à chaque commande, ce qui lancerait ce script.
*/

const TEMPLATE_DIR = 'template-clean-architecture-laravel';
const MARKER_STUDLY = 'TemplateApp';
const MARKER_SNAKE = 'template_app';
const MARKER_KEBAB = 'template-app';

$root = dirname(__DIR__);
chdir($root);

[$positional, $options] = parseArgs(array_slice($argv, 1));

if (isset($options['help']) || isset($options['h'])) {
    echo <<<'TXT'
    Usage : php bin/init.php [nom-du-projet] [--vendor=acme] [--author="Nom"] [--email=mail]

      nom-du-projet  mon-projet, mon_projet, "Mon Projet" ou MonProjet (défaut : nom du dossier)
      --vendor       préfixe du paquet composer (défaut : nom du projet)
      --author       auteur (défaut : git config user.name)
      --email        e-mail de l'auteur (défaut : git config user.email)

    TXT;
    exit(0);
}

if (! file_exists($root.'/.template')) {
    fail('Ce projet a déjà été initialisé (fichier .template absent).');
}

if (strcasecmp(basename($root), TEMPLATE_DIR) === 0) {
    fail('Refus : on est dans le dépôt du template lui-même. Créez un projet avec bin/create-project.sh <nom>.');
}

$rawName = $positional[0] ?? basename($root);
$words = splitWords($rawName);

if ($words === []) {
    fail("Nom de projet invalide : « {$rawName} ».");
}

$studly = implode('', array_map('ucfirst', $words));
$snake = implode('_', $words);
$kebab = implode('-', $words);
$vendor = slug((string) ($options['vendor'] ?? $kebab));
$author = (string) ($options['author'] ?? trim((string) @shell_exec('git config user.name 2>/dev/null')));
$email = (string) ($options['email'] ?? trim((string) @shell_exec('git config user.email 2>/dev/null')));

if (preg_match('/^[0-9]/', $snake)) {
    fail('Le nom doit commencer par une lettre (utilisé comme nom de base PostgreSQL).');
}

info("Initialisation du projet : {$studly}");
info("  APP_NAME / Swagger : {$studly}");
info("  Base PostgreSQL     : {$snake} (tests : {$snake}_test)");
info("  Paquet composer     : {$vendor}/{$kebab}");

// 1. Remplacement des marqueurs dans tous les fichiers texte du projet.
$replacements = [
    MARKER_STUDLY => $studly,
    MARKER_SNAKE => $snake,
    MARKER_KEBAB => $kebab,
];

$excluded = ['.git', 'vendor', 'node_modules', 'storage', 'bootstrap/cache', 'bin'];
$changed = 0;

foreach (projectFiles($root, $excluded) as $file) {
    $content = file_get_contents($file);

    if ($content === false || str_contains($content, "\0")) {
        continue; // binaire
    }

    $new = $content;

    if ($file === $root.'/README.md') {
        // Retire la section qui explique l'utilisation du template.
        $new = preg_replace('/<!-- template:start -->.*?<!-- template:end -->\n*/s', '', $new);
        $new = str_replace('{{AUTHOR}}', $author !== '' ? $author : 'à compléter', $new);
    }

    $new = strtr($new, $replacements);

    if ($new !== $content) {
        file_put_contents($file, $new);
        $changed++;
    }
}

// 2. composer.json : nom, auteur, retrait du hook d'initialisation.
$composerFile = $root.'/composer.json';
$composer = json_decode(file_get_contents($composerFile), true, flags: JSON_THROW_ON_ERROR);

$composer['name'] = "{$vendor}/{$kebab}";
$composer['scripts']['post-create-project-cmd'] = array_values(array_filter(
    $composer['scripts']['post-create-project-cmd'] ?? [],
    fn (string $cmd) => ! str_contains($cmd, 'bin/init.php'),
));

if ($author !== '') {
    $composer['authors'] = [array_filter(['name' => $author, 'email' => $email])];
}

file_put_contents(
    $composerFile,
    json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n",
);

// 3. .env local.
if (! file_exists($root.'/.env') && file_exists($root.'/.env.example')) {
    copy($root.'/.env.example', $root.'/.env');
    info('.env créé depuis .env.example');
}

// 4. Nettoyage des fichiers propres au template.
@unlink($root.'/.template');
@unlink($root.'/bin/create-project.sh');
@unlink(__FILE__);
@rmdir($root.'/bin');

info("{$changed} fichier(s) mis à jour. Projet « {$studly} » prêt.");

// ---------------------------------------------------------------------------

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

                return ! in_array($relative, $excluded, true);
            },
        ),
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && ! $file->isLink()) {
            yield $file->getPathname();
        }
    }
}

function info(string $message): void
{
    fwrite(STDOUT, "\033[32m✔\033[0m {$message}\n");
}

function fail(string $message): never
{
    fwrite(STDERR, "\033[31m✘ {$message}\033[0m\n");
    exit(1);
}
