<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Enums;

enum OutputFormat: string
{
    case Pdf = 'pdf';
    case Png = 'png';
    case Svg = 'svg';
    case Html = 'html';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $format): string => $format->value,
            self::cases()
        );
    }
}
