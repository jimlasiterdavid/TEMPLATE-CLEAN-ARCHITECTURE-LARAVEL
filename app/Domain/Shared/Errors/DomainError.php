<?php

namespace App\Domain\Shared\Errors;

use RuntimeException;

/**
 * Erreur de base du domaine : son message est destiné à être affiché au front.
 */
abstract class DomainError extends RuntimeException {}
