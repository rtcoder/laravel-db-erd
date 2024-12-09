<?php
namespace Rtcoder\LaravelERD\Commands;

use Exception;
use Illuminate\Console\Command;
use Rtcoder\LaravelERD\Services\ERDGenerator;

class GenerateERDCommand extends Command
{
    protected $signature = 'erd:generate';
    protected $description = 'Generates an ERD diagram';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $generator = new ERDGenerator();
        $path = $generator->generate();
        $this->info("ERD diagram saved to: $path");
    }
}
