<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Contracts;

use Throwable;

interface ERDGeneratorInterface
{
    /**
     * Generate an ERD diagram and save it to a file.
     *
     * @throws Throwable
     */
    public function generate(string $outputFile, string $driver): void;
}
