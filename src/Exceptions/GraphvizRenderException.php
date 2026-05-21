<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Exceptions;

class GraphvizRenderException extends ERDException
{
    /**
     * @param array<int, string> $output
     */
    public static function failed(string $format, string $outputFile, array $output = []): self
    {
        $details = trim(implode(PHP_EOL, $output));

        $message = "Graphviz failed to render {$format} output to {$outputFile}. Make sure Graphviz is installed.";

        if ($details !== '') {
            $message .= " Output: {$details}";
        }

        return new self($message);
    }
}
