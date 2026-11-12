<?php

namespace App\Domain\Shared\Errors;

use RuntimeException;

/**
 * Erreur de base du domaine : son message est destiné à être affiché à l'utilisateur.
 */
abstract class DomainError extends RuntimeException {}
