<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Services\TableRelation\Exception;

use InvalidArgumentException;
use Throwable;

class InvalidConnectionNameException extends InvalidArgumentException
{
    public function __construct(string $message = 'Invalid database connection name.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
