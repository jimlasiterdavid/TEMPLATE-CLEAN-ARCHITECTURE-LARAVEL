<?php

namespace App\Domain\Shared\Errors;

/**
 * Règle métier non respectée (validée par le domaine ou levée par une procédure stockée).
 */
class BusinessRuleViolation extends DomainError {}
