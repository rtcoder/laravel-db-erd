<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Exceptions;

class DirectoryCreationException extends ERDException
{
    public static function forPath(string $directory): self
    {
        return new self("Failed to create directory: {$directory}");
    }
}
