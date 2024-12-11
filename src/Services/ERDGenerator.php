<?php

namespace Rtcoder\LaravelERD\Services;

use Exception;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Rtcoder\LaravelERD\Services\TableRelation\Exception\InvalidConnectionNameException;
use Rtcoder\LaravelERD\Services\TableRelation\TableRelationResolver;
use RuntimeException;

class ERDGenerator
{
    /**
     * Generate an ERD diagram and save it to a file.
     *
     * @param string $outputFile
     * @param string $driver
     * @return void The full path to the generated file.
     * @throws Exception
     */
    public function generate(string $outputFile, string $driver): void
    {
        $format = pathinfo($outputFile, PATHINFO_EXTENSION);

        $supportedFormats = ['pdf', 'png', 'svg', 'html'];
        if (!in_array($format, $supportedFormats)) {
            throw new InvalidArgumentException("Unsupported format: $format");
        }

        $tables = $this->getTablesAndRelations($driver);

        if ($format === 'html') {
            $this->renderGraphHtml($tables, $outputFile);
            return;
        }

        $dotGraph = $this->generateDotGraph($tables);

        $this->renderGraph($dotGraph, $outputFile, $format);
    }

    /**
     * Get tables and their relationships from the database.
     *
     * @param string $driver
     * @return array
     * @throws InvalidConnectionNameException
     */
    protected function getTablesAndRelations(string $driver): array
    {
        $relationResolver = new TableRelationResolver();
        $relationResolver->setConnection($driver);

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

    /**
     * @param array $tables
     * @param string $outputFile
     * @return void
     * @throws Exception
     */
    protected function renderGraphHtml(array $tables, string $outputFile): void
    {
        $directory = dirname($outputFile);
        $this->ensureDirectoryExists($directory);

        // Konwertowanie danych na format dla D3.js
        $nodes = [];
        $links = [];
        foreach ($tables as $table) {
            $nodes[] = ['id' => $table['name'], 'name' => $table['name']];
            foreach ($table['relations'] as $relation) {
                $links[] = [
                    'source' => $table['name'],
                    'target' => $relation['referenced_table'],
                    'value' => 1
                ];
            }
        }

        $diagramData = [
            'nodes' => $nodes,
            'links' => $links,
        ];

        $projectViewPath = resource_path('views/vendor/laravel-erd/erd-diagram.blade.php');
        $packageViewPath = __DIR__ . '/../resources/views/erd-diagram.blade.php';

        if (File::exists($projectViewPath)) {
            $htmlContent = view('vendor.laravel-erd.erd-diagram', compact('diagramData'))->render();
        } elseif (File::exists($packageViewPath)) {
            $htmlContent = view()->file($packageViewPath, compact('diagramData'))->render();
        } else {
            throw new Exception("View file not found in either project or package.");
        }

        // Zapisanie pliku HTML
        file_put_contents($outputFile, $htmlContent);
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
