<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/** Thrown when a route matches no handler or a record does not exist. */
final class NotFoundException extends RuntimeException
{
}
