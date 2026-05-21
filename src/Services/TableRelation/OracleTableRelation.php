<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Services\TableRelation;

use Illuminate\Support\Facades\DB;
use Rtcoder\LaravelERD\Services\TableRelation\Interface\TableRelationInterface;

class OracleTableRelation implements TableRelationInterface
{
    /**
     * @return array<int, array{name: string, relations: array<int, array{column: string, referenced_table: string, referenced_column: string}>}>
     */
    public function getTableRelations(): array
    {
        $tables = [];

        $rawTables = DB::select("
            SELECT table_name 
            FROM all_tables 
            WHERE owner = USER;
        ");

        foreach ($rawTables as $table) {
            $tableName = $table->table_name;

            $foreignKeys = DB::select("
                SELECT 
                    a.table_name AS source_table,
                    a.column_name AS source_column,
                    c.table_name AS target_table,
                    c.column_name AS target_column
                FROM 
                    all_cons_columns a
                JOIN 
                    all_constraints b
                ON 
                    a.owner = b.owner
                    AND a.constraint_name = b.constraint_name
                JOIN 
                    all_cons_columns c
                ON 
                    b.r_owner = c.owner
                    AND b.r_constraint_name = c.constraint_name
                WHERE 
                    b.constraint_type = 'R'
                    AND a.table_name = ?
                    AND a.owner = USER;
            ", [$tableName]);

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
