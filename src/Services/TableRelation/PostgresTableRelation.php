<?php

namespace Rtcoder\LaravelERD\Services\TableRelation;

use Illuminate\Support\Facades\DB;
use Rtcoder\LaravelERD\Services\TableRelation\Interface\TableRelationInterface;

class PostgresTableRelation implements TableRelationInterface
{

    /**
     * @return array
     */
    public function getTableRelations(): array
    {
        $tables = [];

        $tableSchema = 'public';

        $rawTables = DB::select("
            SELECT table_name 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = ?
            
        ", [$tableSchema]);

        foreach ($rawTables as $table) {
            $tableName = $table->table_name;

            // Fetch foreign keys for the table
            $foreignKeys = DB::select("
                SELECT 
                    tc.table_name AS source_table,
                    kcu.column_name AS source_column,
                    ccu.table_name AS target_table,
                    ccu.column_name AS target_column
                FROM 
                    information_schema.table_constraints AS tc
                JOIN 
                    information_schema.key_column_usage AS kcu
                ON 
                    tc.constraint_name = kcu.constraint_name
                    AND tc.table_schema = kcu.table_schema
                JOIN 
                    information_schema.constraint_column_usage AS ccu
                ON 
                    ccu.constraint_name = tc.constraint_name
                    AND ccu.table_schema = tc.table_schema
                WHERE 
                    tc.constraint_type = 'FOREIGN KEY'
                    AND tc.table_schema = 'public'
                    AND tc.table_name = ?;

            ", [$tableName]);

            $relations = [];
            foreach ($foreignKeys as $fk) {
                $relations[] = [
                    'column' => $fk->source_column,
                    'referenced_table' => $fk->target_table,
                    'referenced_column' => $fk->target_column,
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
