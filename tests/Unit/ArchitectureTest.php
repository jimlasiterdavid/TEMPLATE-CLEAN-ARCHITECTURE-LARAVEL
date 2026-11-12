<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Garde-fou de la clean architecture : les dépendances pointent vers l'intérieur.
 * Domain ← Application ← Infrastructure / Api
 */
class ArchitectureTest extends TestCase
{
    #[DataProvider('regles')]
    public function test_les_couches_respectent_les_dependances(string $couche, array $interdits): void
    {
        $violations = [];

        foreach ($this->fichiers(__DIR__.'/../../app/'.$couche) as $fichier) {
            preg_match_all('/^use\s+([^;]+);/m', file_get_contents($fichier), $matches);

            foreach ($matches[1] as $import) {
                foreach ($interdits as $interdit) {
                    if (str_starts_with(ltrim($import, '\\'), $interdit)) {
                        $violations[] = basename($fichier)." importe {$import}";
                    }
                }
            }
        }

        $this->assertSame([], $violations);
    }

    public static function regles(): array
    {
        return [
            'Domain est indépendant' => ['Domain', ['Illuminate\\', 'App\\Application\\', 'App\\Infrastructure\\', 'App\\Api\\']],
            'Application ne dépend que du Domain' => ['Application', ['Illuminate\\', 'App\\Infrastructure\\', 'App\\Api\\']],
            'Infrastructure ignore la couche Api' => ['Infrastructure', ['App\\Api\\']],
        ];
    }

    /** @return list<string> */
    private function fichiers(string $dossier): array
    {
        $fichiers = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dossier)) as $f) {
            if ($f->isFile() && $f->getExtension() === 'php') {
                $fichiers[] = $f->getPathname();
            }
        }

        return $fichiers;
    }
}
