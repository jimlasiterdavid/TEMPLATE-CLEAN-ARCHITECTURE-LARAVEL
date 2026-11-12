<?php

namespace App\Api\Requests\Exemple;

use App\Application\Exemple\UseCases\ListerExemplesUseCase;
use Illuminate\Foundation\Http\FormRequest;

class ListerExemplesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'par_page' => ['sometimes', 'integer', 'min:1', 'max:'.ListerExemplesUseCase::PAR_PAGE_MAX],
        ];
    }
}
