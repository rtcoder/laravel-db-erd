<?php

namespace Rtcoder\LaravelERD\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ERDGenerator
{
    /**
     * Generate an ERD diagram and save it to a file.
     *
     * @param string $format The output format (e.g., 'pdf', 'png', 'svg').
     * @param string $outputPath The directory where the diagram will be saved.
     * @return string The full path to the generated file.
     * @throws Exception
     */
    public function generate(string $format, string $outputPath): string
    {
        $tables = $this->getTablesAndRelations();

        // Validate format
        $supportedFormats = ['pdf', 'png', 'svg'];
        if (!in_array($format, $supportedFormats)) {
            throw new \InvalidArgumentException("Unsupported format: $format");
        }

        // Create the graph representation
        $dotGraph = $this->generateDotGraph($tables);

        // Save the graph to a file
        $outputFile = rtrim($outputPath, '/') . "/erd_diagram.$format";
        $this->renderGraph($dotGraph, $outputFile, $format);

        return $outputFile;
    }

    /**
     * Get tables and their relationships from the database.
     *
     * @return array
     */
    protected function getTablesAndRelations(): array
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

            ",[$tableName]);

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

    /**
     * Generate a Graphviz DOT representation of the database schema.
     *
     * @param array $tables
     * @return string
     */
    protected function generateDotGraph(array $tables): string
    {
        $dot = "digraph ERD {\n";
        $dot .= "    rankdir=LR;\n";
        $dot .= "    node [shape=record];\n";

        foreach ($tables as $table) {
            $dot .= "    {$table['name']} [label=\"{ {$table['name']} | ";

            foreach ($table['relations'] as $relation) {
                $dot .= "+ {$relation['column']} → {$relation['referenced_table']}.{$relation['referenced_column']}\\l";
            }

            $dot .= "}\"];\n";
        }

        foreach ($tables as $table) {
            foreach ($table['relations'] as $relation) {
                $dot .= "    {$table['name']} -> {$relation['referenced_table']} [label=\"{$relation['column']} → {$relation['referenced_column']}\"];\n";
            }
        }

        $dot .= "}\n";
        return $dot;
    }

    /**
     * Render the graph using the Graphviz tool.
     *
     * @param string $dotGraph
     * @param string $outputFile
     * @param string $format
     * @throws Exception
     */
    protected function renderGraph(string $dotGraph, string $outputFile, string $format): void
    {
        $directory = dirname($outputFile);
        $this->ensureDirectoryExists($directory);

        $tempFile = tempnam(sys_get_temp_dir(), 'erd') . '.dot';
        file_put_contents($tempFile, $dotGraph);

        $command = escapeshellcmd("dot -T{$format} -o " . escapeshellarg($outputFile) . " " . escapeshellarg($tempFile));
        exec($command, $output, $returnVar);

        unlink($tempFile);

        if ($returnVar !== 0) {
            throw new Exception("Graphviz failed to render the graph. Make sure Graphviz is installed.");
        }
    }
    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new RuntimeException("Failed to create directory: $directory");
            }
        }
    }
}
