<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Exceptions;

use InvalidArgumentException;

class UnsupportedOutputFormatException extends InvalidArgumentException
{
    /**
     * @param array<int, string> $supportedFormats
     */
    public static function forFormat(string $format, array $supportedFormats): self
    {
        return new self(sprintf(
            'Unsupported output format [%s]. Supported formats: %s.',
            $format,
            implode(', ', $supportedFormats)
        ));
    }
}
