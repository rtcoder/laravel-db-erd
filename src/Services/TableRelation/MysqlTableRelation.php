<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Services\TableRelation;

use Illuminate\Support\Facades\DB;
use Rtcoder\LaravelERD\Services\TableRelation\Interface\TableRelationInterface;

class MysqlTableRelation implements TableRelationInterface
{
    /**
     * @return array<int, array{name: string, relations: array<int, array{column: string, referenced_table: string, referenced_column: string}>}>
     */
    public function getTableRelations(): array
    {
        $tables = [];

        $databaseName = DB::getDatabaseName();

        $rawTables = DB::select("
            SELECT table_name 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = ?
        ", [$databaseName]);

        foreach ($rawTables as $table) {
            $tableName = $table->table_name;

            $foreignKeys = DB::select("
                SELECT 
                    kcu.table_name AS source_table,
                    kcu.column_name AS source_column,
                    kcu.referenced_table_name AS target_table,
                    kcu.referenced_column_name AS target_column
                FROM 
                    information_schema.key_column_usage AS kcu
                JOIN 
                    information_schema.referential_constraints AS rc
                ON 
                    kcu.constraint_name = rc.constraint_name
                    AND kcu.table_schema = rc.constraint_schema
                WHERE 
                    kcu.table_schema = ?
                    AND kcu.table_name = ?
                    AND kcu.referenced_table_name IS NOT NULL;
            ", [$databaseName, $tableName]);

            $relations = [];
            foreach ($foreignKeys as $fk) {
                $relations[] = [
                    'column' => (string) $fk->source_column,
                    'referenced_table' => (string) $fk->target_table,
                    'referenced_column' => (string) $fk->target_column,
                ];
            }

            $tables[] = [
                'name' => (string) $tableName,
                'relations' => $relations,
            ];
        }
        return $tables;
    }
}
