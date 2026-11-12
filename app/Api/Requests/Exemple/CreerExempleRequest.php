<?php

namespace App\Api\Requests\Exemple;

use App\Application\Exemple\DTO\CreerExempleInput;
use Illuminate\Foundation\Http\FormRequest;

class CreerExempleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'libelle' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function toInput(): CreerExempleInput
    {
        return new CreerExempleInput(
            libelle: $this->validated('libelle'),
            montant: $this->validated('montant'),
        );
    }
}
