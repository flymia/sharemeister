<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Screenshot;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class SystemMetricsController extends Controller
{
    public function index()
    {
        // 1. Storage Metrics
        $totalSizeBytes = Screenshot::sum('file_size_kb') * 1024;
        
        // 2. Database Health
        $dbStatus = 'healthy';
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = 'unreachable';
        }

        // 3. Filesystem Health
        $storagePath = storage_path('app/public/screenshots');
        $isWritable = File::isWritable(storage_path('app/public'));

        return response()->json([
            'instance_name' => config('app.name'),
            'status' => ($dbStatus === 'healthy' && $isWritable) ? 'ok' : 'degraded',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
            
            'metrics' => [
                'total_users' => User::count(),
                'total_screenshots' => Screenshot::count(),
                'total_storage_used_mb' => round($totalSizeBytes / 1024 / 1024, 2),
                'average_screenshot_size_kb' => round(Screenshot::avg('file_size_kb') ?? 0, 2),
            ],
            
            'health' => [
                'database' => $dbStatus,
                'storage_writable' => $isWritable,
                'php_version' => PHP_VERSION,
                'disk_free_space_gb' => round(disk_free_space(storage_path()) / 1024 / 1024 / 1024, 2),
            ]
        ]);
    }
}