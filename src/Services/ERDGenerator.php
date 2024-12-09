<?php

namespace Rtcoder\LaravelERD\Services;

use Exception;
use InvalidArgumentException;
use Rtcoder\LaravelERD\Services\TableRelation\Exception\InvalidConnectionNameException;
use Rtcoder\LaravelERD\Services\TableRelation\TableRelationResolver;
use RuntimeException;

class ERDGenerator
{
    /**
     * Generate an ERD diagram and save it to a file.
     *
     * @param string|null $format The output format (e.g., 'pdf', 'png', 'svg').
     * @param string|null $outputPath The directory where the diagram will be saved.
     * @return string The full path to the generated file.
     * @throws InvalidConnectionNameException
     * @throws Exception
     */
    public function generate(?string $format = null, ?string $outputPath = null): string
    {
        if (is_null($format)) {
            $format = config('erd.output_format');
        }
        if (is_null($outputPath)) {
            $outputPath = config('erd.output_directory');
        }

        $supportedFormats = ['pdf', 'png', 'svg'];
        if (!in_array($format, $supportedFormats)) {
            throw new InvalidArgumentException("Unsupported format: $format");
        }

        $tables = $this->getTablesAndRelations();

        $dotGraph = $this->generateDotGraph($tables);

        $filename = config('erd.output_name');
        $outputFile = rtrim($outputPath, '/') . "/$filename.$format";
        $this->renderGraph($dotGraph, $outputFile, $format);

        return $outputFile;
    }

    /**
     * Get tables and their relationships from the database.
     *
     * @return array
     * @throws InvalidConnectionNameException
     */
    protected function getTablesAndRelations(): array
    {
        $relationResolver = new TableRelationResolver();
        $relationResolver->setConnection(config('erd.default_driver'));

        $tableRelationClass = $relationResolver->resolve();
        return $tableRelationClass->getTableRelations();
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
