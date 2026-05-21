<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Exceptions;

class FileWriteException extends ERDException
{
    public static function forPath(string $path): self
    {
        return new self("Failed to write file: {$path}");
    }
}
