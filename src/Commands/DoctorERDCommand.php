<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Process\ExecutableFinder;

class DoctorERDCommand extends Command
{
    protected $signature = 'erd:doctor';

    protected $description = 'Check whether Laravel DB ERD dependencies are available';

    public function handle(): int
    {
        $dotPath = (new ExecutableFinder())->find('dot');

        if ($dotPath === null) {
            $this->error('Graphviz was not found. The "dot" binary is not available in PATH.');
            $this->line('');
            $this->line('Install Graphviz:');
            $this->line('  macOS: brew install graphviz');
            $this->line('  Ubuntu / Debian: sudo apt install graphviz');
            $this->line('  Windows: winget install graphviz');
            $this->line('  Windows: choco install graphviz');
            $this->line('');
            $this->warn('After installing Graphviz, restart your terminal so PATH changes are picked up.');

            return CommandAlias::FAILURE;
        }

        exec(escapeshellarg($dotPath) . ' -V 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error("Graphviz was found at {$dotPath}, but running \"dot -V\" failed.");
            return CommandAlias::FAILURE;
        }

        $this->info('Graphviz is available.');
        $this->line("dot path: {$dotPath}");

        if ($output !== []) {
            $this->line('version: ' . trim(implode(' ', $output)));
        }

        return CommandAlias::SUCCESS;
    }
}
