<?php

namespace App\Domain\Shared\Errors;

class NotFoundError extends DomainError
{
    public static function for(string $resource, int|string $id): self
    {
        return new self("{$resource} [{$id}] introuvable.");
    }
}
