<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Services\TableRelation;

use Illuminate\Support\Facades\DB;
use Rtcoder\LaravelERD\Services\TableRelation\Interface\TableRelationInterface;

class SqliteTableRelation implements TableRelationInterface
{
    /**
     * @return array<int, array{name: string, relations: array<int, array{column: string, referenced_table: string, referenced_column: string}>}>
     */
    public function getTableRelations(): array
    {
        $tables = [];

        $rawTables = DB::select("
            SELECT name 
            FROM sqlite_master 
            WHERE type = 'table' AND name NOT LIKE 'sqlite_%';
        ");

        foreach ($rawTables as $table) {
            $tableName = (string) $table->name;
            $quotedTableName = '"' . str_replace('"', '""', $tableName) . '"';

            $foreignKeys = DB::select("PRAGMA foreign_key_list($quotedTableName);");

            $relations = [];
            foreach ($foreignKeys as $fk) {
                $relations[] = [
                    'column' => (string) $fk->{'from'},
                    'referenced_table' => (string) $fk->table,
                    'referenced_column' => (string) $fk->to,
                ];
            }

            $tables[] = [
                'name' => $tableName,
                'relations' => $relations,
            ];
        }

        return $tables;
    }
}
