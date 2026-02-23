<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestSchedulerCommand extends Command
{
    // Command name used in scheduler or terminal
    protected $signature = 'test:scheduler';

    // Command description shown in artisan list
    protected $description = 'Test schedule monitor command';

    public function handle()
    {
        // Write execution time into Laravel log file
        \Log::info('Scheduler executed at '.now());

        // Display success message in console
        $this->info('Scheduler Working Successfully');

        // Return success status to Laravel scheduler
        return Command::SUCCESS;
    }
}