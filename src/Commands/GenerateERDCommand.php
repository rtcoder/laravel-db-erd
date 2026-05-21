<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Commands;

use Illuminate\Console\Command;
use Rtcoder\LaravelERD\Services\ERDGenerator;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;

class GenerateERDCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'erd:generate
                            {--output= : The file path where the ERD will be saved (default: storage/erd/erd_diagram.<format>)} 
                            {--driver= : The database driver to use (default: from config file)} 
                            {--format= : The output format for the ERD (pdf, png, svg, html)}';

    /**
     * The console command description.
     */
    protected $description = 'Generate an Entity-Relationship Diagram (ERD) of the database schema';

    public function handle(): int
    {
        $format = (string) ($this->option('format') ?? config('erd.output_format', 'pdf'));
        $outputDirectory = (string) config('erd.output_directory');
        $outputName = (string) config('erd.output_name', 'erd_diagram');
        $outputPath = (string) ($this->option('output') ?? "{$outputDirectory}/{$outputName}.{$format}");
        $driver = (string) ($this->option('driver') ?? config('erd.default_driver', 'pgsql'));

        $this->info("Generating ERD using driver: {$driver}");
        $this->info("Output format: {$format}");
        $this->info("Saving ERD to: {$outputPath}");

        try {
            $generator = new ERDGenerator();
            $generator->generate($outputPath, $driver);

            $this->info('ERD successfully generated!');
            return CommandAlias::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Failed to generate ERD: ' . $e->getMessage());
            return CommandAlias::FAILURE;
        }
    }
}
