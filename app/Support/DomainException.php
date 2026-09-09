<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * An expected business error whose message is safe to show to the user,
 * for example "Your cart is empty" or "Only 2 items left in stock".
 *
 * Unexpected errors (a broken query, a missing file) stay normal exceptions
 * and are handled by App\Support\ErrorHandler instead.
 */
final class DomainException extends RuntimeException
{
}
