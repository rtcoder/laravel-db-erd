<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Exceptions;

class TemporaryFileCreationException extends ERDException
{
    public static function forDirectory(string $directory): self
    {
        return new self("Failed to create a temporary DOT file in: {$directory}");
    }
}
