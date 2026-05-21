<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Exceptions;

class ViewNotFoundException extends ERDException
{
    /**
     * @param array<int, string> $paths
     */
    public static function forPaths(array $paths): self
    {
        return new self('ERD view file not found. Checked paths: ' . implode(', ', $paths));
    }
}
