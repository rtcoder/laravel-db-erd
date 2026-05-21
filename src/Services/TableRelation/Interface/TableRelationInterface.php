<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Services\TableRelation\Interface;

interface TableRelationInterface
{
    /**
     * @return array<int, array{name: string, relations: array<int, array{column: string, referenced_table: string, referenced_column: string}>}>
     */
    public function getTableRelations(): array;
}
