<?php

namespace Rtcoder\LaravelERD\Services\TableRelation;

use Illuminate\Support\Facades\DB;
use Rtcoder\LaravelERD\Services\TableRelation\Interface\TableRelationInterface;

class MysqlTableRelation implements TableRelationInterface
{

    /**
     * @return array
     */
    public function getTableRelations(): array
    {
        $tables = [];

        $databaseName = DB::getDatabaseName(); // Pobierz nazwę bazy danych

        // Pobierz wszystkie tabele w bieżącej bazie danych
        $rawTables = DB::select("
            SELECT table_name 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = ?
        ", [$databaseName]);

        foreach ($rawTables as $table) {
            $tableName = $table->table_name;

            // Pobierz klucze obce dla tabeli
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
