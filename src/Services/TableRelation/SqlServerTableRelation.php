<?php

namespace Rtcoder\LaravelERD\Services\TableRelation;

use Illuminate\Support\Facades\DB;
use Rtcoder\LaravelERD\Services\TableRelation\Interface\TableRelationInterface;

class SqlServerTableRelation implements TableRelationInterface
{

    /**
     * @return array
     */
    public function getTableRelations(): array
    {
        $tables = [];

        // Pobierz wszystkie tabele w bieżącej bazie danych
        $rawTables = DB::select("
            SELECT TABLE_NAME 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_TYPE = 'BASE TABLE';
        ");

        foreach ($rawTables as $table) {
            $tableName = $table->TABLE_NAME;

            // Pobierz klucze obce dla tabeli
            $foreignKeys = DB::select("
                SELECT 
                    fk.name AS constraint_name,
                    parent.name AS source_table,
                    pc.name AS source_column,
                    referenced.name AS target_table,
                    rc.name AS target_column
                FROM 
                    sys.foreign_keys AS fk
                INNER JOIN 
                    sys.foreign_key_columns AS fkc
                ON 
                    fk.object_id = fkc.constraint_object_id
                INNER JOIN 
                    sys.tables AS parent
                ON 
                    fkc.parent_object_id = parent.object_id
                INNER JOIN 
                    sys.columns AS pc
                ON 
                    fkc.parent_object_id = pc.object_id AND fkc.parent_column_id = pc.column_id
                INNER JOIN 
                    sys.tables AS referenced
                ON 
                    fkc.referenced_object_id = referenced.object_id
                INNER JOIN 
                    sys.columns AS rc
                ON 
                    fkc.referenced_object_id = rc.object_id AND fkc.referenced_column_id = rc.column_id
                WHERE 
                    parent.name = ?;
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
