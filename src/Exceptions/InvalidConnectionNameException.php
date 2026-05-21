<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Exceptions;

use InvalidArgumentException;
use Rtcoder\LaravelERD\Enums\DatabaseConnection;

class InvalidConnectionNameException extends InvalidArgumentException
{
    public static function forConnection(?string $connection): self
    {
        return new self(sprintf(
            'Unsupported database driver [%s]. Supported drivers: %s.',
            $connection ?? 'none',
            implode(', ', DatabaseConnection::values())
        ));
    }
}
