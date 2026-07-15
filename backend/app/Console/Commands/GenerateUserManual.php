<?php

namespace App\Console\Commands;

use App\Services\UserManualExportService;
use Illuminate\Console\Command;

class GenerateUserManual extends Command
{
    protected $signature = 'manual:generate';
    protected $description = 'Generate the CHMSU Internship System Users Manual as DOCX';

    public function handle(UserManualExportService $service): int
    {
        $this->info('Generating Users Manual...');

        $path = $service->generate();

        $this->info("Manual generated successfully!");
        $this->warn("Output: $path");
        $this->warn("File size: " . round(filesize($path) / 1024 / 1024, 2) . " MB");

        return Command::SUCCESS;
    }
}
