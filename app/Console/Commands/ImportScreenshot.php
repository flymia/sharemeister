<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Screenshot;
use App\Models\User;
use Illuminate\Support\Facades\File;


class ImportScreenshot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-screenshot {path} {useremail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all screenshots in a specific path to a users account.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = $this->argument('path');

        if (!is_dir($directory) || !is_readable($directory)) {
            $this->fail("Error: Directory is either missing or not readable.");
        }        
        
        // Check if the provided user exists
        if(!User::where('email', 'like', $this->argument('useremail'))->first()) {
            $this->fail("Error: Could not find the specific user by provided email.");
        }

        $files = File::files($directory);
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

        foreach ($files as $file) {
            $mimeType = File::mimeType($file);

            if (!in_array($mimeType, $allowedMimeTypes)) {
                $this->warn("Invalid file detected: {$file->getFilename()} ({$mimeType})");
            } else {
                $this->info("Importing valid image: {$file->getFilename()} ({$mimeType})");
                Screenshot::create([
                    'uploader_id' => User::where('email', 'like', $this->argument('useremail'))->first()?->id,
                    'image' => 'public/' . $file->getFilename(),
                ]);
            }
        }

        $this->info("Completed scan.");
        return 0;
    }
}
