<?php

namespace App\Domain\Shared\Errors;

/**
 * Règle métier non respectée (validée par le domaine ou remontée par l'infrastructure).
 */
class BusinessRuleViolation extends DomainError {}
