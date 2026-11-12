<?php

namespace App\Web\Livewire\Exemples;

use App\Application\Exemple\DTO\CreerExempleInput;
use App\Application\Exemple\UseCases\CreerExempleUseCase;
use App\Application\Exemple\UseCases\ListerExemplesUseCase;
use App\Domain\Shared\Errors\DomainError;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Composant modèle : valider → cas d'usage → afficher.
 * Aucune logique métier ni accès aux données ici.
 */
#[Layout('layouts.app')]
#[Title('Exemples')]
class Index extends Component
{
    public const PAR_PAGE = 10;

    #[Validate('required|string|max:255')]
    public string $libelle = '';

    #[Validate('required|numeric|min:0')]
    public string $montant = '';

    #[Url]
    public int $page = 1;

    public function creer(CreerExempleUseCase $useCase): void
    {
        $this->validate();

        try {
            $useCase->execute(new CreerExempleInput($this->libelle, $this->montant));
        } catch (DomainError $e) {
            $this->addError('erreur', $e->getMessage());

            return;
        }

        $this->reset('libelle', 'montant');
        session()->flash('succes', 'Exemple créé.');
    }

    public function pagePrecedente(): void
    {
        $this->page = max(1, $this->page - 1);
    }

    public function pageSuivante(): void
    {
        $this->page++;
    }

    public function render(): View
    {
        $this->page = max(1, $this->page);

        return view('livewire.exemples.index', [
            'exemples' => app(ListerExemplesUseCase::class)->execute($this->page, self::PAR_PAGE),
            'parPage' => self::PAR_PAGE,
        ]);
    }
}
