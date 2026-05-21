<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rtcoder\LaravelERD\Exceptions\UnsupportedOutputFormatException;
use Rtcoder\LaravelERD\Services\ERDGenerator;
use Throwable;

class ERDGeneratorTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_it_rejects_unsupported_output_formats(): void
    {
        $generator = new ERDGenerator();

        $this->expectException(UnsupportedOutputFormatException::class);
        $this->expectExceptionMessage('Unsupported output format [txt].');

        $generator->generate('/tmp/erd.txt', 'mysql');
    }

    public function test_it_escapes_dot_identifiers_and_labels(): void
    {
        $generator = new TestableERDGenerator();

        $dotGraph = $generator->dot([
            [
                'name' => 'order items',
                'relations' => [
                    [
                        'column' => 'order"id',
                        'referenced_table' => 'orders',
                        'referenced_column' => 'id',
                    ],
                ],
            ],
        ]);

        self::assertStringContainsString('"order items" [label="{ order items | + order\"id -> orders.id\\l}"];', $dotGraph);
        self::assertStringContainsString('"order items" -> "orders" [label="order\"id -> id"];', $dotGraph);
    }
}

class TestableERDGenerator extends ERDGenerator
{
    /**
     * @param array<int, array{name: string, relations: array<int, array{column: string, referenced_table: string, referenced_column: string}>}> $tables
     */
    public function dot(array $tables): string
    {
        return $this->generateDotGraph($tables);
    }
}
