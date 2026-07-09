<?php

namespace App\Services;

use App\Models\User;
use App\Models\Screenshot;

class DashboardService
{
    public function getDashboardData(User $user): array
    {
        $screenshots = Screenshot::where('uploader_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        $totalCount = Screenshot::where('uploader_id', $user->id)->count();
        $permanentCount = Screenshot::where('uploader_id', $user->id)
            ->where('is_permanent', true)
            ->count();
        $totalSizeKb = Screenshot::where('uploader_id', $user->id)->sum('file_size_kb');
        $totalSizeMb = round($totalSizeKb / 1024, 2);
        
        $limit = $user->storage_limit_mb;
        $usagePercent = 0;
        
        if ($limit > 0) {
            $usagePercent = round(($totalSizeMb / $limit) * 100, 2);
            // Cap the progress bar at 100%
            if ($usagePercent > 100) $usagePercent = 100;
        }

        return [
            'screenshots' => $screenshots,
            'totalCount' => $totalCount,
            'permanentCount' => $permanentCount,
            'totalSize' => $totalSizeMb,
            'limit' => $limit,
            'usagePercent' => $usagePercent
        ];
    }
}
