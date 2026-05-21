<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Services;

use Illuminate\Support\Facades\File;
use Rtcoder\LaravelERD\Exceptions\DirectoryCreationException;
use Rtcoder\LaravelERD\Exceptions\FileWriteException;
use Rtcoder\LaravelERD\Exceptions\GraphvizRenderException;
use Rtcoder\LaravelERD\Exceptions\InvalidConnectionNameException;
use Rtcoder\LaravelERD\Exceptions\TemporaryFileCreationException;
use Rtcoder\LaravelERD\Exceptions\UnsupportedOutputFormatException;
use Rtcoder\LaravelERD\Exceptions\ViewNotFoundException;
use Rtcoder\LaravelERD\Services\TableRelation\TableRelationResolver;
use Throwable;

class ERDGenerator
{
    private const SUPPORTED_FORMATS = ['pdf', 'png', 'svg', 'html'];

    public function __construct(
        private readonly ?TableRelationResolver $relationResolver = null,
    ) {
    }

    /**
     * Generate an ERD diagram and save it to a file.
     *
     * @throws Throwable
     */
    public function generate(string $outputFile, string $driver): void
    {
        $format = strtolower((string) pathinfo($outputFile, PATHINFO_EXTENSION));

        if (!in_array($format, self::SUPPORTED_FORMATS, true)) {
            throw UnsupportedOutputFormatException::forFormat($format, self::SUPPORTED_FORMATS);
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
     * @throws InvalidConnectionNameException
     *
     * @return array<int, array{name: string, relations: array<int, array{column: string, referenced_table: string, referenced_column: string}>}>
     */
    protected function getTablesAndRelations(string $driver): array
    {
        $relationResolver = $this->relationResolver ?? new TableRelationResolver();
        $relationResolver->setConnection($driver);

        $tableRelationClass = $relationResolver->resolve();
        return $tableRelationClass->getTableRelations();
    }

    /**
     * Generate a Graphviz DOT representation of the database schema.
     *
     * @param array<int, array{name: string, relations: array<int, array{column: string, referenced_table: string, referenced_column: string}>}> $tables
     */
    protected function generateDotGraph(array $tables): string
    {
        $dot = "digraph ERD {\n";
        $dot .= "    rankdir=LR;\n";
        $dot .= "    node [shape=record];\n";

        foreach ($tables as $table) {
            $tableName = (string) $table['name'];
            $dot .= '    ' . $this->quoteDotIdentifier($tableName) . ' [label="' . $this->escapeDotLabel("{ {$tableName} | ");

            foreach ($table['relations'] as $relation) {
                $dot .= $this->escapeDotLabel(sprintf(
                    '+ %s -> %s.%s',
                    $relation['column'],
                    $relation['referenced_table'],
                    $relation['referenced_column']
                )) . '\\l';
            }

            $dot .= "}\"];\n";
        }

        foreach ($tables as $table) {
            foreach ($table['relations'] as $relation) {
                $dot .= sprintf(
                    "    %s -> %s [label=\"%s\"];\n",
                    $this->quoteDotIdentifier((string) $table['name']),
                    $this->quoteDotIdentifier((string) $relation['referenced_table']),
                    $this->escapeDotLabel(sprintf('%s -> %s', $relation['column'], $relation['referenced_column']))
                );
            }
        }

        $dot .= "}\n";
        return $dot;
    }

    /**
     * Render the graph using the Graphviz tool.
     *
     * @throws DirectoryCreationException
     * @throws FileWriteException
     * @throws GraphvizRenderException
     * @throws TemporaryFileCreationException
     */
    protected function renderGraph(string $dotGraph, string $outputFile, string $format): void
    {
        $directory = dirname($outputFile);
        $this->ensureDirectoryExists($directory);

        $tempFile = tempnam(sys_get_temp_dir(), 'erd');

        if ($tempFile === false) {
            throw TemporaryFileCreationException::forDirectory(sys_get_temp_dir());
        }

        $dotFile = $tempFile . '.dot';

        if (!rename($tempFile, $dotFile)) {
            @unlink($tempFile);
            throw TemporaryFileCreationException::forDirectory(sys_get_temp_dir());
        }

        if (file_put_contents($dotFile, $dotGraph) === false) {
            @unlink($dotFile);
            throw FileWriteException::forPath($dotFile);
        }

        $command = sprintf(
            'dot -T%s -o %s %s',
            escapeshellarg($format),
            escapeshellarg($outputFile),
            escapeshellarg($dotFile)
        );
        exec($command, $output, $returnVar);

        @unlink($dotFile);

        if ($returnVar !== 0) {
            throw GraphvizRenderException::failed($format, $outputFile, $output);
        }
    }

    /**
     * @throws Throwable
     *
     * @param array<int, array{name: string, relations: array<int, array{column: string, referenced_table: string, referenced_column: string}>}> $tables
     */
    protected function renderGraphHtml(array $tables, string $outputFile): void
    {
        $directory = dirname($outputFile);
        $this->ensureDirectoryExists($directory);

        $nodes = [];
        $links = [];
        foreach ($tables as $table) {
            $nodes[] = ['id' => $table['name'], 'name' => $table['name']];
            foreach ($table['relations'] as $relation) {
                $links[] = [
                    'source' => $table['name'],
                    'target' => $relation['referenced_table'],
                    'value' => 1,
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
            throw ViewNotFoundException::forPaths([$projectViewPath, $packageViewPath]);
        }

        if (file_put_contents($outputFile, $htmlContent) === false) {
            throw FileWriteException::forPath($outputFile);
        }
    }

    /**
     * @throws DirectoryCreationException
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw DirectoryCreationException::forPath($directory);
            }
        }
    }

    private function quoteDotIdentifier(string $identifier): string
    {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\"'], $identifier) . '"';
    }

    private function escapeDotLabel(string $label): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\"'], $label);
    }
}
