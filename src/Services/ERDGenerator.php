<?php

namespace Rtcoder\LaravelERD\Services;

use Exception;
use Illuminate\Support\Facades\DB;

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
        $databaseName = DB::connection()->getDatabaseName();

        // Fetch tables
        $rawTables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?", [$databaseName]);
        foreach ($rawTables as $table) {
            $tableName = $table->TABLE_NAME;

            // Fetch foreign keys for the table
            $foreignKeys = DB::select("
                SELECT
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE
                    TABLE_SCHEMA = ?
                    AND TABLE_NAME = ?
                    AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$databaseName, $tableName]);

            $relations = [];
            foreach ($foreignKeys as $fk) {
                $relations[] = [
                    'column' => $fk->COLUMN_NAME,
                    'referenced_table' => $fk->REFERENCED_TABLE_NAME,
                    'referenced_column' => $fk->REFERENCED_COLUMN_NAME,
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
        $tempFile = tempnam(sys_get_temp_dir(), 'erd') . '.dot';
        file_put_contents($tempFile, $dotGraph);

        $command = escapeshellcmd("dot -T{$format} -o " . escapeshellarg($outputFile) . " " . escapeshellarg($tempFile));
        exec($command, $output, $returnVar);

        unlink($tempFile);

        if ($returnVar !== 0) {
            throw new Exception("Graphviz failed to render the graph. Make sure Graphviz is installed.");
        }
    }
}
