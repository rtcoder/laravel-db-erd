<?php

namespace Rtcoder\LaravelERD\Commands;

use Illuminate\Console\Command;
use Rtcoder\LaravelERD\Services\ERDGenerator;
use Symfony\Component\Console\Command\Command as CommandAlias;

class GenerateERDCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:erd 
                            {--output= : The file path where the ERD will be saved (default: storage/erd/erd_diagram.<format>)} 
                            {--driver= : The database driver to use (default: from config file)} 
                            {--format= : The output format for the ERD (e.g., pdf, png, svg)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an Entity-Relationship Diagram (ERD) of the database schema';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $format = $this->option('format') ?? config('erd.output_format');
        $outputDirectory = config('erd.output_directory');
        $outputName = config('erd.output_name');
        $outputPath = $this->option('output') ?? "{$outputDirectory}/{$outputName}.{$format}";
        $driver = $this->option('driver') ?? config('erd.default_driver');

        // Informacje
        $this->info("Generating ERD using driver: {$driver}");
        $this->info("Output format: {$format}");
        $this->info("Saving ERD to: {$outputPath}");

        try {
            $generator = new ERDGenerator();
            $generator->generate($outputPath, $driver);

            $this->info('ERD successfully generated!');
            return CommandAlias::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate ERD: ' . $e->getMessage());
            return CommandAlias::FAILURE;
        }
    }
}
