<?php

namespace Rtcoder\LaravelERD\Services\TableRelation;

use Illuminate\Support\Facades\DB;
use Rtcoder\LaravelERD\Services\TableRelation\Interface\TableRelationInterface;

class SqliteTableRelation implements TableRelationInterface
{

    /**
     * @return array
     */
    public function getTableRelations(): array
    {
        $tables = [];

        // Pobierz wszystkie tabele w bazie SQLite
        $rawTables = DB::select("
            SELECT name 
            FROM sqlite_master 
            WHERE type = 'table' AND name NOT LIKE 'sqlite_%';
        ");

        foreach ($rawTables as $table) {
            $tableName = $table->name;

            // Pobierz klucze obce dla tabeli
            $foreignKeys = DB::select("
                PRAGMA foreign_key_list($tableName);
            ");

            $relations = [];
            foreach ($foreignKeys as $fk) {
                $relations[] = [
                    'column' => $fk->from,
                    'referenced_table' => $fk->table,
                    'referenced_column' => $fk->to,
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
