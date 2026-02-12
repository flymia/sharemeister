<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class HealthService
{
    /**
     * Perform a full system health check.
     * * @return array
     * @throws Exception
     */
    public function checkSystemHealth(): array
    {
        // 1. Database Check
        DB::connection()->getPdo();

        // 2. Storage Check (Write permissions)
        $testFile = 'healthchecks/' . bin2hex(random_bytes(8)) . '.txt';
        Storage::disk('public')->put($testFile, 'healthcheck');
        Storage::disk('public')->delete($testFile);

        return [
            'status'    => 'ok',
            'database'  => 'connected',
            'storage'   => 'writable',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}